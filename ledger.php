<?php
/**
 * ezyTailor - Ledger Book & Expense / Income Tracking
 */

$pageTitle = 'Ledger Book';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();

$type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Build query
$sql = "SELECT * FROM ledger WHERE 1=1 ";
$params = [];

if ($type !== 'all' && in_array($type, ['income', 'expense'])) {
    $sql .= " AND type = ? ";
    $params[] = $type;
}

if ($category !== 'all' && !empty($category)) {
    $sql .= " AND category = ? ";
    $params[] = $category;
}

if (!empty($startDate)) {
    $sql .= " AND date >= ? ";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $sql .= " AND date <= ? ";
    $params[] = $endDate;
}

$sql .= " ORDER BY date DESC, id DESC LIMIT 200 ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$entries = $stmt->fetchAll();

// Overall Totals
$totIncome = $pdo->query("SELECT COALESCE(SUM(amount), 0) as tot FROM ledger WHERE type = 'income'")->fetch()['tot'];
$totExpense = $pdo->query("SELECT COALESCE(SUM(amount), 0) as tot FROM ledger WHERE type = 'expense'")->fetch()['tot'];
$netBalance = $totIncome - $totExpense;

// Today's Totals
$today = date('Y-m-d');
$todayIncome = $pdo->query("SELECT COALESCE(SUM(amount), 0) as tot FROM ledger WHERE type = 'income' AND date = '$today'")->fetch()['tot'];
$todayExpense = $pdo->query("SELECT COALESCE(SUM(amount), 0) as tot FROM ledger WHERE type = 'expense' AND date = '$today'")->fetch()['tot'];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i><span data-i18n="ledger">Ledger Book (کھاتہ بک)</span></h4>
            <p class="text-muted small mb-0">Record daily shop cash-in and cash-out, rent, material purchases, and karigar advances.</p>
        </div>
        <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modal-add-ledger">
            <i class="bi bi-plus-circle me-1"></i> Add Transaction
        </button>
    </div>

    <!-- Summary Metrics Cards (Matching Requirement 6) -->
    <div class="metrics-grid mb-4">
        <!-- Net Balance -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label" data-i18n="net_balance">Net Cash Balance</span>
                <div class="metric-icon-wrap" style="background:#E0E7FF;color:#4F46E5;">
                    <i class="bi bi-bank"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value <?= $netBalance >= 0 ? 'text-primary' : 'text-danger' ?>">
                    Rs <?= number_format($netBalance) ?>
                </span>
            </div>
            <div class="small text-muted">Total Cash-In minus Cash-Out</div>
        </div>

        <!-- Total Taken (Income) -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label" data-i18n="total_income">Total Taken (Cash In)</span>
                <div class="metric-icon-wrap" style="background:#D1FAE5;color:#059669;">
                    <i class="bi bi-arrow-down-left-circle-fill"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-success">Rs <?= number_format($totIncome) ?></span>
            </div>
            <div class="small text-muted">Today's In: <strong>Rs <?= number_format($todayIncome) ?></strong></div>
        </div>

        <!-- Total Given (Expense) -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label" data-i18n="total_expense">Total Given (Cash Out)</span>
                <div class="metric-icon-wrap" style="background:#FEE2E2;color:#DC2626;">
                    <i class="bi bi-arrow-up-right-circle-fill"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-danger">Rs <?= number_format($totExpense) ?></span>
            </div>
            <div class="small text-muted">Today's Out: <strong>Rs <?= number_format($todayExpense) ?></strong></div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="app-card mb-4">
        <div class="app-card-body p-3">
            <form action="ledger.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="type" id="ledger-filter-type" class="form-select form-select-sm">
                        <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Transactions</option>
                        <option value="income" <?= $type === 'income' ? 'selected' : '' ?>>Income (Cash In) Only</option>
                        <option value="expense" <?= $type === 'expense' ? 'selected' : '' ?>>Expense (Cash Out) Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category" id="ledger-filter-category" class="form-select form-select-sm">
                        <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <option value="Shop Order" <?= $category === 'Shop Order' ? 'selected' : '' ?>>Shop Order Payment</option>
                        <option value="Rent" <?= $category === 'Rent' ? 'selected' : '' ?>>Shop Rent</option>
                        <option value="Utility" <?= $category === 'Utility' ? 'selected' : '' ?>>Utility Bills</option>
                        <option value="Salaries" <?= $category === 'Salaries' ? 'selected' : '' ?>>Karigar Wages / Salaries</option>
                        <option value="Fabric/Material" <?= $category === 'Fabric/Material' ? 'selected' : '' ?>>Fabric & Material (بکرم/دھاگہ)</option>
                        <option value="Sale" <?= $category === 'Sale' ? 'selected' : '' ?>>Sale of Fabric / Material</option>
                        <option value="General" <?= $category === 'General' ? 'selected' : '' ?>>General Refreshment / Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($startDate) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($endDate) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm flex-fill">Filter</button>
                    <a href="ledger.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Ledger Entries Table -->
    <div class="app-card">
        <div class="app-card-header">
            <span class="app-card-title"><i class="bi bi-card-checklist text-primary"></i> Daily Cash Movement</span>
            <span class="text-muted small">Showing <?= count($entries) ?> records</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th data-i18n="date">Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Title / Description</th>
                        <th>Cash In (Income)</th>
                        <th>Cash Out (Expense)</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No transactions recorded for the selected filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($entries as $en): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= format_date($en['date']) ?></div>
                                    <small class="text-muted font-monospace">#L-<?= str_pad($en['id'], 4, '0', STR_PAD_LEFT) ?></small>
                                </td>
                                <td>
                                    <?php if ($en['type'] === 'income'): ?>
                                        <span class="badge badge-paid"><i class="bi bi-arrow-down-left"></i> Cash In</span>
                                    <?php else: ?>
                                        <span class="badge badge-unpaid"><i class="bi bi-arrow-up-right"></i> Cash Out</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($en['category']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($en['title']) ?></div>
                                    <?php if ($en['reference_type'] === 'order'): ?>
                                        <small class="text-primary"><i class="bi bi-link-45deg"></i> Linked to Order #<?= (int)$en['reference_id'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold <?= $en['type'] === 'income' ? 'text-success' : 'text-muted' ?>">
                                    <?= $en['type'] === 'income' ? '+ Rs ' . number_format($en['amount']) : '-' ?>
                                </td>
                                <td class="fw-bold <?= $en['type'] === 'expense' ? 'text-danger' : 'text-muted' ?>">
                                    <?= $en['type'] === 'expense' ? '- Rs ' . number_format($en['amount']) : '-' ?>
                                </td>
                                <td class="small text-muted">
                                    <?= e($en['notes'] ?: '-') ?>
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
