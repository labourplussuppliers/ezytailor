<?php
/**
 * ezyTailor - Order Booking Wizard
 */

$pageTitle = 'Book New Order';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();

$selectedCustomerId = (int)($_GET['customer_id'] ?? 0);
$selectedMeasurementId = (int)($_GET['measurement_id'] ?? 0);

// Fetch active customers
$customers = $pdo->query("SELECT id, name, phone, customer_code, city FROM customers ORDER BY name ASC")->fetchAll();

// Fetch templates
$templates = $pdo->query("SELECT * FROM measurement_templates WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

// Fetch workers
$workers = $pdo->query("SELECT * FROM workers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// If customer selected, fetch their measurements
$savedMeasurements = [];
if ($selectedCustomerId) {
    $mStmt = $pdo->prepare("SELECT id, title, garment_type FROM customer_measurements WHERE customer_id = ? ORDER BY id DESC");
    $mStmt->execute([$selectedCustomerId]);
    $savedMeasurements = $mStmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Title & Back -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><i class="bi bi-scissors text-primary me-2"></i>New Tailoring Order Booking</h4>
            <p class="text-muted small mb-0">Record garment specs, quantity, advance payment, fabric photo, and audio voice instructions.</p>
        </div>
        <a href="orders.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    <!-- Booking Form Card -->
    <form id="order-booking-form">
        <input type="hidden" name="total_amount" id="order_total_amount" value="2200">
        <input type="hidden" name="balance_due" id="order_balance_due" value="1200">
        <input type="hidden" name="fabric_photo" id="hidden_fabric_photo" value="">
        <input type="hidden" name="audio_note" id="hidden_audio_note" value="">

        <div class="row g-4">
            
            <!-- Left Column: Customer, Garment & Measurements -->
            <div class="col-lg-7">
                
                <!-- 1. Customer Selection Card -->
                <div class="app-card mb-4">
                    <div class="app-card-header d-flex justify-content-between align-items-center">
                        <span class="app-card-title"><i class="bi bi-person-circle text-primary"></i> 1. Customer Information</span>
                        <button type="button" class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modal-customer-add">
                            <i class="bi bi-person-plus-fill me-1"></i> + New Customer
                        </button>
                    </div>
                    <div class="app-card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Select Customer *</label>
                                <select name="customer_id" id="order_customer_select" class="form-select" required>
                                    <option value="">-- Choose Customer by Name or Phone --</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $selectedCustomerId === (int)$c['id'] ? 'selected' : '' ?>>
                                            <?= e($c['name']) ?> (<?= e($c['phone']) ?>) - <?= e($c['customer_code']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Customer Measurement Fit</label>
                                    <a href="measurements.php<?= $selectedCustomerId ? '?customer_id=' . $selectedCustomerId : '' ?>" class="small text-decoration-none">
                                        <i class="bi bi-rulers"></i> Manage Measurements
                                    </a>
                                </div>
                                <select name="measurement_id" id="order_measurement_select" class="form-select">
                                    <option value="">-- Select Measurement Fit (Optional) --</option>
                                    <?php foreach ($savedMeasurements as $sm): ?>
                                        <option value="<?= $sm['id'] ?>" <?= $selectedMeasurementId === (int)$sm['id'] ? 'selected' : '' ?>>
                                            <?= e($sm['title']) ?> (<?= ucwords(str_replace('_', ' ', $sm['garment_type'])) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted" style="font-size:0.75rem;">If customer has saved measurements, select here to link specs onto the thermal ticket.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Garment Details & Pricing Card -->
                <div class="app-card mb-4">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="bi bi-tag-fill text-primary"></i> 2. Garment & Stitching Details</span>
                    </div>
                    <div class="app-card-body">
                        <div class="row g-3">
                            <!-- Garment Type -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Garment Category *</label>
                                <select name="garment_type" id="order_garment_type" class="form-select" required>
                                    <?php foreach ($templates as $tmpl): ?>
                                        <option value="<?= e($tmpl['template_key']) ?>">
                                            <?= e($tmpl['title']) ?> <?= !empty($tmpl['title_ur']) ? '('.e($tmpl['title_ur']).')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Quantity with Increment / Decrement Buttons -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Quantity (No. of Suits / Pieces)</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-qty-minus"><i class="bi bi-dash"></i></button>
                                    <input type="number" name="quantity" id="order_quantity_input" class="form-control text-center fw-bold" value="1" min="1" max="100">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-qty-plus"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>

                            <!-- Unit Price -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Rate / Unit Price (Rs) *</label>
                                <input type="number" step="10" name="unit_price" id="order_unit_price" class="form-control fw-bold" value="2200" required>
                            </div>

                            <!-- Assigned Karigar / Worker -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Assign to Worker / Karigar</label>
                                <select name="assigned_worker_id" class="form-select">
                                    <option value="">-- Unassigned (Store Cutter) --</option>
                                    <?php foreach ($workers as $w): ?>
                                        <option value="<?= $w['id'] ?>"><?= e($w['name']) ?> (<?= e($w['role']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Fabric Details -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Fabric Details & Brand</label>
                                <input type="text" name="fabric_details" class="form-control" placeholder="e.g. Wash-n-Wear Boski, Pasha White Cotton, 4.5 meters provided">
                            </div>

                            <!-- Special Instructions -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Special Tailoring Instructions & Cutting Notes</label>
                                <textarea name="special_instructions" class="form-control" rows="2" placeholder="e.g. 1.25 inch stiff ban collar, double-button on cuffs, hidden gum patti, deliver with fabric bag"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Photo & Voice Recording Media Card -->
                <div class="app-card">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="bi bi-camera text-primary"></i> 3. Fabric Photo & Audio Voice Instructions</span>
                    </div>
                    <div class="app-card-body">
                        <div class="row g-3">
                            <!-- Fabric Image Swatch Upload -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Fabric / Style Photo</label>
                                <input type="file" id="fabric_photo_input" class="form-control form-control-sm" accept="image/*">
                                <div class="mt-2 text-center">
                                    <img id="fabric_preview_img" src="" alt="Preview" class="img-thumbnail rounded" style="display:none;max-height:100px;">
                                </div>
                            </div>

                            <!-- HTML5 Web Audio API Voice Note Recorder -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Voice Recording (MediaRecorder API)</label>
                                <div class="p-3 border rounded bg-light text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 voice-record-toggle-btn mb-2"
                                            data-target-input="#hidden_audio_note" 
                                            data-target-player="#voice_note_player">
                                        <i class="bi bi-mic-fill"></i> Record Audio Instructions
                                    </button>
                                    <audio id="voice_note_player" controls class="w-100" style="display:none;height:35px;"></audio>
                                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Speak customer requirements clearly into device mic.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Schedule, Payment & Summary -->
            <div class="col-lg-5">
                
                <!-- Delivery & Schedule Card -->
                <div class="app-card mb-4">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="bi bi-calendar-event text-primary"></i> Delivery Schedule</span>
                    </div>
                    <div class="app-card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Booking Date</label>
                            <input type="date" name="booking_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Promised Delivery Date *</label>
                            <input type="date" name="delivery_date" id="order_delivery_date" class="form-control form-control-lg fw-bold text-primary" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                            
                            <!-- Quick Shortcuts -->
                            <div class="d-flex gap-1 mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" data-delivery-preset="1">+1 Day (Urgent)</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" data-delivery-preset="3">+3 Days</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" data-delivery-preset="7">+1 Week</button>
                            </div>
                        </div>

                        <!-- Urgent Toggle -->
                        <div class="form-check form-switch p-3 bg-light rounded border">
                            <input class="form-check-input ms-0 me-3" type="checkbox" name="is_urgent" id="order_is_urgent" value="1">
                            <label class="form-check-label fw-bold text-danger" for="order_is_urgent">
                                <i class="bi bi-lightning-fill"></i> Mark as Urgent Priority Order
                            </label>
                            <small class="text-muted d-block ps-5">Highlights order red on karigar dashboard and thermal receipt.</small>
                        </div>
                    </div>
                </div>

                <!-- Financial Calculation & Advance Payment Card -->
                <div class="app-card mb-4">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="bi bi-wallet2 text-primary"></i> Billing & POS Advance</span>
                    </div>
                    <div class="app-card-body">
                        <!-- Order Total Display -->
                        <div class="p-3 bg-light rounded border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">Total Order Amount:</span>
                                <span class="h4 fw-bold mb-0 text-dark" id="order_total_display">Rs 2,200</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top text-danger">
                                <span class="fw-bold">Remaining Balance Due:</span>
                                <span class="h5 fw-bold mb-0" id="order_balance_display">Rs 1,200</span>
                            </div>
                        </div>

                        <!-- Advance Payment -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Advance Payment Received (Rs)</label>
                            <input type="number" step="10" name="advance_paid" id="order_advance_paid" class="form-control form-control-lg fw-bold text-success" value="1000">
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Cash" selected>Cash (کیش)</option>
                                <option value="JazzCash">JazzCash</option>
                                <option value="EasyPaisa">EasyPaisa</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit & Print Button -->
                <button type="submit" class="btn btn-primary-custom btn-lg w-100 py-3 fw-bold shadow">
                    <i class="bi bi-printer-fill me-2"></i> Confirm Booking & Print POS Thermal Receipt
                </button>

            </div>

        </div>
    </form>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
