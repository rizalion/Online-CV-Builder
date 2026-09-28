<?php
/**
 * API: Save Section Data (Steps 2-4)
 * 
 * Handles education, experience, skills, certifications, and projects.
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/validation.php';

require_login();
csrf_guard();

$pdo = db();
$userId = current_user_id();
$cvId = (int)($_POST['cv_id'] ?? 0);
$section = $_POST['section'] ?? '';

// Verify CV ownership
$stmt = $pdo->prepare('SELECT id FROM cv_profiles WHERE id = ? AND user_id = ?');
$stmt->execute([$cvId, $userId]);
if (!$stmt->fetch()) {
    json_response(['success' => false, 'errors' => ['CV not found.']], 404);
}

try {
    $pdo->beginTransaction();

    switch ($section) {
        case 'education':
            $entries = json_decode($_POST['entries'] ?? '[]', true);
            // Delete existing and re-insert
            $pdo->prepare('DELETE FROM education WHERE cv_id = ?')->execute([$cvId]);
            $stmt = $pdo->prepare(
                'INSERT INTO education (cv_id, institution, degree, field_of_study, start_date, end_date, grade, sort_order) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($entries as $entry) {
                $stmt->execute([
                    $cvId,
                    trim($entry['institution'] ?? ''),
                    trim($entry['degree'] ?? ''),
                    trim($entry['field_of_study'] ?? '') ?: null,
                    $entry['start_date'] ?: null,
                    $entry['end_date'] ?: null,
                    trim($entry['grade'] ?? '') ?: null,
                    (int)($entry['sort_order'] ?? 0),
                ]);
            }
            break;

        case 'experience':
            $entries = json_decode($_POST['entries'] ?? '[]', true);
            $pdo->prepare('DELETE FROM experience WHERE cv_id = ?')->execute([$cvId]);
            $stmt = $pdo->prepare(
                'INSERT INTO experience (cv_id, company, job_title, location, start_date, end_date, description, sort_order) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($entries as $entry) {
                $stmt->execute([
                    $cvId,
                    trim($entry['company'] ?? ''),
                    trim($entry['job_title'] ?? ''),
                    trim($entry['location'] ?? '') ?: null,
                    $entry['start_date'] ?: null,
                    $entry['end_date'] ?: null,
                    trim($entry['description'] ?? '') ?: null,
                    (int)($entry['sort_order'] ?? 0),
                ]);
            }
            break;

        case 'all_step4':
            // Skills
            $skills = json_decode($_POST['skills'] ?? '[]', true);
            $pdo->prepare('DELETE FROM skills WHERE cv_id = ?')->execute([$cvId]);
            $stmt = $pdo->prepare(
                'INSERT INTO skills (cv_id, skill_name, proficiency_level, sort_order) VALUES (?, ?, ?, ?)'
            );
            foreach ($skills as $skill) {
                if (!empty(trim($skill['skill_name'] ?? ''))) {
                    $level = max(1, min(5, (int)($skill['proficiency_level'] ?? 3)));
                    $stmt->execute([
                        $cvId,
                        trim($skill['skill_name']),
                        $level,
                        (int)($skill['sort_order'] ?? 0),
                    ]);
                }
            }

            // Certifications
            $certs = json_decode($_POST['certifications'] ?? '[]', true);
            $pdo->prepare('DELETE FROM certifications WHERE cv_id = ?')->execute([$cvId]);
            $stmt = $pdo->prepare(
                'INSERT INTO certifications (cv_id, title, issuer, issue_date, credential_url, sort_order) 
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($certs as $cert) {
                if (!empty(trim($cert['title'] ?? ''))) {
                    $stmt->execute([
                        $cvId,
                        trim($cert['title']),
                        trim($cert['issuer'] ?? '') ?: null,
                        $cert['issue_date'] ?: null,
                        trim($cert['credential_url'] ?? '') ?: null,
                        (int)($cert['sort_order'] ?? 0),
                    ]);
                }
            }

            // Projects
            $projects = json_decode($_POST['projects'] ?? '[]', true);
            $pdo->prepare('DELETE FROM projects WHERE cv_id = ?')->execute([$cvId]);
            $stmt = $pdo->prepare(
                'INSERT INTO projects (cv_id, title, description, tech_stack, project_url, sort_order) 
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($projects as $proj) {
                if (!empty(trim($proj['title'] ?? ''))) {
                    $stmt->execute([
                        $cvId,
                        trim($proj['title']),
                        trim($proj['description'] ?? '') ?: null,
                        trim($proj['tech_stack'] ?? '') ?: null,
                        trim($proj['project_url'] ?? '') ?: null,
                        (int)($proj['sort_order'] ?? 0),
                    ]);
                }
            }
            break;

        default:
            $pdo->rollBack();
            json_response(['success' => false, 'errors' => ['Invalid section.']], 400);
    }

    $pdo->commit();
    json_response(['success' => true, 'message' => 'Section saved.']);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Save section error: ' . $e->getMessage());
    json_response(['success' => false, 'errors' => ['Failed to save data.']], 500);
}
