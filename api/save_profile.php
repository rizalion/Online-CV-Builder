<?php
/**
 * API: Save CV Profile (Step 1)
 * 
 * Creates or updates the cv_profiles row and handles profile picture upload.
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/includes/upload.php';

require_login();
csrf_guard();

// Handle theme save (separate action)
if (($_POST['action'] ?? '') === 'save_theme') {
    $_SESSION['theme'] = ($_POST['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
    json_response(['success' => true]);
}

$pdo = db();
$userId = current_user_id();

// Collect and validate input
$fullName = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');
$linkedin = trim($_POST['linkedin'] ?? '');
$website  = trim($_POST['website'] ?? '');
$summary  = trim($_POST['summary'] ?? '');
$cvId     = (int)($_POST['cv_id'] ?? 0);

$errors = collect_errors([
    validate_required($fullName, 'Full name', 150),
    validate_email($email),
    validate_phone($phone),
    validate_url($linkedin, 'LinkedIn URL'),
    validate_url($website, 'Website URL'),
    validate_optional($summary, 'Summary', 1000),
]);

if (!empty($errors)) {
    json_response(['success' => false, 'errors' => $errors], 422);
}

// Handle profile picture upload
$picturePath = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadResult = handle_profile_upload($_FILES['profile_picture']);
    if (!$uploadResult['success']) {
        json_response(['success' => false, 'errors' => $uploadResult['errors']], 422);
    }
    $picturePath = $uploadResult['path'];

    // Update user's profile picture
    $stmt = $pdo->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
    $stmt->execute([$picturePath, $userId]);
}

try {
    $pdo->beginTransaction();

    if ($cvId > 0) {
        // Verify ownership and update
        $stmt = $pdo->prepare('SELECT id FROM cv_profiles WHERE id = ? AND user_id = ?');
        $stmt->execute([$cvId, $userId]);
        if (!$stmt->fetch()) {
            $pdo->rollBack();
            json_response(['success' => false, 'errors' => ['CV not found.']], 404);
        }

        $stmt = $pdo->prepare(
            'UPDATE cv_profiles SET full_name = ?, email = ?, phone = ?, address = ?, 
             linkedin = ?, website = ?, summary = ? WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$fullName, $email, $phone, $address, $linkedin, $website, $summary, $cvId, $userId]);
    } else {
        // Create new
        $stmt = $pdo->prepare(
            'INSERT INTO cv_profiles (user_id, full_name, email, phone, address, linkedin, website, summary) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $fullName, $email, $phone, $address, $linkedin, $website, $summary]);
        $cvId = (int)$pdo->lastInsertId();
    }

    $pdo->commit();

    json_response([
        'success' => true,
        'cv_id' => $cvId,
        'message' => 'Profile saved successfully.',
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Save profile error: ' . $e->getMessage());
    json_response(['success' => false, 'errors' => ['Failed to save profile.']], 500);
}
