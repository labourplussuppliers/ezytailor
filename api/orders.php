<?php
/**
 * ezyTailor - Orders Management API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDB();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $status = $_GET['status'] ?? 'all';
        $q = trim($_GET['q'] ?? '');
        $limit = (int)($_GET['limit'] ?? 50);

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

        if ($status !== 'all') {
            if ($status === 'unpaid') {
                $sql .= " AND o.balance_due > 0 ";
            } elseif ($status === 'urgent') {
                $sql .= " AND o.is_urgent = 1 ";
            } else {
                $sql .= " AND o.order_status = ? ";
                $params[] = $status;
            }
        }

        if ($q !== '') {
            $sql .= " AND (o.order_no LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR o.garment_type LIKE ?) ";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        $sql .= " ORDER BY o.id DESC LIMIT " . $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'count' => count($orders),
            'data' => $orders
        ]);
        exit;

    } elseif ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT o.*, 
                   c.name as customer_name, c.phone as customer_phone, c.alt_phone as customer_alt_phone,
                   c.customer_code, c.address as customer_address, c.city as customer_city,
                   w.name as worker_name, w.phone as worker_phone,
                   cm.title as measurement_title, cm.measurements_json, cm.styles_json
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            LEFT JOIN workers w ON o.assigned_worker_id = w.id
            LEFT JOIN customer_measurements cm ON o.measurement_id = cm.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        // Parse measurements
        $order['measurements'] = json_decode($order['measurements_json'] ?? '[]', true) ?: [];
        $order['styles'] = json_decode($order['styles_json'] ?? '[]', true) ?: [];

        // Order items
        $iStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $iStmt->execute([$id]);
        $order['items'] = $iStmt->fetchAll();

        // Payments
        $pStmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC");
        $pStmt->execute([$id]);
        $order['payments'] = $pStmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $order]);
        exit;

    } elseif ($action === 'create') {
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $measurementId = !empty($_POST['measurement_id']) ? (int)$_POST['measurement_id'] : null;
        $garmentType = trim($_POST['garment_type'] ?? 'male_shalwar_kameez');
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $unitPrice = (float)($_POST['unit_price'] ?? 0);
        $totalAmount = (float)($_POST['total_amount'] ?? ($unitPrice * $quantity));
        $advancePaid = (float)($_POST['advance_paid'] ?? 0);
        $bookingDate = trim($_POST['booking_date'] ?? date('Y-m-d'));
        $deliveryDate = trim($_POST['delivery_date'] ?? date('Y-m-d', strtotime('+7 days')));
        $isUrgent = !empty($_POST['is_urgent']) ? 1 : 0;
        $fabricDetails = trim($_POST['fabric_details'] ?? '');
        $fabricPhoto = trim($_POST['fabric_photo'] ?? '');
        $audioNote = trim($_POST['audio_note'] ?? '');
        $specialInstructions = trim($_POST['special_instructions'] ?? '');
        $assignedWorkerId = !empty($_POST['assigned_worker_id']) ? (int)$_POST['assigned_worker_id'] : null;
        $paymentMethod = trim($_POST['payment_method'] ?? 'Cash');

        if (!$customerId) {
            echo json_encode(['success' => false, 'message' => 'Please select a customer.']);
            exit;
        }

        if ($totalAmount <= 0) {
            $totalAmount = $unitPrice * $quantity;
        }

        $balanceDue = max(0, $totalAmount - $advancePaid);

        if ($balanceDue == 0) {
            $paymentStatus = 'paid';
        } elseif ($advancePaid > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        $orderNo = generate_order_no($pdo);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_no, customer_id, measurement_id, garment_type, quantity,
                unit_price, total_amount, advance_paid, balance_due, payment_status,
                order_status, booking_date, delivery_date, is_urgent, fabric_details,
                fabric_photo, audio_note, special_instructions, assigned_worker_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $orderNo, $customerId, $measurementId, $garmentType, $quantity,
            $unitPrice, $totalAmount, $advancePaid, $balanceDue, $paymentStatus,
            $bookingDate, $deliveryDate, $isUrgent, $fabricDetails ?: null,
            $fabricPhoto ?: null, $audioNote ?: null, $specialInstructions ?: null, $assignedWorkerId
        ]);
        $orderId = $pdo->lastInsertId();

        // Insert order items
        $itemName = ucwords(str_replace('_', ' ', $garmentType));
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items (order_id, item_name, garment_type, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $itemStmt->execute([$orderId, $itemName, $garmentType, $quantity, $unitPrice, $totalAmount]);

        // If advance paid, record in payments and ledger
        if ($advancePaid > 0) {
            $payStmt = $pdo->prepare("
                INSERT INTO payments (order_id, customer_id, amount, payment_method, notes, payment_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $payStmt->execute([$orderId, $customerId, $advancePaid, $paymentMethod, 'Booking Advance for ' . $orderNo, $bookingDate]);

            // Add to ledger as income
            $ledStmt = $pdo->prepare("
                INSERT INTO ledger (type, category, title, amount, date, reference_type, reference_id, notes)
                VALUES ('income', 'Shop Order', ?, ?, ?, 'order', ?, ?)
            ");
            $ledStmt->execute([
                "Advance received for $orderNo ($itemName)",
                $advancePaid,
                $bookingDate,
                $orderId,
                "Payment Method: $paymentMethod"
            ]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'order_no' => $orderNo,
            'message' => "Order booked successfully! Order No: $orderNo"
        ]);
        exit;

    } elseif ($action === 'update_status') {
        $orderId = (int)($_POST['id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');

        $allowed = ['pending', 'cutting', 'stitching', 'ready', 'delivered', 'cancelled'];
        if (!$orderId || !in_array($newStatus, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid order or status']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);

        echo json_encode([
            'success' => true,
            'message' => "Order status updated to " . ucfirst($newStatus)
        ]);
        exit;

    } elseif ($action === 'delete') {
        $orderId = (int)($_POST['id'] ?? 0);
        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);

        echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
