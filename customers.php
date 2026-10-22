<?php
/**
 * ezyTailor - Customers Management Directory
 */

$pageTitle = 'Customers';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();

$q = trim($_GET['q'] ?? '');
$sql = "
    SELECT c.*,
           COUNT(o.id) as total_orders,
           COALESCE(SUM(o.balance_due), 0) as total_balance_due,
           COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM customers c
    LEFT JOIN orders o ON c.id = o.customer_id
";

$params = [];
if ($q !== '') {
    $sql .= " WHERE c.name LIKE ? OR c.phone LIKE ? OR c.city LIKE ? OR c.customer_code LIKE ? ";
    $params = ["%$q%", "%$q%", "%$q%", "%$q%"];
}

$sql .= " GROUP BY c.id ORDER BY c.id DESC ";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Header Row -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><i class="bi bi-people-fill text-primary me-2"></i><span data-i18n="customers">Customers Directory</span></h4>
            <p class="text-muted small mb-0">View customer profiles, contact info, saved measurements, and pending dues.</p>
        </div>
        <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modal-customer-add">
            <i class="bi bi-person-plus-fill me-1"></i> <span data-i18n="new_customer">+ Add Customer</span>
        </button>
    </div>

    <!-- Search & Filter Card -->
    <div class="app-card mb-4">
        <div class="app-card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="customer-search-input" class="form-control border-start-0" 
                               placeholder="Search customer by name, phone, city or code..." 
                               value="<?= e($q) ?>">
                    </div>
                </div>
                <div class="col-md-6 col-lg-7 text-md-end text-muted small">
                    Total Registered Customers: <strong class="text-dark"><?= count($customers) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table Card -->
    <div class="app-card">
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th data-i18n="customer">Customer Name</th>
                        <th data-i18n="phone">Phone Number</th>
                        <th>Orders Count</th>
                        <th data-i18n="balance">Balance Due</th>
                        <th>Registration Date</th>
                        <th class="text-end" data-i18n="action">Actions</th>
                    </tr>
                </thead>
                <tbody id="customers-table-body">
                    <?php if (empty($customers)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No customers found. Click "+ Add Customer" to register one.</td></tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace"><?= e($c['customer_code']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold"><a href="customer_view.php?id=<?= $c['id'] ?>" class="text-decoration-none text-dark"><?= e($c['name']) ?></a></div>
                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($c['city'] ?: 'Lahore') ?></small>
                                </td>
                                <td>
                                    <div>
                                        <a href="tel:<?= e($c['phone']) ?>" class="text-decoration-none text-dark fw-semibold">
                                            <i class="bi bi-telephone-fill text-success me-1"></i> <?= e($c['phone']) ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($c['alt_phone'])): ?>
                                        <small class="text-muted"><i class="bi bi-phone"></i> <?= e($c['alt_phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= (int)$c['total_orders'] ?> orders</span>
                                </td>
                                <td>
                                    <?php if ($c['total_balance_due'] > 0): ?>
                                        <span class="badge badge-unpaid">Rs <?= number_format($c['total_balance_due']) ?> Due</span>
                                    <?php else: ?>
                                        <span class="badge badge-paid">Fully Clear</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= format_date($c['created_at']) ?></small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Profile -->
                                        <a href="customer_view.php?id=<?= $c['id'] ?>" class="btn btn-outline-secondary" title="View Profile, Measurements & History">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <!-- Call direct -->
                                        <a href="tel:<?= e($c['phone']) ?>" class="btn btn-outline-primary" title="Call">
                                            <i class="bi bi-telephone-outbound"></i>
                                        </a>
                                        <!-- Direct WhatsApp -->
                                        <button type="button" class="btn btn-outline-success btn-whatsapp" 
                                                data-phone="<?= e($c['phone']) ?>" 
                                                data-name="<?= e($c['name']) ?>" 
                                                data-balance="<?= $c['total_balance_due'] ?>"
                                                title="WhatsApp Direct Chat">
                                            <i class="bi bi-whatsapp"></i>
                                        </button>
                                        <!-- Edit -->
                                        <button type="button" class="btn btn-outline-secondary" onclick="Customers.edit(<?= $c['id'] ?>)" title="Edit Customer Details">
                                            <i class="bi bi-pencil-square"></i>
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
