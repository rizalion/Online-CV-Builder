<?php
/**
 * Authentication Functions
 * 
 * Handles user registration, login, logout, and rate limiting.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

/**
 * Register a new user.
 *
 * @return array ['success' => bool, 'errors' => string[], 'user_id' => int|null]
 */
function register_user(string $name, string $email, string $password, string $confirm_password): array
{
    $errors = [];

    // Validate name
    $name = trim($name);
    if (empty($name) || strlen($name) > 100) {
        $errors[] = 'Name is required and must be under 100 characters.';
    }

    // Validate email
    $email = trim(strtolower($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Validate password
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }

    // Confirm match
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check duplicate email
    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors, 'user_id' => null];
    }

    // Create user
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $hash]);
    $userId = (int)db()->lastInsertId();

    return ['success' => true, 'errors' => [], 'user_id' => $userId];
}

/**
 * Attempt to log in a user.
 *
 * @return array ['success' => bool, 'errors' => string[]]
 */
function login_user(string $email, string $password): array
{
    $errors = [];
    $email = trim(strtolower($email));

    // Check rate limiting
    if (is_login_locked($email)) {
        $errors[] = 'Too many login attempts. Please try again in ' . LOGIN_LOCKOUT_MINUTES . ' minutes.';
        return ['success' => false, 'errors' => $errors];
    }

    // Find user
    $stmt = db()->prepare('SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Log failed attempt
        log_login_attempt($email);
        $errors[] = 'Invalid email or password.';
        return ['success' => false, 'errors' => $errors];
    }

    if (!$user['is_active']) {
        $errors[] = 'Your account has been deactivated. Please contact support.';
        return ['success' => false, 'errors' => $errors];
    }

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);

    // Set session variables
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Clear login attempts for this email
    clear_login_attempts($email);

    return ['success' => true, 'errors' => []];
}

/**
 * Log out the current user.
 */
function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Get the currently logged-in user's full data.
 */
function get_current_user_data(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, name, email, profile_picture, role, created_at FROM users WHERE id = ?');
    $stmt->execute([current_user_id()]);
    return $stmt->fetch() ?: null;
}

// ── Rate Limiting ────────────────────────────────

/**
 * Log a failed login attempt.
 */
function log_login_attempt(string $email): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = db()->prepare('INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)');
    $stmt->execute([$ip, $email]);
}

/**
 * Check if login is locked for an email/IP.
 */
function is_login_locked(string $email): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $window = date('Y-m-d H:i:s', strtotime('-' . LOGIN_LOCKOUT_MINUTES . ' minutes'));

    $stmt = db()->prepare(
        'SELECT COUNT(*) as attempts FROM login_attempts 
         WHERE (ip_address = ? OR email = ?) AND attempted_at > ?'
    );
    $stmt->execute([$ip, $email, $window]);
    $result = $stmt->fetch();

    return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
}

/**
 * Clear login attempts after successful login.
 */
function clear_login_attempts(string $email): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = db()->prepare('DELETE FROM login_attempts WHERE ip_address = ? OR email = ?');
    $stmt->execute([$ip, $email]);
}
