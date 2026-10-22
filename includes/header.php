<?php
/**
 * ezyTailor - Common Header Template
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$settings = getSettings();
$currentTheme = $_COOKIE['ezytailor_theme'] ?? ($settings['theme'] ?? 'olive');
$currentLang = $_COOKIE['ezytailor_lang'] ?? ($settings['language'] ?? 'en');
$isRtl = in_array($currentLang, ['ur', 'ar']);
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" data-theme="<?= e($currentTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= e($settings['shop_name']) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%235C6B50'><path d='M10 2a2 2 0 0 0-2 2v1H5a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h1v9a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-9h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3V4a2 2 0 0 0-2-2h-4zm0 2h4v1h-4V4zm-3 5h10v9H7V9z'/></svg>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons & FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- App Styles -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/themes.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/print.css?v=<?= APP_VERSION ?>">

    <!-- Global JS App Paths -->
    <script>
        window.APP_BASE_URL = '<?= BASE_URL ?>';
        window.APP_CURRENCY = '<?= e($settings['currency']) ?>';
    </script>
</head>
<body class="<?= $currentLang === 'ur' ? 'urdu-font' : '' ?>">
<div class="app-wrapper">
