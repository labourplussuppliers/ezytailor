<?php
/**
 * ezyTailor - Home Dashboard Screen
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();

// 1. Fetch monthly summary data (matching screenshot requirements)
$firstDayOfMonth = date('Y-m-01');

// Monthly orders & unpaid metrics
$stmtStats = $pdo->query("
    SELECT 
        COUNT(id) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_order_value,
        COALESCE(SUM(advance_paid), 0) as total_advance,
        COALESCE(SUM(balance_due), 0) as total_unpaid_amount,
        SUM(CASE WHEN balance_due > 0 THEN 1 ELSE 0 END) as unpaid_orders_count,
        SUM(CASE WHEN order_status = 'ready' THEN 1 ELSE 0 END) as ready_orders_count,
        SUM(CASE WHEN is_urgent = 1 AND order_status NOT IN ('delivered', 'cancelled') THEN 1 ELSE 0 END) as urgent_orders_count
    FROM orders
    WHERE booking_date >= '$firstDayOfMonth'
");
$stats = $stmtStats->fetch();

// Total customers count
$totalCustomers = $pdo->query("SELECT COUNT(*) as count FROM customers")->fetch()['count'] ?? 0;

// Ledger Net Balance
$incTotal = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM ledger WHERE type = 'income'")->fetch()['total'];
$expTotal = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM ledger WHERE type = 'expense'")->fetch()['total'];
$netCash = $incTotal - $expTotal;

// 2. Fetch Recent Orders
$recentOrdersStmt = $pdo->query("
    SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.customer_code
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    ORDER BY o.id DESC
    LIMIT 8
");
$recentOrders = $recentOrdersStmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Top Greeting Row -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><span data-i18n="dashboard">Dashboard</span> - <?= date('F Y') ?></h4>
            <p class="text-muted small mb-0">Overview of tailoring orders, measurements, and daily cash flow.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modal-customer-add">
                <i class="bi bi-person-plus-fill me-1"></i> <span data-i18n="new_customer">+ Customer</span>
            </button>
            <a href="order_create.php" class="btn btn-primary-custom btn-sm">
                <i class="bi bi-scissors me-1"></i> <span data-i18n="new_order">+ Order</span>
            </a>
        </div>
    </div>

    <!-- 1. Monthly Business Summary Cards (Matching Screenshot) -->
    <div class="metrics-grid">
        <!-- Unpaid Amount Card (e.g. 19,200 / 46,600) -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label" data-i18n="unpaid_amount">Unpaid Amount</span>
                <div class="metric-icon-wrap" style="background:#FEE2E2;color:#DC2626;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
            <div>
                <div class="metric-value-row">
                    <span class="metric-value text-danger">Rs <?= number_format($stats['total_unpaid_amount']) ?></span>
                    <span class="metric-subvalue text-muted">/ <?= number_format($stats['total_order_value']) ?></span>
                </div>
                <?php 
                $unpaidPercent = ($stats['total_order_value'] > 0) ? round(($stats['total_unpaid_amount'] / $stats['total_order_value']) * 100) : 0;
                ?>
                <div class="metric-progress">
                    <div class="metric-progress-bar bg-danger" style="width: <?= $unpaidPercent ?>%;"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted">
                    <span><?= $unpaidPercent ?>% pending recovery</span>
                    <span>Rs <?= number_format($stats['total_advance']) ?> paid</span>
                </div>
            </div>
        </div>

        <!-- Unpaid Orders Count Card -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label" data-i18n="unpaid_orders">Unpaid Orders</span>
                <div class="metric-icon-wrap" style="background:#FEF3C7;color:#D97706;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div>
                <div class="metric-value-row">
                    <span class="metric-value text-warning-emphasis"><?= (int)$stats['unpaid_orders_count'] ?></span>
                    <span class="metric-subvalue text-muted">/ <?= (int)$stats['total_orders'] ?> orders</span>
                </div>
                <?php 
                $unpaidOrdersPercent = ($stats['total_orders'] > 0) ? round(($stats['unpaid_orders_count'] / $stats['total_orders']) * 100) : 0;
                ?>
                <div class="metric-progress">
                    <div class="metric-progress-bar bg-warning" style="width: <?= $unpaidOrdersPercent ?>%;"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted">
                    <span>Pending balance collection</span>
                    <span><?= (int)$stats['total_orders'] - (int)$stats['unpaid_orders_count'] ?> fully cleared</span>
                </div>
            </div>
        </div>

        <!-- Ready for Delivery Orders -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label" data-i18n="ready_orders">Ready for Delivery</span>
                <div class="metric-icon-wrap" style="background:#D1FAE5;color:#059669;">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
            <div>
                <div class="metric-value-row">
                    <span class="metric-value text-success"><?= (int)$stats['ready_orders_count'] ?></span>
                    <span class="metric-subvalue text-muted">suits ready</span>
                </div>
                <div class="small text-muted mt-2">
                    <i class="bi bi-bell-fill text-primary"></i> Inform customers for pickup
                </div>
            </div>
        </div>

        <!-- Net Cash Balance in Ledger -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label" data-i18n="net_balance">Net Cash Balance</span>
                <div class="metric-icon-wrap" style="background:#E0E7FF;color:#4F46E5;">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div>
                <div class="metric-value-row">
                    <span class="metric-value text-primary">Rs <?= number_format($netCash) ?></span>
                </div>
                <div class="small text-muted mt-2 d-flex justify-content-between">
                    <span>In: Rs <?= number_format($incTotal) ?></span>
                    <span>Out: Rs <?= number_format($expTotal) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Quick Navigation Grid (from App UI) -->
    <div class="mb-4">
        <h6 class="fw-bold mb-3 text-secondary text-uppercase small" style="letter-spacing:0.05em;">Quick Navigation</h6>
        <div class="quick-nav-grid">
            <a href="customers.php" class="quick-nav-btn">
                <div class="quick-nav-icon"><i class="bi bi-people-fill"></i></div>
                <span class="quick-nav-title" data-i18n="customers">Customers</span>
            </a>
            <a href="orders.php" class="quick-nav-btn">
                <div class="quick-nav-icon"><i class="bi bi-bag-check-fill"></i></div>
                <span class="quick-nav-title" data-i18n="orders">Orders</span>
            </a>
            <a href="payments.php" class="quick-nav-btn">
                <div class="quick-nav-icon"><i class="bi bi-credit-card-2-front-fill"></i></div>
                <span class="quick-nav-title" data-i18n="payments">Payments</span>
            </a>
            <a href="ledger.php" class="quick-nav-btn">
                <div class="quick-nav-icon"><i class="bi bi-journal-text"></i></div>
                <span class="quick-nav-title" data-i18n="ledger">Ledger Book</span>
            </a>
            <a href="measurements.php" class="quick-nav-btn">
                <div class="quick-nav-icon"><i class="bi bi-rulers"></i></div>
                <span class="quick-nav-title" data-i18n="measurements">Measurements</span>
            </a>
            <a href="analytics.php" class="quick-nav-btn">
                <div class="quick-nav-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <span class="quick-nav-title" data-i18n="analytics">Analytics</span>
            </a>
        </div>
    </div>

    <!-- 3. Recent Orders Table -->
    <div class="app-card">
        <div class="app-card-header">
            <div class="app-card-title">
                <i class="bi bi-clock-history text-primary"></i>
                <span data-i18n="recent_orders">Recent Orders</span>
            </div>
            <div>
                <a href="orders.php" class="btn btn-sm btn-outline-secondary" data-i18n="view_all">View All Orders</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th data-i18n="order_no">Order #</th>
                        <th data-i18n="customer">Customer</th>
                        <th data-i18n="garment">Garment</th>
                        <th data-i18n="delivery_date">Delivery Date</th>
                        <th data-i18n="total">Total</th>
                        <th data-i18n="balance">Balance Due</th>
                        <th data-i18n="status">Status</th>
                        <th class="text-end" data-i18n="action">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No orders found. Click "+ Order" to create one.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold font-monospace"><?= e($ord['order_no']) ?></div>
                                    <?php if ($ord['is_urgent']): ?>
                                        <span class="badge badge-urgent"><i class="bi bi-lightning-fill"></i> Urgent</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($ord['customer_name']) ?></div>
                                    <small class="text-muted"><a href="tel:<?= e($ord['customer_phone']) ?>" class="text-decoration-none text-muted"><i class="bi bi-telephone"></i> <?= e($ord['customer_phone']) ?></a></small>
                                </td>
                                <td>
                                    <span class="text-capitalize fw-semibold"><?= ucwords(str_replace('_', ' ', $ord['garment_type'])) ?></span>
                                    <small class="text-muted d-block"><?= (int)$ord['quantity'] ?> Qty</small>
                                </td>
                                <td>
                                    <div class="fw-semibold <?= (strtotime($ord['delivery_date']) <= strtotime('+2 days') && $ord['order_status'] !== 'delivered') ? 'text-danger fw-bold' : '' ?>">
                                        <?= format_date($ord['delivery_date']) ?>
                                    </div>
                                    <small class="text-muted"><?= format_date($ord['booking_date'], 'd M') ?></small>
                                </td>
                                <td class="fw-bold">Rs <?= number_format($ord['total_amount']) ?></td>
                                <td>
                                    <?php if ($ord['balance_due'] > 0): ?>
                                        <span class="badge badge-unpaid">Rs <?= number_format($ord['balance_due']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-paid">Fully Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-<?= e($ord['order_status']) ?>">
                                        <?= ucfirst(e($ord['order_status'])) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Receipt Print Button -->
                                        <a href="receipt.php?id=<?= $ord['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="Print 58mm/80mm Thermal Receipt">
                                            <i class="bi bi-printer-fill text-dark"></i>
                                        </a>

                                        <!-- Collect Payment (if balance > 0) -->
                                        <?php if ($ord['balance_due'] > 0): ?>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="Payments.openCollectModal(<?= $ord['id'] ?>, '<?= e($ord['order_no']) ?>', '<?= addslashes(e($ord['customer_name'])) ?>', <?= (float)$ord['balance_due'] ?>)" 
                                                    title="Collect Balance Payment">
                                                <i class="bi bi-cash"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- WhatsApp Customer Update -->
                                        <button type="button" class="btn btn-outline-success btn-whatsapp" 
                                                data-phone="<?= e($ord['customer_phone']) ?>" 
                                                data-name="<?= e($ord['customer_name']) ?>" 
                                                data-balance="<?= $ord['balance_due'] ?>"
                                                title="Send WhatsApp Update">
                                            <i class="bi bi-whatsapp"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
