<?php
/**
 * ezyTailor - Sidebar Navigation
 */
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$pdo = getDB();

// Quick counters for badges
$badgeOrders = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE order_status NOT IN ('delivered', 'cancelled')")->fetch()['cnt'] ?? 0;
$badgeCust = $pdo->query("SELECT COUNT(*) as cnt FROM customers")->fetch()['cnt'] ?? 0;
?>
<aside class="app-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo-icon">
            <i class="bi bi-scissors"></i>
        </div>
        <div>
            <div class="sidebar-brand-name"><?= e($settings['shop_name']) ?></div>
            <div class="sidebar-brand-sub"><?= e($settings['tagline']) ?></div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-label" data-i18n="dashboard">Main</li>
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span data-i18n="dashboard">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="customers.php" class="nav-link <?= in_array($currentPage, ['customers.php', 'customer_view.php']) ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i>
                <span data-i18n="customers">Customers</span>
                <span class="badge bg-secondary rounded-pill"><?= $badgeCust ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a href="orders.php" class="nav-link <?= in_array($currentPage, ['orders.php', 'order_create.php']) ? 'active' : '' ?>">
                <i class="bi bi-bag-check-fill"></i>
                <span data-i18n="orders">Orders</span>
                <?php if ($badgeOrders > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?= $badgeOrders ?></span>
                <?php endif; ?>
            </a>
        </li>

        <li class="nav-label mt-2">Tailoring</li>
        <li class="nav-item">
            <a href="measurements.php" class="nav-link <?= $currentPage === 'measurements.php' ? 'active' : '' ?>">
                <i class="bi bi-rulers"></i>
                <span data-i18n="measurements">Measurements</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="order_create.php" class="nav-link <?= $currentPage === 'order_create.php' ? 'active' : '' ?>">
                <i class="bi bi-plus-circle-fill text-success"></i>
                <span>Book New Order</span>
            </a>
        </li>

        <li class="nav-label mt-2">Accounts & POS</li>
        <li class="nav-item">
            <a href="payments.php" class="nav-link <?= $currentPage === 'payments.php' ? 'active' : '' ?>">
                <i class="bi bi-cash-stack"></i>
                <span data-i18n="payments">Payments</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="ledger.php" class="nav-link <?= $currentPage === 'ledger.php' ? 'active' : '' ?>">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span data-i18n="ledger">Ledger Book</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="analytics.php" class="nav-link <?= $currentPage === 'analytics.php' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span data-i18n="analytics">Analytics</span>
            </a>
        </li>

        <li class="nav-label mt-2">Configuration</li>
        <li class="nav-item">
            <a href="settings.php" class="nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                <i class="bi bi-gear-fill"></i>
                <span data-i18n="settings">Settings</span>
            </a>
        </li>
    </ul>

    <div class="p-3 border-top bg-light small text-center text-muted">
        <div><strong><?= APP_NAME ?></strong> v<?= APP_VERSION ?></div>
        <div style="font-size:0.75rem;">Tailoring Studio POS</div>
    </div>
</aside>
