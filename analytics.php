<?php
/**
 * ezyTailor - Business Analytics & Charts (Chart.js)
 */

$pageTitle = 'Business Analytics';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="page-content">
    
    <!-- Title & Time Filter Range -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><i class="bi bi-graph-up-arrow text-primary me-2"></i><span data-i18n="analytics">Business Analytics & Growth</span></h4>
            <p class="text-muted small mb-0">Visual performance charts, sales trends, garment distribution, and payment collection rate.</p>
        </div>
        
        <!-- Time Filter Buttons -->
        <div class="btn-group shadow-sm">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-analytics-range="today">Today</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-analytics-range="week">This Week</button>
            <button type="button" class="btn btn-sm btn-outline-secondary active" data-analytics-range="month">This Month</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-analytics-range="year">This Year</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-analytics-range="all">Lifetime</button>
        </div>
    </div>

    <!-- Analytics Key Metric Summary Cards -->
    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Total Order Value (Gross Sales)</span>
                <div class="metric-icon-wrap" style="background:#E0E7FF;color:#4F46E5;">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-primary" id="stat_total_value">Rs 0</span>
            </div>
            <div class="metric-progress">
                <div class="metric-progress-bar bg-success" id="stat_collection_progress" style="width: 0%;"></div>
            </div>
            <small class="text-muted mt-2 d-block" id="stat_collection_percent">0% Collected</small>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Total Cash Collected</span>
                <div class="metric-icon-wrap" style="background:#D1FAE5;color:#059669;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-success" id="stat_total_received">Rs 0</span>
            </div>
            <small class="text-muted">Advances + Balance payments</small>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Outstanding Unpaid</span>
                <div class="metric-icon-wrap" style="background:#FEE2E2;color:#DC2626;">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-danger" id="stat_total_unpaid">Rs 0</span>
            </div>
            <small class="text-muted">Awaiting delivery collection</small>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Total Booked Orders</span>
                <div class="metric-icon-wrap" style="background:#FEF3C7;color:#D97706;">
                    <i class="bi bi-bag-check-fill"></i>
                </div>
            </div>
            <div class="metric-value-row">
                <span class="metric-value text-dark" id="stat_total_orders">0</span>
            </div>
            <small class="text-muted"><span id="stat_ready_orders">0</span> suits ready for delivery</small>
        </div>
    </div>

    <!-- Charts Row 1: Revenue & Orders Growth Trend -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="app-card">
                <div class="app-card-header">
                    <span class="app-card-title"><i class="bi bi-activity text-primary"></i> Revenue Trend & Orders Growth</span>
                    <span class="badge bg-light text-muted border">Daily / Monthly Breakdown</span>
                </div>
                <div class="app-card-body p-4">
                    <div style="height: 360px; width: 100%;">
                        <canvas id="revenueChartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Breakdown by Garment Category & Payment Status -->
    <div class="row g-4">
        <!-- Garment Distribution Donut -->
        <div class="col-md-6">
            <div class="app-card h-100">
                <div class="app-card-header">
                    <span class="app-card-title"><i class="bi bi-pie-chart-fill text-primary"></i> Orders by Garment Category</span>
                </div>
                <div class="app-card-body p-4">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="categoryChartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status Distribution Pie -->
        <div class="col-md-6">
            <div class="app-card h-100">
                <div class="app-card-header">
                    <span class="app-card-title"><i class="bi bi-pie-chart text-success"></i> Payment Status Distribution</span>
                </div>
                <div class="app-card-body p-4">
                    <div style="height: 280px; width: 100%;">
                        <canvas id="paymentChartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
