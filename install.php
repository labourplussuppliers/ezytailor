<?php
/**
 * ezyTailor - 1-Click Web Database Installer & Diagnostics
 */

require_once __DIR__ . '/config/config.php';

$dbConnected = false;
$dbError = null;
$tablesFound = [];
$installed = false;

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $dbConnected = true;

    // Check existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $tablesFound = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    $dbError = $e->getMessage();
}

// Handle Auto-Install Action
if (isset($_POST['install']) && $dbConnected) {
    try {
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
            $installed = true;

            // Re-fetch tables
            $stmt = $pdo->query("SHOW TABLES");
            $tablesFound = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $dbError = "schema.sql file not found in /database folder.";
        }
    } catch (Exception $e) {
        $dbError = "Installation error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ezyTailor - Database Auto-Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #F7F6F2; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .install-box { max-width: 600px; margin: 50px auto; background: #fff; border-radius: 14px; border: 1px solid #E5E7EB; box-shadow: 0 10px 25px rgba(0,0,0,0.06); overflow: hidden; }
        .install-header { background: #5C6B50; color: #fff; padding: 25px; text-align: center; }
        .install-body { padding: 30px; }
    </style>
</head>
<body>

<div class="install-box">
    <div class="install-header">
        <i class="bi bi-scissors" style="font-size: 2.5rem;"></i>
        <h3 class="fw-bold mt-2 mb-0">ezyTailor System Setup</h3>
        <p class="mb-0 opacity-75 small">Live Server Database Auto-Installer</p>
    </div>

    <div class="install-body">
        <?php if ($installed): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill fs-4"></i>
                <div>
                    <strong>Setup Completed Successfully!</strong><br>
                    All database tables, measurement templates, and sample data have been installed.
                </div>
            </div>
            <div class="text-center">
                <a href="index.php" class="btn btn-success btn-lg px-5 fw-bold">
                    <i class="bi bi-house-door-fill me-1"></i> Open Dashboard Now
                </a>
            </div>
        <?php else: ?>
            <!-- Diagnostics -->
            <div class="mb-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Server & Database Diagnostics:</h6>
                
                <div class="d-flex justify-content-between p-2 border-bottom">
                    <span>PHP Version:</span>
                    <strong><?= PHP_VERSION ?> (OK)</strong>
                </div>
                <div class="d-flex justify-content-between p-2 border-bottom">
                    <span>Database Host:</span>
                    <code><?= e(DB_HOST) ?></code>
                </div>
                <div class="d-flex justify-content-between p-2 border-bottom">
                    <span>Database Name:</span>
                    <code><?= e(DB_NAME) ?></code>
                </div>
                <div class="d-flex justify-content-between p-2 border-bottom">
                    <span>Database User:</span>
                    <code><?= e(DB_USER) ?></code>
                </div>
                <div class="d-flex justify-content-between p-2 border-bottom">
                    <span>MySQL Connection:</span>
                    <?php if ($dbConnected): ?>
                        <span class="badge bg-success">Connected Successfully</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Connection Failed</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-between p-2 border-bottom">
                    <span>Existing Tables Found:</span>
                    <strong class="<?= count($tablesFound) > 0 ? 'text-success' : 'text-danger' ?>">
                        <?= count($tablesFound) ?> tables
                    </strong>
                </div>
            </div>

            <?php if ($dbError): ?>
                <div class="alert alert-danger small mb-4">
                    <strong>Error:</strong> <?= htmlspecialchars($dbError) ?>
                </div>
            <?php endif; ?>

            <?php if (count($tablesFound) >= 8): ?>
                <div class="alert alert-info small mb-3">
                    <i class="bi bi-info-circle me-1"></i> Database tables are already installed and ready to go!
                </div>
                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-success btn-lg fw-bold">
                        <i class="bi bi-arrow-right-circle me-1"></i> Go to Dashboard
                    </a>
                </div>
            <?php else: ?>
                <p class="text-muted small">
                    Click the button below to automatically create all required tables (customers, orders, measurements, ledger, settings) and insert demonstration data.
                </p>
                <form method="POST">
                    <button type="submit" name="install" class="btn btn-primary w-100 py-3 fw-bold" style="background:#5C6B50;border-color:#5C6B50;">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Install Database Tables (1-Click)
                    </button>
                </form>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

</body>
</html>
