<?php
/**
 * ezyTailor - Orders List & Management
 */

$pageTitle = 'Orders';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();

$filterStatus = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

$sql = "
    SELECT o.*, 
           c.name as customer_name, c.phone as customer_phone, c.customer_code, c.city as customer_city,
           w.name as worker_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    LEFT JOIN workers w ON o.assigned_worker_id = w.id
    WHERE 1=1
";

$params = [];

if ($filterStatus !== 'all') {
    if ($filterStatus === 'unpaid') {
        $sql .= " AND o.balance_due > 0 ";
    } elseif ($filterStatus === 'urgent') {
        $sql .= " AND o.is_urgent = 1 ";
    } else {
        $sql .= " AND o.order_status = ? ";
        $params[] = $filterStatus;
    }
}

if ($searchQuery !== '') {
    $sql .= " AND (o.order_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR o.garment_type LIKE ?) ";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sql .= " ORDER BY o.id DESC LIMIT 100 ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Counts for filter pills
$countAll = $pdo->query("SELECT COUNT(*) as cnt FROM orders")->fetch()['cnt'];
$countPending = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'pending'")->fetch()['cnt'];
$countCutting = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'cutting'")->fetch()['cnt'];
$countStitching = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'stitching'")->fetch()['cnt'];
$countReady = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'ready'")->fetch()['cnt'];
$countDelivered = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'delivered'")->fetch()['cnt'];
$countUnpaid = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE balance_due > 0")->fetch()['cnt'];
$countUrgent = $pdo->query("SELECT COUNT(*) as cnt FROM orders WHERE is_urgent = 1 AND order_status != 'delivered'")->fetch()['cnt'];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><i class="bi bi-bag-check-fill text-primary me-2"></i><span data-i18n="orders">Orders Management</span></h4>
            <p class="text-muted small mb-0">Track cutting, stitching, deliveries, advance payments, and thermal receipts.</p>
        </div>
        <a href="order_create.php" class="btn btn-primary-custom">
            <i class="bi bi-plus-circle-fill me-1"></i> <span data-i18n="new_order">+ Book New Order</span>
        </a>
    </div>

    <!-- Filter Nav Pills -->
    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="orders.php?status=all" class="btn btn-sm <?= $filterStatus === 'all' ? 'btn-dark' : 'btn-outline-secondary' ?>">
            All (<?= $countAll ?>)
        </a>
        <a href="orders.php?status=urgent" class="btn btn-sm <?= $filterStatus === 'urgent' ? 'btn-danger' : 'btn-outline-danger' ?>">
            <i class="bi bi-lightning-fill"></i> Urgent (<?= $countUrgent ?>)
        </a>
        <a href="orders.php?status=pending" class="btn btn-sm <?= $filterStatus === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>">
            Pending (<?= $countPending ?>)
        </a>
        <a href="orders.php?status=cutting" class="btn btn-sm <?= $filterStatus === 'cutting' ? 'btn-primary' : 'btn-outline-primary' ?>">
            Cutting (<?= $countCutting ?>)
        </a>
        <a href="orders.php?status=stitching" class="btn btn-sm <?= $filterStatus === 'stitching' ? 'btn-info text-white' : 'btn-outline-info' ?>">
            Stitching (<?= $countStitching ?>)
        </a>
        <a href="orders.php?status=ready" class="btn btn-sm <?= $filterStatus === 'ready' ? 'btn-success' : 'btn-outline-success' ?>">
            Ready for Delivery (<?= $countReady ?>)
        </a>
        <a href="orders.php?status=delivered" class="btn btn-sm <?= $filterStatus === 'delivered' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
            Delivered (<?= $countDelivered ?>)
        </a>
        <a href="orders.php?status=unpaid" class="btn btn-sm <?= $filterStatus === 'unpaid' ? 'btn-danger' : 'btn-outline-danger' ?>">
            Unpaid Dues (<?= $countUnpaid ?>)
        </a>
    </div>

    <!-- Search Form -->
    <div class="app-card mb-4">
        <div class="app-card-body p-3">
            <form action="orders.php" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" 
                               placeholder="Search by order #, customer name, phone or suit type..." 
                               value="<?= e($searchQuery) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter Results</button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="orders.php?status=<?= e($filterStatus) ?>" class="btn btn-link btn-sm text-danger text-decoration-none">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="app-card">
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th data-i18n="order_no">Order #</th>
                        <th data-i18n="customer">Customer</th>
                        <th data-i18n="garment">Garment Details</th>
                        <th data-i18n="delivery_date">Delivery Date</th>
                        <th data-i18n="total">Total</th>
                        <th data-i18n="advance">Advance</th>
                        <th data-i18n="balance">Balance Due</th>
                        <th data-i18n="status">Production Status</th>
                        <th class="text-end" data-i18n="action">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted">No orders matching the criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold font-monospace"><?= e($o['order_no']) ?></div>
                                    <?php if ($o['is_urgent']): ?>
                                        <span class="badge badge-urgent"><i class="bi bi-lightning-fill"></i> Urgent</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><a href="customer_view.php?id=<?= $o['customer_id'] ?>" class="text-decoration-none text-dark"><?= e($o['customer_name']) ?></a></div>
                                    <small class="text-muted"><a href="tel:<?= e($o['customer_phone']) ?>" class="text-decoration-none text-muted"><i class="bi bi-telephone"></i> <?= e($o['customer_phone']) ?></a></small>
                                </td>
                                <td>
                                    <span class="text-capitalize fw-semibold"><?= ucwords(str_replace('_', ' ', $o['garment_type'])) ?></span>
                                    <small class="text-muted d-block">Qty: <?= (int)$o['quantity'] ?></small>
                                    <?php if (!empty($o['fabric_details'])): ?>
                                        <small class="text-muted d-block text-truncate" style="max-width:180px;"><i class="bi bi-tag"></i> <?= e($o['fabric_details']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold <?= (strtotime($o['delivery_date']) <= strtotime('+2 days') && $o['order_status'] !== 'delivered') ? 'text-danger fw-bold' : '' ?>">
                                        <?= format_date($o['delivery_date']) ?>
                                    </div>
                                    <small class="text-muted">Booked: <?= format_date($o['booking_date'], 'd M') ?></small>
                                </td>
                                <td class="fw-bold">Rs <?= number_format($o['total_amount']) ?></td>
                                <td class="text-success">Rs <?= number_format($o['advance_paid']) ?></td>
                                <td>
                                    <?php if ($o['balance_due'] > 0): ?>
                                        <span class="badge badge-unpaid">Rs <?= number_format($o['balance_due']) ?> Due</span>
                                    <?php else: ?>
                                        <span class="badge badge-paid">Fully Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Production Status Dropdown -->
                                    <select class="form-select form-select-sm order-status-select" data-order-id="<?= $o['id'] ?>" style="min-width:115px;font-size:0.8rem;font-weight:600;">
                                        <option value="pending" <?= $o['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="cutting" <?= $o['order_status'] === 'cutting' ? 'selected' : '' ?>>Cutting</option>
                                        <option value="stitching" <?= $o['order_status'] === 'stitching' ? 'selected' : '' ?>>Stitching</option>
                                        <option value="ready" <?= $o['order_status'] === 'ready' ? 'selected' : '' ?>>Ready</option>
                                        <option value="delivered" <?= $o['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $o['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Instant POS Thermal Receipt -->
                                        <a href="receipt.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="Thermal POS Receipt (58mm/80mm)">
                                            <i class="bi bi-printer-fill text-dark"></i>
                                        </a>

                                        <!-- Collect Payment Modal Trigger -->
                                        <?php if ($o['balance_due'] > 0): ?>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="Payments.openCollectModal(<?= $o['id'] ?>, '<?= e($o['order_no']) ?>', '<?= addslashes(e($o['customer_name'])) ?>', <?= (float)$o['balance_due'] ?>)" 
                                                    title="Collect Payment">
                                                <i class="bi bi-cash-stack"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- WhatsApp Notification -->
                                        <button type="button" class="btn btn-outline-success btn-whatsapp" 
                                                data-phone="<?= e($o['customer_phone']) ?>" 
                                                data-name="<?= e($o['customer_name']) ?>" 
                                                data-balance="<?= $o['balance_due'] ?>"
                                                title="Send WhatsApp Message">
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
