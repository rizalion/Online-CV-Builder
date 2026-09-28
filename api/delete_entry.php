<?php
/**
 * API: Delete Entry
 * 
 * Deletes a CV profile or a specific sub-entry.
 */
require_once dirname(__DIR__) . '/includes/auth.php';

require_login();
csrf_guard();

$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$userId = current_user_id();

if ($id <= 0) {
    json_response(['success' => false, 'errors' => ['Invalid ID.']], 400);
}

$pdo = db();

try {
    switch ($type) {
        case 'cv':
            // Verify ownership
            $stmt = $pdo->prepare('SELECT id FROM cv_profiles WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
            if (!$stmt->fetch()) {
                json_response(['success' => false, 'errors' => ['CV not found.']], 404);
            }
            // CASCADE will delete all child rows
            $pdo->prepare('DELETE FROM cv_profiles WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
            break;

        case 'education':
        case 'experience':
        case 'skills':
        case 'certifications':
        case 'projects':
            // Verify ownership through cv_profiles join
            $stmt = $pdo->prepare(
                "SELECT t.id FROM {$type} t 
                 JOIN cv_profiles c ON t.cv_id = c.id 
                 WHERE t.id = ? AND c.user_id = ?"
            );
            $stmt->execute([$id, $userId]);
            if (!$stmt->fetch()) {
                json_response(['success' => false, 'errors' => ['Entry not found.']], 404);
            }
            $pdo->prepare("DELETE FROM {$type} WHERE id = ?")->execute([$id]);
            break;

        default:
            json_response(['success' => false, 'errors' => ['Invalid type.']], 400);
    }

    json_response(['success' => true, 'message' => 'Deleted successfully.']);
} catch (Exception $e) {
    error_log('Delete error: ' . $e->getMessage());
    json_response(['success' => false, 'errors' => ['Failed to delete.']], 500);
}
