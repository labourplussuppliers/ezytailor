<?php
/**
 * ezyTailor - Payments Management & Order Cash History
 */

$pageTitle = 'Payments';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();

$q = trim($_GET['q'] ?? '');
$sql = "
    SELECT p.*, 
           c.name as customer_name, c.phone as customer_phone, c.customer_code,
           o.order_no, o.total_amount as order_total, o.balance_due as current_balance,
           o.payment_status as order_payment_status
    FROM payments p
    JOIN customers c ON p.customer_id = c.id
    LEFT JOIN orders o ON p.order_id = o.id
    WHERE 1=1
";

$params = [];
if ($q !== '') {
    $sql .= " AND (c.name LIKE ? OR c.phone LIKE ? OR o.order_no LIKE ? OR p.payment_method LIKE ?) ";
    $params = ["%$q%", "%$q%", "%$q%", "%$q%"];
}

$sql .= " ORDER BY p.id DESC LIMIT 100 ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Total collections
$sumTotal = $pdo->query("SELECT COALESCE(SUM(amount), 0) as tot FROM payments")->fetch()['tot'];

// Cash vs Online
$sumCash = $pdo->query("SELECT COALESCE(SUM(amount), 0) as tot FROM payments WHERE payment_method = 'Cash'")->fetch()['tot'];
$sumOnline = $pdo->query("SELECT COALESCE(SUM(amount), 0) as tot FROM payments WHERE payment_method != 'Cash'")->fetch()['tot'];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><i class="bi bi-credit-card-2-front-fill text-primary me-2"></i><span data-i18n="payments">Order Payments History</span></h4>
            <p class="text-muted small mb-0">Track customer advances, cash on delivery, and outstanding order dues.</p>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Total Payments Received</span>
                <div class="metric-icon-wrap" style="background:#D1FAE5;color:#059669;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-success">Rs <?= number_format($sumTotal) ?></span>
            </div>
            <small class="text-muted">Lifetime collections</small>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Cash In Counter</span>
                <div class="metric-icon-wrap" style="background:#FEF3C7;color:#D97706;">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-dark">Rs <?= number_format($sumCash) ?></span>
            </div>
            <small class="text-muted">Physical store cash</small>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">JazzCash / EasyPaisa / Bank</span>
                <div class="metric-icon-wrap" style="background:#E0E7FF;color:#4F46E5;">
                    <i class="bi bi-phone-fill"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-primary">Rs <?= number_format($sumOnline) ?></span>
            </div>
            <small class="text-muted">Digital wallet & bank transfers</small>
        </div>
    </div>

    <!-- Payments Table Card -->
    <div class="app-card">
        <div class="app-card-header">
            <span class="app-card-title"><i class="bi bi-list-check text-primary"></i> Transactions Log</span>
            <div class="search-box-wrap" style="max-width:280px;">
                <form action="payments.php" method="GET">
                    <input type="text" name="q" class="search-box-input" placeholder="Search payments..." value="<?= e($q) ?>">
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Receipt ID</th>
                        <th>Payment Date</th>
                        <th data-i18n="order_no">Order #</th>
                        <th data-i18n="customer">Customer</th>
                        <th>Payment Method</th>
                        <th>Amount Paid</th>
                        <th>Current Order Balance</th>
                        <th>Remarks / Trx ID</th>
                        <th class="text-end">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted">No payment records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><span class="font-monospace text-muted">#PAY-<?= str_pad($p['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                                <td><?= format_date($p['payment_date']) ?></td>
                                <td>
                                    <?php if (!empty($p['order_no'])): ?>
                                        <a href="receipt.php?id=<?= $p['order_id'] ?>" target="_blank" class="fw-bold text-decoration-none font-monospace">
                                            <?= e($p['order_no']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><a href="customer_view.php?id=<?= $p['customer_id'] ?>" class="text-decoration-none text-dark"><?= e($p['customer_name']) ?></a></div>
                                    <small class="text-muted"><?= e($p['customer_phone']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($p['payment_method']) ?></span></td>
                                <td class="fw-bold text-success fs-6">Rs <?= number_format($p['amount']) ?></td>
                                <td>
                                    <?php if ($p['current_balance'] > 0): ?>
                                        <span class="badge badge-unpaid">Rs <?= number_format($p['current_balance']) ?> Due</span>
                                    <?php else: ?>
                                        <span class="badge badge-paid">Cleared</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= e($p['notes']) ?>
                                    <?php if (!empty($p['transaction_ref'])): ?>
                                        <span class="d-block text-primary">Ref: <?= e($p['transaction_ref']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (!empty($p['order_id'])): ?>
                                        <a href="receipt.php?id=<?= $p['order_id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Thermal POS Slip">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    <?php endif; ?>
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
