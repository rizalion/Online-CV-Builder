<?php
/**
 * API: Get CV Data (JSON)
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_login();

$cvId = (int)($_GET['id'] ?? 0);
if ($cvId <= 0) {
    json_response(['success' => false, 'errors' => ['Invalid CV ID.']], 400);
}

$cvData = get_cv_data($cvId);
if (!$cvData) {
    json_response(['success' => false, 'errors' => ['CV not found.']], 404);
}

// Check ownership (unless admin)
if ($cvData['user_id'] !== current_user_id() && !is_admin()) {
    json_response(['success' => false, 'errors' => ['Access denied.']], 403);
}

json_response(['success' => true, 'data' => $cvData]);
