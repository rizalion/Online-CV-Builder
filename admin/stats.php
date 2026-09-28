<?php
/**
 * Admin: Download & Template Analytics
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_admin();

$pdo = db();

// Template popularity
$templateStats = $pdo->query(
    'SELECT t.name, t.slug, 
            COUNT(d.id) as download_count,
            SUM(CASE WHEN d.format = "pdf" THEN 1 ELSE 0 END) as pdf_count,
            SUM(CASE WHEN d.format = "print" THEN 1 ELSE 0 END) as print_count
     FROM templates t 
     LEFT JOIN cv_downloads d ON t.id = d.template_id 
     GROUP BY t.id 
     ORDER BY download_count DESC'
)->fetchAll();

// Average sections per CV
$avgSections = $pdo->query(
    'SELECT 
        ROUND(AVG(edu_count), 1) as avg_edu,
        ROUND(AVG(exp_count), 1) as avg_exp,
        ROUND(AVG(skill_count), 1) as avg_skills
     FROM (
        SELECT c.id,
            (SELECT COUNT(*) FROM education WHERE cv_id = c.id) as edu_count,
            (SELECT COUNT(*) FROM experience WHERE cv_id = c.id) as exp_count,
            (SELECT COUNT(*) FROM skills WHERE cv_id = c.id) as skill_count
        FROM cv_profiles c
     ) as stats'
)->fetch();

$pageTitle = 'Analytics';
$extraCss = ['admin.css'];
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 style="font-size: 1.5rem;"><i class="fas fa-chart-bar me-2 text-gradient"></i>Analytics</h1>
            <p class="text-muted mb-0">Template usage and CV completion metrics.</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-ghost btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <!-- Template Stats -->
    <div class="card mb-4">
        <div class="card-body" style="padding: 24px;">
            <h5 style="font-size: 1rem; margin-bottom: 16px;">
                <i class="fas fa-palette me-2"></i>Template Popularity
            </h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Template</th>
                            <th>Total Downloads</th>
                            <th>PDF</th>
                            <th>Print</th>
                            <th>Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalDl = max(1, array_sum(array_column($templateStats, 'download_count')));
                        foreach ($templateStats as $ts): 
                            $pct = round(($ts['download_count'] / $totalDl) * 100);
                        ?>
                        <tr>
                            <td><strong><?= e($ts['name']) ?></strong></td>
                            <td><?= number_format($ts['download_count']) ?></td>
                            <td><?= number_format($ts['pdf_count']) ?></td>
                            <td><?= number_format($ts['print_count']) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="flex:1;height:8px;background:var(--border-color);border-radius:4px;overflow:hidden;">
                                        <div style="width:<?= $pct ?>%;height:100%;background:var(--primary);border-radius:4px;"></div>
                                    </div>
                                    <span class="text-muted" style="font-size:0.8rem;min-width:35px;"><?= $pct ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CV Completion Metrics -->
    <div class="card">
        <div class="card-body" style="padding: 24px;">
            <h5 style="font-size: 1rem; margin-bottom: 16px;">
                <i class="fas fa-clipboard-check me-2"></i>Average CV Completeness
            </h5>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="stat-value" style="font-size:2.5rem; color: var(--primary);"><?= $avgSections['avg_edu'] ?? 0 ?></div>
                    <div class="stat-label">Avg Education Entries</div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="stat-value" style="font-size:2.5rem; color: #10b981;"><?= $avgSections['avg_exp'] ?? 0 ?></div>
                    <div class="stat-label">Avg Experience Entries</div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="stat-value" style="font-size:2.5rem; color: #f59e0b;"><?= $avgSections['avg_skills'] ?? 0 ?></div>
                    <div class="stat-label">Avg Skills Listed</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
