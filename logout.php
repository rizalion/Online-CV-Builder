<?php
require_once __DIR__ . '/includes/db.php';

session_unset();
session_destroy();
if (isset($_COOKIE['remember_cv'])) {
    setcookie('remember_cv', '', time() - 3600, '/');
}

header('Location: ' . BASE_URL . '/login.php');
exit;
