<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cv_repository.php';
require_login();

$cvId = filter_input(INPUT_GET, 'cv_id', FILTER_VALIDATE_INT);
$templateId = filter_input(INPUT_GET, 'template', FILTER_VALIDATE_INT) ?: 1;
$userId = (int)$_SESSION['user_id'];

if (!$cvId || $cvId <= 0) die("Invalid CV.");

$repo = new CVRepository();
$cv = $repo->get_cv_by_id($cvId, $userId);

if (!$cv) die("CV not found or access denied.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Preview CV - CV Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body class="bg-light">
    
    <div class="container py-3 no-print">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
            <div>
                <a href="form.php" class="btn btn-outline-secondary"><i class="fas fa-edit"></i> Edit CV</a>
                <a href="select_template.php?cv_id=<?= $cvId ?>" class="btn btn-outline-info"><i class="fas fa-paint-roller"></i> Change Template</a>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
                <a href="generate_pdf.php?cv_id=<?= $cvId ?>&template=<?= $templateId ?>" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="shadow" style="background:#fff;">
            <?php 
                if ($templateId === 1) include __DIR__ . '/templates/template1.php';
                elseif ($templateId === 2) include __DIR__ . '/templates/template2.php';
                elseif ($templateId === 3) include __DIR__ . '/templates/template3.php';
                else include __DIR__ . '/templates/template1.php';
            ?>
        </div>
    </div>

</body>
</html>
