<?php
/**
 * ezyTailor - Payments Management API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDB();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $q = trim($_GET['q'] ?? '');
        $limit = (int)($_GET['limit'] ?? 100);

        $sql = "
            SELECT p.*, 
                   c.name as customer_name, c.phone as customer_phone, c.customer_code,
                   o.order_no, o.total_amount as order_total, o.balance_due as current_order_balance
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

        $sql .= " ORDER BY p.id DESC LIMIT " . $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        // Calculate total payments received
        $sumStmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total_received FROM payments");
        $sumRow = $sumStmt->fetch();

        echo json_encode([
            'success' => true,
            'total_received' => $sumRow['total_received'],
            'count' => count($payments),
            'data' => $payments
        ]);
        exit;

    } elseif ($action === 'collect') {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = trim($_POST['payment_method'] ?? 'Cash');
        $transactionRef = trim($_POST['transaction_ref'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $paymentDate = trim($_POST['payment_date'] ?? date('Y-m-d'));

        if (!$orderId || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please provide a valid order and payment amount.']);
            exit;
        }

        // Fetch current order
        $oStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $oStmt->execute([$orderId]);
        $order = $oStmt->fetch();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            exit;
        }

        $customerId = $order['customer_id'];
        $orderNo = $order['order_no'];
        $currentBalance = (float)$order['balance_due'];

        if ($amount > $currentBalance) {
            $amount = $currentBalance; // Cap at outstanding balance
        }

        $newAdvance = (float)$order['advance_paid'] + $amount;
        $newBalance = max(0, (float)$order['total_amount'] - $newAdvance);
        $newStatus = ($newBalance <= 0) ? 'paid' : 'partial';

        $pdo->beginTransaction();

        // 1. Insert into payments
        $pStmt = $pdo->prepare("
            INSERT INTO payments (order_id, customer_id, amount, payment_method, transaction_ref, notes, payment_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $pStmt->execute([
            $orderId, $customerId, $amount, $paymentMethod,
            $transactionRef ?: null, $notes ?: "Balance payment for $orderNo", $paymentDate
        ]);

        // 2. Update order
        $uStmt = $pdo->prepare("
            UPDATE orders 
            SET advance_paid = ?, balance_due = ?, payment_status = ?
            WHERE id = ?
        ");
        $uStmt->execute([$newAdvance, $newBalance, $newStatus, $orderId]);

        // 3. Insert into ledger as Income
        $lStmt = $pdo->prepare("
            INSERT INTO ledger (type, category, title, amount, date, reference_type, reference_id, notes)
            VALUES ('income', 'Shop Order', ?, ?, ?, 'order', ?, ?)
        ");
        $lStmt->execute([
            "Payment collected for $orderNo",
            $amount,
            $paymentDate,
            $orderId,
            "Method: $paymentMethod" . ($transactionRef ? " (Ref: $transactionRef)" : "")
        ]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => "Payment of Rs " . number_format($amount, 0) . " recorded successfully. Remaining balance: Rs " . number_format($newBalance, 0),
            'new_balance' => $newBalance,
            'payment_status' => $newStatus
        ]);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
