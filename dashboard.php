<?php
/**
 * User Dashboard — List and manage CVs
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$user = get_current_user_data();
$cvs = get_user_cvs(current_user_id());

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="animate-in">Welcome, <?= e($user['name']) ?> 👋</h1>
                <p class="lead animate-in animate-in-delay-1">
                    Manage your CVs, create new ones, and download them as PDF.
                </p>
            </div>
            <div class="col-md-4 text-md-end animate-in animate-in-delay-2">
                <a href="<?= BASE_URL ?>/form.php" class="btn btn-primary btn-lg" id="dashNewCv">
                    <i class="fas fa-plus me-2"></i> New CV
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($cvs)): ?>
        <!-- Empty State -->
        <div class="empty-state animate-in animate-in-delay-2">
            <div class="empty-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3>No CVs yet</h3>
            <p>Create your first professional CV in just a few minutes.</p>
            <a href="<?= BASE_URL ?>/form.php" class="btn btn-primary" id="emptyCtaNewCv">
                <i class="fas fa-plus me-2"></i> Create My First CV
            </a>
        </div>
    <?php else: ?>
        <!-- CV List -->
        <div class="row g-4 mb-5">
            <?php foreach ($cvs as $index => $cv): ?>
                <div class="col-md-6 col-lg-4 animate-in animate-in-delay-<?= min($index + 1, 4) ?>">
                    <div class="card cv-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <h5 class="cv-name"><?= e($cv['full_name']) ?></h5>
                                    <p class="cv-email"><?= e($cv['email']) ?></p>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary badge-pill">CV</span>
                            </div>
                            <p class="cv-date">
                                <i class="fas fa-clock me-1"></i>
                                Updated <?= time_ago($cv['updated_at']) ?>
                            </p>
                            <div class="cv-actions">
                                <a href="<?= BASE_URL ?>/form.php?cv_id=<?= (int)$cv['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm" id="cvEdit<?= (int)$cv['id'] ?>">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <a href="<?= BASE_URL ?>/select_template.php?cv_id=<?= (int)$cv['id'] ?>" 
                                   class="btn btn-primary btn-sm" id="cvPreview<?= (int)$cv['id'] ?>">
                                    <i class="fas fa-eye me-1"></i> Preview
                                </a>
                                <button class="btn btn-ghost btn-sm" onclick="deleteCv(<?= (int)$cv['id'] ?>)" 
                                        id="cvDelete<?= (int)$cv['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
async function deleteCv(cvId) {
    if (!confirm('Are you sure you want to delete this CV? This cannot be undone.')) return;

    try {
        const result = await apiPost('<?= BASE_URL ?>/api/delete_entry.php', {
            type: 'cv',
            id: cvId
        });
        if (result.success) {
            showToast('CV deleted successfully.', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(result.errors?.[0] || 'Failed to delete CV.', 'danger');
        }
    } catch (err) {
        showToast('An error occurred. Please try again.', 'danger');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
