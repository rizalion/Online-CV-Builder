<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cv_repository.php';
require_admin();

$repo = new CVRepository();
$stats = $repo->get_admin_stats();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background: #343a40; }
        .sidebar a { color: #fff; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover { background: #495057; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar flex-shrink-0" style="width: 250px;">
            <div class="p-4 text-white border-bottom border-secondary">
                <h4>CV Admin</h4>
            </div>
            <a href="dashboard.php" class="bg-secondary"><i class="fas fa-home"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-users"></i> Users</a>
            <a href="../dashboard.php"><i class="fas fa-arrow-left"></i> Back to App</a>
        </div>
        <div class="flex-grow-1 p-4 bg-light">
            <h2 class="mb-4">Dashboard</h2>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <h2><?= $stats['users'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total CVs Created</h5>
                            <h2><?= $stats['cvs'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Downloads</h5>
                            <h2><?= $stats['downloads'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Registrations</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent'] as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $u['created_at'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
