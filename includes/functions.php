<?php
/**
 * Shared Utility Functions
 */

require_once __DIR__ . '/config.php';

/**
 * Safely escape output for HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a date string for display.
 */
function format_date(?string $date, string $format = 'M Y'): string
{
    if (empty($date)) {
        return 'Present';
    }
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Format a full date with day.
 */
function format_full_date(?string $date): string
{
    return format_date($date, 'M d, Y');
}

/**
 * Get a proficiency label from a numeric level.
 */
function proficiency_label(int $level): string
{
    return match ($level) {
        1 => 'Beginner',
        2 => 'Elementary',
        3 => 'Intermediate',
        4 => 'Advanced',
        5 => 'Expert',
        default => 'Unknown',
    };
}

/**
 * Get proficiency percentage for visual bars.
 */
function proficiency_percent(int $level): int
{
    return $level * 20; // 1=20%, 2=40%, 3=60%, 4=80%, 5=100%
}

/**
 * Build a full asset URL.
 */
function asset_url(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Get the profile picture URL or a default placeholder.
 */
function profile_picture_url(?string $path): string
{
    if (!empty($path) && file_exists(ROOT_PATH . $path)) {
        return BASE_URL . '/' . $path;
    }
    return asset_url('images/placeholders/default-avatar.svg');
}

/**
 * Redirect to a URL and exit.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Send a JSON response and exit.
 */
function json_response(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get all CV data for a given cv_id.
 */
function get_cv_data(int $cvId): ?array
{
    $pdo = db();

    // Profile
    $stmt = $pdo->prepare('SELECT * FROM cv_profiles WHERE id = ?');
    $stmt->execute([$cvId]);
    $profile = $stmt->fetch();
    if (!$profile) {
        return null;
    }

    // User profile picture
    $stmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ?');
    $stmt->execute([$profile['user_id']]);
    $user = $stmt->fetch();

    // Education
    $stmt = $pdo->prepare('SELECT * FROM education WHERE cv_id = ? ORDER BY sort_order ASC, start_date DESC');
    $stmt->execute([$cvId]);
    $education = $stmt->fetchAll();

    // Experience
    $stmt = $pdo->prepare('SELECT * FROM experience WHERE cv_id = ? ORDER BY sort_order ASC, start_date DESC');
    $stmt->execute([$cvId]);
    $experience = $stmt->fetchAll();

    // Skills
    $stmt = $pdo->prepare('SELECT * FROM skills WHERE cv_id = ? ORDER BY sort_order ASC');
    $stmt->execute([$cvId]);
    $skills = $stmt->fetchAll();

    // Certifications
    $stmt = $pdo->prepare('SELECT * FROM certifications WHERE cv_id = ? ORDER BY sort_order ASC');
    $stmt->execute([$cvId]);
    $certifications = $stmt->fetchAll();

    // Projects
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE cv_id = ? ORDER BY sort_order ASC');
    $stmt->execute([$cvId]);
    $projects = $stmt->fetchAll();

    return [
        'personal' => [
            'full_name' => $profile['full_name'],
            'email'     => $profile['email'],
            'phone'     => $profile['phone'],
            'address'   => $profile['address'],
            'linkedin'  => $profile['linkedin'],
            'website'   => $profile['website'],
        ],
        'profile_picture' => $user['profile_picture'] ?? null,
        'summary'         => $profile['summary'],
        'education'       => $education,
        'experience'      => $experience,
        'skills'          => $skills,
        'certifications'  => $certifications,
        'projects'        => $projects,
        'cv_id'           => $cvId,
        'user_id'         => $profile['user_id'],
    ];
}

/**
 * Get all CVs for a user.
 */
function get_user_cvs(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT id, full_name, email, created_at, updated_at 
         FROM cv_profiles WHERE user_id = ? ORDER BY updated_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Generate a safe PDF filename from a user's name.
 */
function safe_pdf_filename(string $name): string
{
    $clean = preg_replace('/[^A-Za-z0-9_\- ]/', '', $name);
    $clean = str_replace(' ', '_', trim($clean));
    return $clean . '_CV_' . date('Y-m-d') . '.pdf';
}

/**
 * Time ago helper for admin panel.
 */
function time_ago(string $datetime): string
{
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}
