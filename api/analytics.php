<?php
/**
 * ezyTailor - Business Analytics & Chart.js API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDB();
$range = $_GET['range'] ?? 'month'; // today, week, month, year, all

try {
    $dateCondition = "";
    $today = date('Y-m-d');

    if ($range === 'today') {
        $dateCondition = " AND booking_date = '$today' ";
    } elseif ($range === 'week') {
        $last7Days = date('Y-m-d', strtotime('-7 days'));
        $dateCondition = " AND booking_date >= '$last7Days' ";
    } elseif ($range === 'month') {
        $firstDayOfMonth = date('Y-m-01');
        $dateCondition = " AND booking_date >= '$firstDayOfMonth' ";
    } elseif ($range === 'year') {
        $firstDayOfYear = date('Y-01-01');
        $dateCondition = " AND booking_date >= '$firstDayOfYear' ";
    }

    // 1. Core Summary Metrics
    $metricsSql = "
        SELECT 
            COUNT(id) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_order_value,
            COALESCE(SUM(advance_paid), 0) as total_received,
            COALESCE(SUM(balance_due), 0) as total_unpaid,
            SUM(CASE WHEN balance_due > 0 THEN 1 ELSE 0 END) as unpaid_orders_count,
            SUM(CASE WHEN order_status = 'ready' THEN 1 ELSE 0 END) as ready_orders_count,
            SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders_count,
            COUNT(DISTINCT customer_id) as active_customers_count
        FROM orders
        WHERE 1=1 $dateCondition
    ";
    $metrics = $pdo->query($metricsSql)->fetch();

    // Total registered customers
    $totalCustRow = $pdo->query("SELECT COUNT(*) as total_customers FROM customers")->fetch();
    $metrics['total_registered_customers'] = $totalCustRow['total_customers'];

    // 2. Revenue Trend (Daily for past 14 days or Monthly)
    $trendLabels = [];
    $trendRevenue = [];
    $trendOrders = [];

    if ($range === 'year' || $range === 'all') {
        // By Month
        $trendSql = "
            SELECT DATE_FORMAT(booking_date, '%b %Y') as label,
                   SUM(total_amount) as revenue,
                   COUNT(id) as orders_count
            FROM orders
            GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
            ORDER BY MIN(booking_date) ASC
            LIMIT 12
        ";
        $trendRows = $pdo->query($trendSql)->fetchAll();
        foreach ($trendRows as $tr) {
            $trendLabels[] = $tr['label'];
            $trendRevenue[] = (float)$tr['revenue'];
            $trendOrders[] = (int)$tr['orders_count'];
        }
    } else {
        // Daily trend for past 10 days
        for ($i = 9; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dLabel = date('d M', strtotime($d));
            $trendLabels[] = $dLabel;

            $dSql = "SELECT COALESCE(SUM(total_amount), 0) as rev, COUNT(id) as ords FROM orders WHERE booking_date = '$d'";
            $dRow = $pdo->query($dSql)->fetch();
            $trendRevenue[] = (float)$dRow['rev'];
            $trendOrders[] = (int)$dRow['ords'];
        }
    }

    // 3. Garment Category Breakdown
    $catSql = "
        SELECT garment_type, COUNT(id) as count, SUM(total_amount) as total_val
        FROM orders
        WHERE 1=1 $dateCondition
        GROUP BY garment_type
        ORDER BY count DESC
    ";
    $catRows = $pdo->query($catSql)->fetchAll();
    $catLabels = [];
    $catCounts = [];
    foreach ($catRows as $cr) {
        $catLabels[] = ucwords(str_replace('_', ' ', $cr['garment_type']));
        $catCounts[] = (int)$cr['count'];
    }

    // 4. Payment Status Breakdown
    $payStatusSql = "
        SELECT payment_status, COUNT(id) as count
        FROM orders
        WHERE 1=1 $dateCondition
        GROUP BY payment_status
    ";
    $payStatusRows = $pdo->query($payStatusSql)->fetchAll();
    $payStatusData = ['paid' => 0, 'partial' => 0, 'unpaid' => 0];
    foreach ($payStatusRows as $psr) {
        $payStatusData[$psr['payment_status']] = (int)$psr['count'];
    }

    echo json_encode([
        'success' => true,
        'metrics' => $metrics,
        'charts' => [
            'revenue_trend' => [
                'labels' => $trendLabels,
                'revenue' => $trendRevenue,
                'orders' => $trendOrders
            ],
            'categories' => [
                'labels' => $catLabels,
                'counts' => $catCounts
            ],
            'payment_status' => [
                'labels' => ['Paid in Full', 'Partial Payment', 'Unpaid'],
                'counts' => [$payStatusData['paid'], $payStatusData['partial'], $payStatusData['unpaid']]
            ]
        ]
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
