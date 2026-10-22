<?php
/**
 * ezyTailor - Mobile Bottom Navigation Bar
 */
$curPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
?>
<nav class="mobile-bottom-nav">
    <a href="index.php" class="bottom-nav-link <?= $curPage === 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-house-door-fill"></i>
        <span data-i18n="dashboard">Home</span>
    </a>
    <a href="customers.php" class="bottom-nav-link <?= in_array($curPage, ['customers.php', 'customer_view.php']) ? 'active' : '' ?>">
        <i class="bi bi-people-fill"></i>
        <span data-i18n="customers">Customers</span>
    </a>
    <a href="orders.php" class="bottom-nav-link <?= in_array($curPage, ['orders.php', 'order_create.php']) ? 'active' : '' ?>">
        <i class="bi bi-bag-check-fill"></i>
        <span data-i18n="orders">Orders</span>
    </a>
    <a href="ledger.php" class="bottom-nav-link <?= $curPage === 'ledger.php' ? 'active' : '' ?>">
        <i class="bi bi-journal-bookmark-fill"></i>
        <span data-i18n="ledger">Ledger</span>
    </a>
    <a href="settings.php" class="bottom-nav-link <?= $curPage === 'settings.php' ? 'active' : '' ?>">
        <i class="bi bi-gear-fill"></i>
        <span data-i18n="settings">Settings</span>
    </a>
</nav>
