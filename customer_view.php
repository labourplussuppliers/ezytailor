<?php
/**
 * ezyTailor - Detailed Customer Profile View (Tabs: Measurements, Orders, Payments)
 */

$pageTitle = 'Customer Profile';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();
$customerId = (int)($_GET['id'] ?? 0);
$activeTab = $_GET['tab'] ?? 'measurements';

if (!$customerId) {
    header('Location: customers.php');
    exit;
}

// Fetch Customer
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    die("Customer not found.");
}

// Customer Summary Metrics
$sumStmt = $pdo->prepare("
    SELECT 
        COUNT(id) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_spent,
        COALESCE(SUM(advance_paid), 0) as total_paid,
        COALESCE(SUM(balance_due), 0) as total_balance
    FROM orders 
    WHERE customer_id = ?
");
$sumStmt->execute([$customerId]);
$summary = $sumStmt->fetch();

// Saved Measurements
$mStmt = $pdo->prepare("
    SELECT cm.*, mt.title as template_title, mt.icon as template_icon
    FROM customer_measurements cm
    LEFT JOIN measurement_templates mt ON cm.template_id = mt.id
    WHERE cm.customer_id = ?
    ORDER BY cm.id DESC
");
$mStmt->execute([$customerId]);
$measurements = $mStmt->fetchAll();

// Orders
$oStmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
$oStmt->execute([$customerId]);
$orders = $oStmt->fetchAll();

// Payments
$pStmt = $pdo->prepare("
    SELECT p.*, o.order_no 
    FROM payments p 
    LEFT JOIN orders o ON p.order_id = o.id 
    WHERE p.customer_id = ? 
    ORDER BY p.id DESC
");
$pStmt->execute([$customerId]);
$payments = $pStmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Customer Header Profile Card -->
    <div class="app-card mb-4">
        <div class="app-card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:68px;height:68px;border-radius:18px;background:var(--primary-subtle);color:var(--primary-color);display:flex;align-items:center;justify-content:center;font-size:2rem;box-shadow:var(--shadow-sm);">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold mb-0"><?= e($customer['name']) ?></h4>
                            <span class="badge bg-light text-dark border font-monospace"><?= e($customer['customer_code']) ?></span>
                        </div>
                        <div class="text-muted small mt-1">
                            <span class="me-3"><i class="bi bi-geo-alt"></i> <?= e($customer['city'] ?: 'Lahore') ?></span>
                            <span><i class="bi bi-calendar3"></i> Customer since <?= format_date($customer['created_at']) ?></span>
                        </div>
                        <?php if (!empty($customer['address'])): ?>
                            <div class="text-muted small mt-1"><i class="bi bi-house"></i> <?= e($customer['address']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Call and WhatsApp Action Buttons (Requirement 3) -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Direct Call Button -->
                    <a href="tel:<?= e($customer['phone']) ?>" class="btn btn-outline-primary btn-sm px-3 fw-bold">
                        <i class="bi bi-telephone-outbound-fill me-1"></i> Call <?= e($customer['phone']) ?>
                    </a>
                    
                    <!-- Direct WhatsApp Button -->
                    <button type="button" class="btn btn-success btn-sm px-3 fw-bold btn-whatsapp" 
                            data-phone="<?= e($customer['phone']) ?>" 
                            data-name="<?= e($customer['name']) ?>" 
                            data-balance="<?= $summary['total_balance'] ?>">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                    </button>

                    <!-- Book Order for this customer -->
                    <a href="order_create.php?customer_id=<?= $customer['id'] ?>" class="btn btn-primary-custom btn-sm px-3 fw-bold">
                        <i class="bi bi-scissors me-1"></i> + Book Order
                    </a>
                </div>
            </div>

            <!-- Financial Summary Bar -->
            <div class="row g-3 mt-3 pt-3 border-top">
                <div class="col-sm-3 col-6">
                    <span class="text-muted small">Total Orders</span>
                    <div class="h5 fw-bold mb-0 text-dark"><?= (int)$summary['total_orders'] ?></div>
                </div>
                <div class="col-sm-3 col-6">
                    <span class="text-muted small">Total Spent</span>
                    <div class="h5 fw-bold mb-0 text-dark">Rs <?= number_format($summary['total_spent']) ?></div>
                </div>
                <div class="col-sm-3 col-6">
                    <span class="text-muted small">Total Paid</span>
                    <div class="h5 fw-bold mb-0 text-success">Rs <?= number_format($summary['total_paid']) ?></div>
                </div>
                <div class="col-sm-3 col-6">
                    <span class="text-muted small">Balance Due</span>
                    <div class="h5 fw-bold mb-0 <?= $summary['total_balance'] > 0 ? 'text-danger' : 'text-muted' ?>">
                        Rs <?= number_format($summary['total_balance']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 border" id="customerTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'measurements' ? 'active' : '' ?>" id="tab-measurements-btn" data-bs-toggle="pill" data-bs-target="#tab-measurements" type="button" role="tab">
                <i class="bi bi-rulers me-1"></i> Measurements (<?= count($measurements) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'orders' ? 'active' : '' ?>" id="tab-orders-btn" data-bs-toggle="pill" data-bs-target="#tab-orders" type="button" role="tab">
                <i class="bi bi-bag-check me-1"></i> Orders History (<?= count($orders) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'payments' ? 'active' : '' ?>" id="tab-payments-btn" data-bs-toggle="pill" data-bs-target="#tab-payments" type="button" role="tab">
                <i class="bi bi-cash-stack me-1"></i> Payments Log (<?= count($payments) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-info-btn" data-bs-toggle="pill" data-bs-target="#tab-info" type="button" role="tab">
                <i class="bi bi-person-lines-fill me-1"></i> Profile & Notes
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="customerTabContent">

        <!-- TAB 1: MEASUREMENTS -->
        <div class="tab-pane fade <?= $activeTab === 'measurements' ? 'show active' : '' ?>" id="tab-measurements" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-rulers text-primary me-2"></i>Saved Measurements</h5>
                <a href="measurements.php?customer_id=<?= $customer['id'] ?>" class="btn btn-primary-custom btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add New Fit / Measurement
                </a>
            </div>

            <?php if (empty($measurements)): ?>
                <div class="app-card text-center py-5 text-muted">
                    <i class="bi bi-rulers display-4 text-muted mb-3 d-block"></i>
                    <h6>No measurements saved for this customer yet.</h6>
                    <p class="small">Record precise body dimensions or choose from built-in templates.</p>
                    <a href="measurements.php?customer_id=<?= $customer['id'] ?>" class="btn btn-outline-custom btn-sm">Create First Measurement</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($measurements as $m): 
                        $fields = json_decode($m['measurements_json'], true) ?: [];
                        $styles = json_decode($m['styles_json'], true) ?: [];
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="app-card h-100">
                                <div class="app-card-header">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="metric-icon-wrap" style="width:34px;height:34px;font-size:1.1rem;">
                                            <i class="bi <?= e($m['template_icon'] ?: 'bi-scissors') ?>"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0"><?= e($m['title']) ?></h6>
                                            <small class="text-muted text-capitalize"><?= ucwords(str_replace('_', ' ', $m['garment_type'])) ?></small>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= format_date($m['updated_at'], 'd M Y') ?></small>
                                </div>
                                <div class="app-card-body">
                                    <!-- Measurements Grid -->
                                    <div class="row g-2 mb-3">
                                        <?php foreach ($fields as $k => $v): ?>
                                            <div class="col-4">
                                                <div class="p-2 border rounded text-center bg-light">
                                                    <span class="d-block text-muted text-uppercase" style="font-size:0.68rem;"><?= str_replace('_', ' ', $k) ?></span>
                                                    <span class="fw-bold text-dark fs-6"><?= e($v) ?> <small class="text-muted" style="font-size:0.65rem;">in</small></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Styling Notes -->
                                    <?php if (!empty($styles)): ?>
                                        <div class="border-top pt-2 mb-2">
                                            <span class="fw-semibold small d-block mb-1 text-secondary">Style Specs:</span>
                                            <ul class="list-unstyled small mb-0">
                                                <?php foreach ($styles as $sk => $sv): ?>
                                                    <li><i class="bi bi-check2 text-success me-1"></i><strong><?= ucwords(str_replace('_', ' ', $sk)) ?>:</strong> <?= e($sv) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Voice Note Player (if recorded) -->
                                    <?php if (!empty($m['audio_note'])): ?>
                                        <div class="border-top pt-2 mb-2">
                                            <span class="small fw-bold text-danger d-flex align-items-center gap-1 mb-1">
                                                <i class="bi bi-mic-fill"></i> Voice Instruction Note:
                                            </span>
                                            <audio controls class="w-100" style="height:36px;">
                                                <source src="<?= UPLOAD_URL ?>/audio/<?= e($m['audio_note']) ?>">
                                                Your browser does not support audio playback.
                                            </audio>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Fabric Photo (if attached) -->
                                    <?php if (!empty($m['fabric_photo'])): ?>
                                        <div class="border-top pt-2 mb-2">
                                            <span class="small fw-bold text-muted d-block mb-1">Fabric Sample:</span>
                                            <img src="<?= UPLOAD_URL ?>/fabrics/<?= e($m['fabric_photo']) ?>" class="img-thumbnail rounded" style="max-height:80px;" alt="Fabric Swatch">
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($m['notes'])): ?>
                                        <div class="small text-muted bg-light p-2 rounded border mt-2">
                                            <i class="bi bi-info-circle me-1"></i><?= e($m['notes']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Actions -->
                                    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                        <a href="measurements.php?customer_id=<?= $customer['id'] ?>&edit_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <a href="order_create.php?customer_id=<?= $customer['id'] ?>&measurement_id=<?= $m['id'] ?>" class="btn btn-sm btn-primary-custom">
                                            <i class="bi bi-bag-plus me-1"></i> Book Order with this Fit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB 2: ORDERS -->
        <div class="tab-pane fade <?= $activeTab === 'orders' ? 'show active' : '' ?>" id="tab-orders" role="tabpanel">
            <div class="app-card">
                <div class="app-card-header">
                    <h6 class="app-card-title"><i class="bi bi-bag-check text-primary me-2"></i>Orders Placed by <?= e($customer['name']) ?></h6>
                    <a href="order_create.php?customer_id=<?= $customer['id'] ?>" class="btn btn-primary-custom btn-sm">+ Book New Order</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Garment</th>
                                <th>Booking Date</th>
                                <th>Delivery Date</th>
                                <th>Total</th>
                                <th>Advance</th>
                                <th>Balance Due</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">No orders placed yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold font-monospace"><?= e($o['order_no']) ?></span>
                                            <?php if ($o['is_urgent']): ?>
                                                <span class="badge badge-urgent ms-1">Urgent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-capitalize"><?= ucwords(str_replace('_', ' ', $o['garment_type'])) ?> (<?= (int)$o['quantity'] ?>)</td>
                                        <td><?= format_date($o['booking_date']) ?></td>
                                        <td class="fw-semibold <?= strtotime($o['delivery_date']) <= strtotime('+2 days') ? 'text-danger' : '' ?>">
                                            <?= format_date($o['delivery_date']) ?>
                                        </td>
                                        <td class="fw-bold">Rs <?= number_format($o['total_amount']) ?></td>
                                        <td>Rs <?= number_format($o['advance_paid']) ?></td>
                                        <td>
                                            <?php if ($o['balance_due'] > 0): ?>
                                                <span class="badge badge-unpaid">Rs <?= number_format($o['balance_due']) ?> Due</span>
                                            <?php else: ?>
                                                <span class="badge badge-paid">Fully Cleared</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-status badge-<?= e($o['order_status']) ?>">
                                                <?= ucfirst(e($o['order_status'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="receipt.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="Thermal POS Receipt">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <?php if ($o['balance_due'] > 0): ?>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="Payments.openCollectModal(<?= $o['id'] ?>, '<?= e($o['order_no']) ?>', '<?= addslashes(e($customer['name'])) ?>', <?= (float)$o['balance_due'] ?>)" title="Collect Balance">
                                                        <i class="bi bi-cash"></i>
                                                    </button>
                                                <?php endif; ?>
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

        <!-- TAB 3: PAYMENTS -->
        <div class="tab-pane fade <?= $activeTab === 'payments' ? 'show active' : '' ?>" id="tab-payments" role="tabpanel">
            <div class="app-card">
                <div class="app-card-header">
                    <h6 class="app-card-title"><i class="bi bi-cash-stack text-success me-2"></i>Payment Transactions Log</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Payment Date</th>
                                <th>Order Ref</th>
                                <th>Payment Method</th>
                                <th>Transaction Ref</th>
                                <th>Amount Paid</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">No payments recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td><?= format_date($p['payment_date']) ?></td>
                                        <td><span class="font-monospace fw-bold"><?= e($p['order_no'] ?: 'General Deposit') ?></span></td>
                                        <td><span class="badge bg-light text-dark border"><?= e($p['payment_method']) ?></span></td>
                                        <td><?= e($p['transaction_ref'] ?: '-') ?></td>
                                        <td class="fw-bold text-success">Rs <?= number_format($p['amount']) ?></td>
                                        <td class="small text-muted"><?= e($p['notes'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 4: PROFILE INFO -->
        <div class="tab-pane fade" id="tab-info" role="tabpanel">
            <div class="app-card">
                <div class="app-card-body p-4">
                    <h5 class="fw-bold mb-3">Edit Customer Information</h5>
                    <form id="customer-profile-edit-form">
                        <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Customer Code</label>
                                <input type="text" name="customer_code" class="form-control" value="<?= e($customer['customer_code']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="male" <?= $customer['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= $customer['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= $customer['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Full Name *</label>
                                <input type="text" name="name" class="form-control" value="<?= e($customer['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Primary Phone *</label>
                                <input type="text" name="phone" class="form-control" value="<?= e($customer['phone']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Alt Phone / WhatsApp</label>
                                <input type="text" name="alt_phone" class="form-control" value="<?= e($customer['alt_phone']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">City</label>
                                <input type="text" name="city" class="form-control" value="<?= e($customer['city']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Full Address</label>
                                <input type="text" name="address" class="form-control" value="<?= e($customer['address']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Special Notes & Fitting Preferences</label>
                                <textarea name="notes" class="form-control" rows="3"><?= e($customer['notes']) ?></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-primary-custom" onclick="Customers.save(document.getElementById('customer-profile-edit-form'))">
                                    Update Customer Details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
