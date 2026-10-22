<?php
/**
 * ezyTailor - Settings & Profile API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDB();
$action = $_GET['action'] ?? 'get';

try {
    if ($action === 'get') {
        $settings = getSettings();
        echo json_encode(['success' => true, 'data' => $settings]);
        exit;

    } elseif ($action === 'save') {
        $shopName = trim($_POST['shop_name'] ?? 'ezyTailor Boutique');
        $tagline = trim($_POST['tagline'] ?? '');
        $ownerName = trim($_POST['owner_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $currency = trim($_POST['currency'] ?? 'Rs');
        $theme = trim($_POST['theme'] ?? 'olive');
        $language = trim($_POST['language'] ?? 'en');
        $receiptHeader = trim($_POST['receipt_header'] ?? '');
        $receiptFooter = trim($_POST['receipt_footer'] ?? '');
        $terms = trim($_POST['terms'] ?? '');
        $receiptPaperSize = trim($_POST['receipt_paper_size'] ?? '80mm');
        $showBarcode = !empty($_POST['show_barcode']) ? 1 : 0;

        $stmt = $pdo->prepare("
            UPDATE settings SET
                shop_name = ?, tagline = ?, owner_name = ?, phone = ?, whatsapp = ?,
                email = ?, city = ?, address = ?, currency = ?, theme = ?, language = ?,
                receipt_header = ?, receipt_footer = ?, terms = ?, receipt_paper_size = ?,
                show_barcode = ?
            WHERE id = 1
        ");
        $stmt->execute([
            $shopName, $tagline, $ownerName, $phone, $whatsapp,
            $email, $city, $address, $currency, $theme, $language,
            $receiptHeader, $receiptFooter, $terms, $receiptPaperSize,
            $showBarcode
        ]);

        echo json_encode(['success' => true, 'message' => 'Settings updated successfully!']);
        exit;

    } elseif ($action === 'set_theme') {
        $theme = trim($_GET['theme'] ?? 'olive');
        if (in_array($theme, ['olive', 'navy', 'dark', 'maroon', 'teal'])) {
            $stmt = $pdo->prepare("UPDATE settings SET theme = ? WHERE id = 1");
            $stmt->execute([$theme]);
        }
        echo json_encode(['success' => true]);
        exit;

    } elseif ($action === 'set_lang') {
        $lang = trim($_GET['lang'] ?? 'en');
        if (in_array($lang, ['en', 'ur', 'hi', 'ar'])) {
            $stmt = $pdo->prepare("UPDATE settings SET language = ? WHERE id = 1");
            $stmt->execute([$lang]);
        }
        echo json_encode(['success' => true]);
        exit;

    } elseif ($action === 'reset_demo') {
        // Re-run schema to reset demo data
        $schemaFile = ROOT_PATH . '/database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec("DROP DATABASE IF EXISTS ezytailor_db; CREATE DATABASE ezytailor_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE ezytailor_db;");
            $pdo->exec($sql);
            echo json_encode(['success' => true, 'message' => 'Sample database reloaded successfully!']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Schema file not found.']);
            exit;
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
