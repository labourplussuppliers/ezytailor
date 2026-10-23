<?php
/**
 * ezyTailor Web Application - Configuration
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Karachi');

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'u949081598_ezytailor_root');
define('DB_PASS', 'Munazza@#123');
define('DB_NAME', 'u949081598_ezytailor');

// App Info
define('APP_NAME', 'ezyTailor');
define('APP_VERSION', '2.5.0');
define('APP_TAGLINE', 'Smart Tailor POS & Shop Management');

// Base Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');
define('UPLOAD_FABRIC_PATH', UPLOAD_PATH . '/fabrics');
define('UPLOAD_AUDIO_PATH', UPLOAD_PATH . '/audio');

// Ensure upload folders exist
if (!is_dir(UPLOAD_FABRIC_PATH)) {
    mkdir(UPLOAD_FABRIC_PATH, 0777, true);
}
if (!is_dir(UPLOAD_AUDIO_PATH)) {
    mkdir(UPLOAD_AUDIO_PATH, 0777, true);
}

// Dynamic Base URL detection
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$protocol = $is_https ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = dirname($script_name);
$dir = str_replace('\\', '/', $dir);

// Handle subdirectories correctly
$parts = explode('/', trim($dir, '/'));
$app_folder = !empty($parts[0]) ? '/' . $parts[0] : '';
if ($app_folder === '/ajax' || $app_folder === '/api') {
    $app_folder = '';
}
define('BASE_URL', $protocol . $host . $app_folder);
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', ASSETS_URL . '/uploads');

/**
 * Helper to escape HTML output
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function format_money($amount, $currency = 'Rs') {
    return $currency . ' ' . number_format((float)$amount, 0);
}

/**
 * Format date for UI
 */
function format_date($date_str, $format = 'd M Y') {
    if (empty($date_str)) return '-';
    $time = strtotime($date_str);
    return $time ? date($format, $time) : '-';
}

/**
 * Generate next Order Number
 */
function generate_order_no($pdo) {
    $year = date('Y');
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM orders");
    $row = $stmt->fetch();
    $next_id = ($row['max_id'] ?? 0) + 1;
    return 'ORD-' . $year . '-' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
}

/**
 * Generate next Customer Code
 */
function generate_customer_code($pdo) {
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM customers");
    $row = $stmt->fetch();
    $next_id = ($row['max_id'] ?? 0) + 101;
    return 'CUST-' . $next_id;
}
