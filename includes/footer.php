<?php
/**
 * ezyTailor - Common Footer, Modals & Scripts
 */
?>
</main> <!-- end app-main -->
</div> <!-- end app-main-wrapper -->
</div> <!-- end app-wrapper -->

<!-- Floating Action Button Speed Dial -->
<div class="fab-container">
    <button type="button" class="fab-btn-main" aria-label="Quick Actions">
        <i class="bi bi-plus"></i>
    </button>
    <div class="fab-actions">
        <button type="button" class="fab-item" data-bs-toggle="modal" data-bs-target="#modal-customer-add">
            <span class="fab-icon-small"><i class="bi bi-person-plus-fill"></i></span>
            <span data-i18n="new_customer">+ Customer</span>
        </button>
        <a href="order_create.php" class="fab-item">
            <span class="fab-icon-small"><i class="bi bi-bag-plus-fill"></i></span>
            <span data-i18n="new_order">+ Order</span>
        </a>
    </div>
</div>

<?php include __DIR__ . '/bottom_nav.php'; ?>

<!-- ========================================== -->
<!-- MODAL: ADD / EDIT CUSTOMER -->
<!-- ========================================== -->
<div class="modal fade" id="modal-customer-add" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus text-primary me-2"></i><span data-i18n="new_customer">Customer Information</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customer-form">
                <input type="hidden" name="id" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Customer Code</label>
                            <input type="text" name="customer_code" class="form-control form-control-sm" placeholder="Auto (e.g. CUST-109)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Gender</label>
                            <select name="gender" class="form-select form-select-sm">
                                <option value="male">Male (مرد)</option>
                                <option value="female">Female (خاتون)</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Chaudhry Bilal">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Primary Phone *</label>
                            <input type="text" name="phone" class="form-control" required placeholder="0300 1234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Alternate Phone / WhatsApp</label>
                            <input type="text" name="alt_phone" class="form-control" placeholder="0321 9876543">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="city" class="form-control form-control-sm" value="Lahore" placeholder="City name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Address</label>
                            <input type="text" name="address" class="form-control form-control-sm" placeholder="Street / Area">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Customer Notes / Preferences</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Prefers stiff collars, VIP customer, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-i18n="cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom btn-sm" data-i18n="save">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: COLLECT PAYMENT -->
<!-- ========================================== -->
<div class="modal fade" id="modal-collect-payment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin me-2"></i><span data-i18n="add_payment">Collect Order Payment</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="collect-payment-form">
                <input type="hidden" name="order_id" id="pay_modal_order_id">
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Order:</span>
                            <strong id="pay_modal_order_no">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Customer:</span>
                            <strong id="pay_modal_customer">-</strong>
                        </div>
                        <div class="d-flex justify-content-between text-danger fw-bold">
                            <span>Balance Due:</span>
                            <span id="pay_modal_balance_display">Rs 0</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount to Collect (Rs) *</label>
                        <input type="number" step="1" name="amount" id="pay_modal_amount" class="form-control form-control-lg fw-bold text-success" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Cash">Cash (کیش)</option>
                                <option value="JazzCash">JazzCash</option>
                                <option value="EasyPaisa">EasyPaisa</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card">POS Card</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Transaction Reference / Slip #</label>
                        <input type="text" name="transaction_ref" class="form-control form-control-sm" placeholder="Optional ref/trx id">
                    </div>

                    <div>
                        <label class="form-label small fw-semibold">Payment Notes</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Received by Master Asif">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: ADD LEDGER ENTRY -->
<!-- ========================================== -->
<div class="modal fade" id="modal-add-ledger" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus text-primary me-2"></i>New Ledger Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ledger-entry-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Transaction Type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_expense" value="expense" checked>
                                <label class="form-check-label text-danger fw-bold" for="type_expense">Expense (خرچہ / Cash Out)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_income" value="income">
                                <label class="form-check-label text-success fw-bold" for="type_income">Income (آمدن / Cash In)</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category" class="form-select">
                                <option value="Rent">Shop Rent (کرایہ)</option>
                                <option value="Utility">Utility Bills (بجلی / گیس)</option>
                                <option value="Salaries">Worker Salaries / Wages</option>
                                <option value="Fabric/Material">Fabric & Material (دھاگہ/بکرم)</option>
                                <option value="General">General / Refreshment</option>
                                <option value="Shop Order">Shop Order Payment</option>
                                <option value="Sale">Sale of Material</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Date</label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (Rs) *</label>
                        <input type="number" step="1" name="amount" class="form-control form-control-lg" required placeholder="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Title / Description *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Monthly rent, Afternoon tea for karigars">
                    </div>

                    <div>
                        <label class="form-label small fw-semibold">Additional Remarks</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Optional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom btn-sm">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Core JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

<!-- App JavaScript Modules -->
<script src="<?= ASSETS_URL ?>/js/i18n.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= ASSETS_URL ?>/js/app.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= ASSETS_URL ?>/js/customers.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= ASSETS_URL ?>/js/measurements.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= ASSETS_URL ?>/js/orders.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= ASSETS_URL ?>/js/payments.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= ASSETS_URL ?>/js/analytics.js?v=<?= APP_VERSION ?>"></script>
</body>
</html>
