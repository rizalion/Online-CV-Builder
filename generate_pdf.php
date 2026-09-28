<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cv_repository.php';
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'><h2>PDF Library Missing!</h2><p>Composer dependencies are not installed.</p><p>You must install Composer and run <strong>composer install</strong> in the project folder to enable PDF downloads.</p><button onclick='window.history.back()'>Go Back</button></div>");
}
require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

require_login();

$cvId = filter_input(INPUT_GET, 'cv_id', FILTER_VALIDATE_INT);
$templateId = filter_input(INPUT_GET, 'template', FILTER_VALIDATE_INT) ?: 1;
$userId = (int)$_SESSION['user_id'];

if (!$cvId || $cvId <= 0) die("Invalid CV.");

$repo = new CVRepository();
$cv = $repo->get_cv_by_id($cvId, $userId);

if (!$cv) die("CV not found or access denied.");

// Log download
$repo->log_download($cvId, $templateId);

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { margin: 0; padding: 0; background: #fff; }
</style>
</head>
<body>
<?php
if ($templateId === 1) include __DIR__ . '/templates/template1.php';
elseif ($templateId === 2) include __DIR__ . '/templates/template2.php';
elseif ($templateId === 3) include __DIR__ . '/templates/template3.php';
else include __DIR__ . '/templates/template1.php';
?>
</body>
</html>
<?php
$html = ob_get_clean();

// Replace local images with base64 for dompdf (isRemoteEnabled=false for security)
if (!empty($cv['profile']['profile_picture'])) {
    $path = __DIR__ . '/' . str_replace(BASE_URL . '/', '', $cv['profile']['profile_picture']);
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $html = str_replace(htmlspecialchars(BASE_URL . '/' . $cv['profile']['profile_picture'], ENT_QUOTES, 'UTF-8'), $base64, $html);
    }
}

// Dompdf setup
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false); // Hardened: false
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Sanitize filename
$cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $cv['profile']['full_name']);
$filename = 'CV_' . $cleanName . '_' . date('Y-m-d') . '.pdf';

// Prevent output buffering corruption
if (ob_get_length()) {
    ob_end_clean();
}

$dompdf->stream($filename, ["Attachment" => true]);
