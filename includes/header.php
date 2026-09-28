<?php
/**
 * Global Header Partial
 * 
 * Includes the HTML head, navbar, and opens the main content area.
 * Set $pageTitle before including this file.
 */
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

$pageTitle = isset($pageTitle) ? e($pageTitle) . ' — ' . APP_NAME : APP_NAME;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($_SESSION['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Build professional resumes and CVs with beautiful templates. Free online CV builder.">
    <title><?= $pageTitle ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- App Styles -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/darkmode.css" rel="stylesheet">
    <?php if (isset($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link href="<?= BASE_URL ?>/assets/css/<?= e($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/">
                <i class="fas fa-file-alt brand-icon"></i>
                <span class="brand-text"><?= APP_NAME ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/dashboard.php" id="navDashboard">
                                <i class="fas fa-th-large me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/form.php" id="navNewCv">
                                <i class="fas fa-plus-circle me-1"></i> New CV
                            </a>
                        </li>
                        <?php if (is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php" id="navAdmin">
                                    <i class="fas fa-shield-alt me-1"></i> Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" id="navUserDropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= e($_SESSION['user_name'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/dashboard.php"><i class="fas fa-th-large me-2"></i>My CVs</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/login.php" id="navLogin">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2 nav-cta" href="<?= BASE_URL ?>/register.php" id="navRegister">
                                Get Started <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item ms-2">
                        <button class="btn btn-link nav-link theme-toggle" id="themeToggle" title="Toggle Dark Mode">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php
    // Flash messages
    $flash = get_flash();
    if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <main class="main-content">
