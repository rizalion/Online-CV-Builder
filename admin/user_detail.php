<?php
/**
 * Admin: User Detail
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_admin();

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    redirect(BASE_URL . '/admin/users.php');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('danger', 'User not found.');
    redirect(BASE_URL . '/admin/users.php');
}

$cvs = get_user_cvs($userId);

// Download count for this user's CVs
$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM cv_downloads d 
     JOIN cv_profiles c ON d.cv_id = c.id 
     WHERE c.user_id = ?'
);
$stmt->execute([$userId]);
$downloadCount = $stmt->fetchColumn();

$pageTitle = 'User: ' . $user['name'];
$extraCss = ['admin.css'];
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container py-4">
    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-ghost btn-sm mb-3">
        <i class="fas fa-arrow-left me-1"></i> Back to Users
    </a>

    <div class="row g-4">
        <!-- User Info -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center" style="padding: 32px;">
                    <?php $pic = profile_picture_url($user['profile_picture']); ?>
                    <img src="<?= e($pic) ?>" alt="Avatar" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:16px;border:3px solid var(--border-color);">
                    <h4 style="margin-bottom:4px;"><?= e($user['name']) ?></h4>
                    <p class="text-muted mb-3"><?= e($user['email']) ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <?php if ($user['is_active']): ?>
                            <span class="badge" style="background:rgba(16,185,129,0.1);color:#059669;padding:6px 12px;">Active</span>
                        <?php else: ?>
                            <span class="badge" style="background:rgba(239,68,68,0.1);color:#dc2626;padding:6px 12px;">Inactive</span>
                        <?php endif; ?>
                        <span class="badge" style="background:var(--primary-glow);color:var(--primary);padding:6px 12px;"><?= e($user['role']) ?></span>
                    </div>

                    <p class="text-muted small mb-0">Registered <?= time_ago($user['created_at']) ?></p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-body" style="padding: 24px;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">CVs Created</span>
                        <strong><?= count($cvs) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Downloads</span>
                        <strong><?= number_format($downloadCount) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- User CVs -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" style="padding: 24px;">
                    <h5 style="font-size:1rem;margin-bottom:16px;"><i class="fas fa-file-alt me-2"></i>User's CVs</h5>
                    <?php if (empty($cvs)): ?>
                        <p class="text-muted">This user hasn't created any CVs yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cvs as $cv): ?>
                                    <tr>
                                        <td><strong><?= e($cv['full_name']) ?></strong></td>
                                        <td class="text-muted"><?= e($cv['email']) ?></td>
                                        <td class="text-muted"><?= time_ago($cv['created_at']) ?></td>
                                        <td class="text-muted"><?= time_ago($cv['updated_at']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
