<?php
/**
 * API: Log Download Event
 */
require_once dirname(__DIR__) . '/includes/auth.php';

require_login();
csrf_guard();

$cvId = (int)($_POST['cv_id'] ?? 0);
$templateId = (int)($_POST['template_id'] ?? 0);
$format = in_array($_POST['format'] ?? '', ['pdf', 'print']) ? $_POST['format'] : 'pdf';

if ($cvId <= 0) {
    json_response(['success' => false, 'errors' => ['Invalid CV ID.']], 400);
}

// Verify ownership
$stmt = db()->prepare('SELECT id FROM cv_profiles WHERE id = ? AND user_id = ?');
$stmt->execute([$cvId, current_user_id()]);
if (!$stmt->fetch()) {
    json_response(['success' => false, 'errors' => ['CV not found.']], 404);
}

$stmt = db()->prepare(
    'INSERT INTO cv_downloads (cv_id, template_id, format) VALUES (?, ?, ?)'
);
$stmt->execute([$cvId, $templateId ?: null, $format]);

json_response(['success' => true]);
