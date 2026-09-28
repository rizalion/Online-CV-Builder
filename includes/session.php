<?php
/**
 * Session Management & CSRF Protection
 * 
 * Must be included at the top of every page/endpoint.
 */

require_once __DIR__ . '/config.php';

// Configure session parameters before starting
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);

    // Enable secure cookies if HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

/**
 * Generate or retrieve the CSRF token for this session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden input field containing the CSRF token.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Validate a submitted CSRF token.
 * Checks both POST body and X-CSRF-Token header (for AJAX).
 */
function csrf_verify(): bool
{
    $token = $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Abort with 403 if CSRF validation fails.
 */
function csrf_guard(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        http_response_code(403);
        die(json_encode(['success' => false, 'errors' => ['Invalid security token. Please refresh and try again.']]));
    }
}

/**
 * Check if a user is currently logged in.
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the current user's ID.
 */
function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the current user's role.
 */
function current_user_role(): string
{
    return $_SESSION['user_role'] ?? 'user';
}

/**
 * Check if the current user is an admin.
 */
function is_admin(): bool
{
    return current_user_role() === 'admin';
}

/**
 * Require login — redirect to login page if not authenticated.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Require admin role — send 403 if not admin.
 */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Access denied.');
    }
}

/**
 * Set a flash message to display on the next page load.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the flash message.
 */
function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
