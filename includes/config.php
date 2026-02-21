<?php
// ============================================================
// ENVIRONMENT AUTO-DETECTION — zero configuration needed
// ============================================================

// Detect localhost vs production
$isLocalhost = (
    strpos($_SERVER['HTTP_HOST'] ?? 'localhost', 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false
);

// Base URL: handles /protfolio subdirectory on localhost, root on production
define('BASE_URL', $isLocalhost ? '/protfolio' : '');

// Site identity
define('SITE_NAME', 'Sakthi Portfolio');
define('SITE_URL', ($isLocalhost ? 'http://localhost' : 'https://sakthi.page.gd') . BASE_URL);

// ============================================================
// DATABASE CREDENTIALS
// NOTE: For maximum security, move these to a .env file above
// webroot. On InfinityFree, protect via .htaccess which blocks
// direct access to this file / the includes/ directory.
// ============================================================
if ($isLocalhost) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'portfolio_db');
} else {
    // InfinityFree production credentials
    define('DB_HOST', 'sql301.infinityfree.com');
    define('DB_USER', 'if0_41140025');
    define('DB_PASS', 'Sakthi10122004');
    define('DB_NAME', 'if0_41140025_sakthi_portfolio');
}

// Paths
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');

// Security
define('HASH_COST', 12);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('LOGIN_MAX_ATTEMPTS', 5); // max failed logins before lockout
define('LOGIN_LOCKOUT_MINUTES', 15); // lockout window in minutes

// Error reporting — verbose on localhost, silent in production
if ($isLocalhost) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Upload limits
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB