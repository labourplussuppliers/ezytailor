<?php
/**
 * ezyTailor - Instant 58mm & 80mm POS Thermal Receipt
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pdo = getDB();
$settings = getSettings();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    die("Error: No order ID specified.");
}

$stmt = $pdo->prepare("
    SELECT o.*, 
           c.name as customer_name, c.phone as customer_phone, c.alt_phone as customer_alt_phone,
           c.customer_code, c.address as customer_address, c.city as customer_city,
           w.name as worker_name,
           cm.title as measurement_title, cm.measurements_json, cm.styles_json
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    LEFT JOIN workers w ON o.assigned_worker_id = w.id
    LEFT JOIN customer_measurements cm ON o.measurement_id = cm.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("Error: Order not found.");
}

// Fetch order items
$iStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$iStmt->execute([$orderId]);
$items = $iStmt->fetchAll();

// If no items, synthesize from order
if (empty($items)) {
    $items = [
        [
            'item_name' => ucwords(str_replace('_', ' ', $order['garment_type'])),
            'quantity' => $order['quantity'],
            'unit_price' => $order['unit_price'],
            'total_price' => $order['total_amount']
        ]
    ];
}

$measurements = json_decode($order['measurements_json'] ?? '[]', true) ?: [];
$styles = json_decode($order['styles_json'] ?? '[]', true) ?: [];

$paperSize = $_GET['size'] ?? ($settings['receipt_paper_size'] ?? '80mm');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt <?= e($order['order_no']) ?> - <?= e($settings['shop_name']) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/print.css?v=<?= APP_VERSION ?>">
</head>
<body>

<!-- On-Screen Control Toolbar (Hidden in Print) -->
<div class="receipt-print-actions bg-dark text-white p-3 text-center d-flex justify-content-center align-items-center gap-3 flex-wrap shadow">
    <button type="button" class="btn btn-success fw-bold px-4" onclick="window.print()">
        <i class="bi bi-printer-fill me-1"></i> Print Thermal Receipt
    </button>
    <div class="btn-group">
        <a href="receipt.php?id=<?= $orderId ?>&size=80mm" class="btn btn-sm <?= $paperSize === '80mm' ? 'btn-primary' : 'btn-outline-light' ?>">
            80mm Roll (Standard POS)
        </a>
        <a href="receipt.php?id=<?= $orderId ?>&size=58mm" class="btn btn-sm <?= $paperSize === '58mm' ? 'btn-primary' : 'btn-outline-light' ?>">
            58mm Roll (Mini POS)
        </a>
    </div>
    <a href="orders.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-arrow-left"></i> Return to Orders
    </a>
</div>

<!-- Receipt Roll Container -->
<div class="receipt-preview-container">
    <div class="receipt-paper size-<?= $paperSize === '58mm' ? '58mm' : '80mm' ?>" id="printable-receipt">
        
        <!-- Shop Header -->
        <div class="receipt-header">
            <div class="receipt-shop-name"><?= e($settings['shop_name']) ?></div>
            <div class="receipt-tagline"><?= e($settings['tagline']) ?></div>
            <div class="receipt-shop-contact">
                <div><i class="bi bi-geo-alt"></i> <?= e($settings['address']) ?></div>
                <div><i class="bi bi-telephone"></i> Tel: <?= e($settings['phone']) ?></div>
                <?php if (!empty($settings['whatsapp'])): ?>
                    <div><i class="bi bi-whatsapp"></i> WhatsApp: <?= e($settings['whatsapp']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Metadata -->
        <div class="receipt-meta-grid">
            <div class="receipt-row bold">
                <span>ORDER #:</span>
                <span><?= e($order['order_no']) ?></span>
            </div>
            <div class="receipt-row">
                <span>Booked:</span>
                <span><?= format_date($order['booking_date'], 'd-M-Y') ?></span>
            </div>
            <!-- Delivery Date Highlight Box -->
            <div class="receipt-highlight-box <?= $order['is_urgent'] ? 'border-2' : '' ?>">
                DELIVERY DATE: <?= strtoupper(format_date($order['delivery_date'], 'D, d M Y')) ?>
                <?php if ($order['is_urgent']): ?>
                    <div style="font-size:10.5px;color:#000;">*** URGENT RUSH ORDER ***</div>
                <?php endif; ?>
            </div>
            <div class="receipt-row">
                <span>Customer:</span>
                <span class="bold"><?= e($order['customer_name']) ?></span>
            </div>
            <div class="receipt-row">
                <span>Phone:</span>
                <span><?= e($order['customer_phone']) ?></span>
            </div>
            <div class="receipt-row">
                <span>Client Code:</span>
                <span><?= e($order['customer_code']) ?></span>
            </div>
            <?php if (!empty($order['worker_name'])): ?>
                <div class="receipt-row">
                    <span>Assigned Tailor:</span>
                    <span><?= e($order['worker_name']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Items Breakdown -->
        <table class="receipt-items-table">
            <thead>
                <tr>
                    <th>Item & Specs</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td>
                            <div class="bold"><?= e($it['item_name']) ?></div>
                            <?php if (!empty($order['fabric_details'])): ?>
                                <small style="font-size:10px;"><?= e($order['fabric_details']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= (int)$it['quantity'] ?></td>
                        <td class="text-right"><?= number_format($it['unit_price']) ?></td>
                        <td class="text-right bold"><?= number_format($it['total_price']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Measurements Breakdown Grid (for Tailor Master & Customer reference) -->
        <?php if (!empty($measurements)): ?>
            <div class="receipt-measurements-section">
                <div class="receipt-measurements-title">
                    SPECIFICATIONS (<?= e($order['measurement_title'] ?: 'FIT SPECS') ?>)
                </div>
                <div class="measurements-compact-grid">
                    <?php foreach ($measurements as $mk => $mv): ?>
                        <div class="receipt-row">
                            <span><?= strtoupper(str_replace('_', ' ', $mk)) ?>:</span>
                            <span class="bold"><?= e($mv) ?>"</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Style Notes -->
                <?php if (!empty($styles)): ?>
                    <div style="border-top:1px dotted #999;margin-top:4px;padding-top:4px;font-size:10px;">
                        <?php foreach ($styles as $sk => $sv): ?>
                            <div>• <?= ucwords(str_replace('_', ' ', $sk)) ?>: <strong><?= e($sv) ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($order['special_instructions'])): ?>
            <div style="font-size:10.5px;margin:5px 0;padding:4px;border:1px dashed #666;">
                <strong>NOTE:</strong> <?= e($order['special_instructions']) ?>
            </div>
        <?php endif; ?>

        <!-- Totals & Balances -->
        <div class="receipt-totals">
            <div class="receipt-row">
                <span>SUBTOTAL:</span>
                <span>Rs <?= number_format($order['total_amount']) ?></span>
            </div>
            <div class="receipt-row">
                <span>ADVANCE PAID:</span>
                <span class="bold">Rs <?= number_format($order['advance_paid']) ?></span>
            </div>
            <!-- Large Balance Box -->
            <div class="receipt-balance-box">
                <span>BALANCE DUE:</span>
                <span>Rs <?= number_format($order['balance_due']) ?></span>
            </div>
        </div>

        <!-- Dynamic Barcode (Rendered via JsBarcode) -->
        <div class="receipt-barcode-wrap">
            <svg id="receipt-barcode" class="receipt-barcode-svg"></svg>
            <div style="font-size:10px;font-family:monospace;letter-spacing:1px;margin-top:2px;"><?= e($order['order_no']) ?></div>
        </div>

        <!-- Signatures Line -->
        <div class="receipt-signature-line">
            <span>Customer Sign: __________</span>
            <span>Master Tailor: __________</span>
        </div>

        <!-- Receipt Footer & Terms -->
        <div class="receipt-footer">
            <div class="bold"><?= e($settings['receipt_footer']) ?></div>
            <div class="receipt-terms">
                <?= nl2br(e($settings['terms'])) ?>
            </div>
            <div style="margin-top:6px;font-size:9px;">
                Printed on <?= date('d M Y, h:i A') ?> • ezyTailor POS
            </div>
        </div>

    </div>
</div>

<!-- Scripts for Barcode -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    JsBarcode("#receipt-barcode", "<?= e($order['order_no']) ?>", {
        format: "CODE128",
        width: 1.5,
        height: 35,
        displayValue: false,
        margin: 0
    });
});
</script>

</body>
</html>
