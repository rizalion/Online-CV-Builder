<?php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'cv_builder');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application
define('APP_NAME', 'CV Builder');
define('BASE_URL', 'http://localhost/cv-builder');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

define('SESSION_LIFETIME', 1800); // 30 minutes
define('ROOT_PATH', dirname(__DIR__) . '/');

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data:;");
