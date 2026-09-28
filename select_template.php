<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$cvId = (int)($_GET['cv_id'] ?? $_SESSION['current_cv_id'] ?? 0);

if ($cvId <= 0) {
    die("CV ID not found.");
}

$templates = [
    1 => ['name' => 'Classic Professional', 'desc' => 'Two-column traditional layout with blue accents.'],
    2 => ['name' => 'Modern Minimal', 'desc' => 'Clean single-column layout with timeline.'],
    3 => ['name' => 'Creative Dark', 'desc' => 'Dark neon theme with a glowing avatar.']
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Template - CV Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/darkmode.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center mb-5">Select Your Template</h2>
        <div class="row g-4">
            <?php foreach ($templates as $id => $tpl): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm template-card text-center p-4">
                    <h3><?= $tpl['name'] ?></h3>
                    <p class="text-muted"><?= $tpl['desc'] ?></p>
                    <a href="preview.php?cv_id=<?= $cvId ?>&template=<?= $id ?>" class="btn btn-primary mt-auto">Select & Preview</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="<?= BASE_URL ?>/assets/js/darkmode.js"></script>
</body>
</html>
