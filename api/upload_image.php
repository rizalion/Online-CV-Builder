<?php
/**
 * API: Upload Profile Image
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/upload.php';

require_login();
csrf_guard();

if (!isset($_FILES['profile_picture'])) {
    json_response(['success' => false, 'errors' => ['No file uploaded.']], 400);
}

$result = handle_profile_upload($_FILES['profile_picture']);

if ($result['success']) {
    // Update user record
    $stmt = db()->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
    $stmt->execute([$result['path'], current_user_id()]);

    json_response([
        'success' => true,
        'path' => $result['path'],
        'url' => BASE_URL . '/' . $result['path'],
    ]);
} else {
    json_response(['success' => false, 'errors' => $result['errors']], 422);
}
