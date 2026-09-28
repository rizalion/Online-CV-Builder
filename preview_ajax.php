<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Invalid request.');
if (!csrf_verify()) die('Invalid CSRF token.');

// Map nested arrays straight through (htmlspecialchar handles escaping in templates)
$cv = [
    'profile' => [
        'full_name' => htmlspecialchars($_POST['full_name'] ?? 'Your Name', ENT_QUOTES, 'UTF-8'),
        'email' => htmlspecialchars($_POST['email'] ?? 'email@example.com', ENT_QUOTES, 'UTF-8'),
        'phone' => htmlspecialchars($_POST['phone'] ?? '(123) 456-7890', ENT_QUOTES, 'UTF-8'),
        'address' => htmlspecialchars($_POST['address'] ?? 'City, Country', ENT_QUOTES, 'UTF-8'),
        'linkedin_url' => htmlspecialchars($_POST['linkedin_url'] ?? '', ENT_QUOTES, 'UTF-8'),
        'portfolio_url' => htmlspecialchars($_POST['portfolio_url'] ?? '', ENT_QUOTES, 'UTF-8'),
        'professional_summary' => htmlspecialchars($_POST['professional_summary'] ?? 'Summary goes here...', ENT_QUOTES, 'UTF-8'),
        'profile_picture' => '' 
    ],
    'education' => [],
    'experience' => [],
    'skills' => json_decode($_POST['skills_json'] ?? '[]', true) ?: [],
    'certifications' => [],
    'projects' => []
];

// Education
if (!empty($_POST['education']) && is_array($_POST['education'])) {
    foreach ($_POST['education'] as $edu) {
        if(empty(trim($edu['institution'] ?? ''))) continue;
        $cv['education'][] = [
            'institution' => htmlspecialchars($edu['institution'], ENT_QUOTES, 'UTF-8'),
            'degree' => htmlspecialchars($edu['degree'] ?? '', ENT_QUOTES, 'UTF-8'),
            'field_of_study' => htmlspecialchars($edu['field_of_study'] ?? '', ENT_QUOTES, 'UTF-8'),
            'start_date' => htmlspecialchars($edu['start_date'] ?? '', ENT_QUOTES, 'UTF-8'),
            'end_date' => htmlspecialchars($edu['end_date'] ?? '', ENT_QUOTES, 'UTF-8'),
            'grade' => htmlspecialchars($edu['grade'] ?? '', ENT_QUOTES, 'UTF-8')
        ];
    }
}

// Experience
if (!empty($_POST['experience']) && is_array($_POST['experience'])) {
    foreach ($_POST['experience'] as $exp) {
        if(empty(trim($exp['company'] ?? ''))) continue;
        $cv['experience'][] = [
            'company' => htmlspecialchars($exp['company'], ENT_QUOTES, 'UTF-8'),
            'job_title' => htmlspecialchars($exp['job_title'] ?? '', ENT_QUOTES, 'UTF-8'),
            'location' => htmlspecialchars($exp['location'] ?? '', ENT_QUOTES, 'UTF-8'),
            'start_date' => htmlspecialchars($exp['start_date'] ?? '', ENT_QUOTES, 'UTF-8'),
            'end_date' => htmlspecialchars($exp['end_date'] ?? '', ENT_QUOTES, 'UTF-8'),
            'responsibilities' => htmlspecialchars($exp['responsibilities'] ?? '', ENT_QUOTES, 'UTF-8')
        ];
    }
}

// Skills
foreach($cv['skills'] as &$sk) {
    $sk['skill_name'] = htmlspecialchars($sk['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $sk['proficiency'] = htmlspecialchars($sk['prof'] ?? '', ENT_QUOTES, 'UTF-8');
}

$templateId = (int)($_POST['template_id'] ?? 1);

ob_start();
if ($templateId === 1) include __DIR__ . '/templates/template1.php';
elseif ($templateId === 2) include __DIR__ . '/templates/template2.php';
elseif ($templateId === 3) include __DIR__ . '/templates/template3.php';
else include __DIR__ . '/templates/template1.php';
$html = ob_get_clean();

echo $html;
