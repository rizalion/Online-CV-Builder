<?php
/**
 * Admin Index — redirect to dashboard
 */
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();
redirect(BASE_URL . '/admin/dashboard.php');
