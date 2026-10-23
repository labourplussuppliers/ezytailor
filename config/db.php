<?php
/**
 * ezyTailor Web Application - Database Connection & Helpers
 */

require_once __DIR__ . '/config.php';

$pdo = null;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 1. Primary connection with configured credentials
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // 2. Fallback to root if local user not yet set up
    try {
        $fallback_dsn = "mysql:host=127.0.0.1;port=3306;charset=utf8mb4";
        $root_pdo = new PDO($fallback_dsn, 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Ensure database exists
        $root_pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        // Ensure user exists
        $root_pdo->exec("CREATE USER IF NOT EXISTS '" . DB_USER . "'@'localhost' IDENTIFIED BY '" . DB_PASS . "';");
        $root_pdo->exec("CREATE USER IF NOT EXISTS '" . DB_USER . "'@'127.0.0.1' IDENTIFIED BY '" . DB_PASS . "';");
        $root_pdo->exec("GRANT ALL PRIVILEGES ON `" . DB_NAME . "`.* TO '" . DB_USER . "'@'localhost';");
        $root_pdo->exec("GRANT ALL PRIVILEGES ON `" . DB_NAME . "`.* TO '" . DB_USER . "'@'127.0.0.1';");
        $root_pdo->exec("FLUSH PRIVILEGES;");
        $root_pdo = null;

        // Retry connection
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        // Auto-import schema if tables missing
        $check = $pdo->query("SHOW TABLES LIKE 'settings'");
        if ($check->rowCount() === 0) {
            $schema_file = ROOT_PATH . '/database/schema.sql';
            if (file_exists($schema_file)) {
                $sql = file_get_contents($schema_file);
                $pdo->exec($sql);
            }
        }
    } catch (PDOException $ex) {
        die("<div style='font-family:sans-serif;padding:30px;background:#fef2f2;border:1px solid #f87171;color:#991b1b;border-radius:8px;margin:30px auto;max-width:600px;'>
            <h3 style='margin-top:0;'>ezyTailor Database Connection Error</h3>
            <p>Could not connect to MySQL server.</p>
            <p><strong>Database:</strong> " . htmlspecialchars(DB_NAME) . "</p>
            <p><strong>User:</strong> " . htmlspecialchars(DB_USER) . "</p>
            <p><code>" . htmlspecialchars($ex->getMessage()) . "</code></p>
        </div>");
    }
}

/**
 * Return global PDO instance
 */
function getDB() {
    global $pdo;
    return $pdo;
}

/**
 * Get shop settings cached or from DB
 */
function getSettings() {
    global $pdo;
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    try {
        $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1 LIMIT 1");
        $settings = $stmt->fetch();
        if ($settings) {
            return $settings;
        }
    } catch (Exception $e) {
        // Fallback default
    }

    return [
        'id' => 1,
        'shop_name' => 'ezyTailor Boutique',
        'tagline' => 'Smart Tailor POS & Shop Management',
        'owner_name' => 'Master Tailor',
        'phone' => '+92 300 9876543',
        'whatsapp' => '+923009876543',
        'email' => 'contact@ezytailor.com',
        'city' => 'Lahore',
        'address' => 'Shop #14, Main Market, Lahore',
        'currency' => 'Rs',
        'theme' => 'olive',
        'language' => 'en',
        'receipt_header' => '*** ezyTailor Premium Tailoring Studio ***',
        'receipt_footer' => 'Thank you for choosing us!',
        'terms' => 'Please bring receipt for collection.',
        'receipt_paper_size' => '80mm',
        'show_barcode' => 1
    ];
}
