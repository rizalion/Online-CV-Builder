<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();

if (isset($_POST['delete_id'])) {
    if (csrf_verify()) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role != "admin"');
        $stmt->execute([$_POST['delete_id']]);
        header('Location: users.php');
        exit;
    }
}

$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$total = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$pages = ceil($total / $limit);

$stmt = $pdo->prepare('
    SELECT u.id, u.name, u.email, u.created_at, COUNT(c.id) as cv_count
    FROM users u
    LEFT JOIN cv_profiles c ON u.id = c.user_id
    GROUP BY u.id
    ORDER BY u.id DESC
    LIMIT ? OFFSET ?
');
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
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
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="users.php" class="bg-secondary"><i class="fas fa-users"></i> Users</a>
            <a href="../dashboard.php"><i class="fas fa-arrow-left"></i> Back to App</a>
        </div>
        <div class="flex-grow-1 p-4 bg-light">
            <h2 class="mb-4">Manage Users</h2>
            
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>CVs Created</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge bg-primary rounded-pill"><?= $u['cv_count'] ?></span></td>
                                <td><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($pages > 1): ?>
                <div class="card-footer bg-white">
                    <ul class="pagination mb-0">
                        <?php for($i=1; $i<=$pages; $i++): ?>
                            <li class="page-item <?= $i==$page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
