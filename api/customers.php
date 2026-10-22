<?php
/**
 * ezyTailor - Customers API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDB();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
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

        echo json_encode([
            'success' => true,
            'count' => count($customers),
            'data' => $customers
        ]);
        exit;

    } elseif ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid Customer ID']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $customer = $stmt->fetch();

        if (!$customer) {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            exit;
        }

        // Get saved measurements
        $mStmt = $pdo->prepare("SELECT * FROM customer_measurements WHERE customer_id = ? ORDER BY id DESC");
        $mStmt->execute([$id]);
        $customer['measurements'] = $mStmt->fetchAll();

        // Get orders
        $oStmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
        $oStmt->execute([$id]);
        $customer['orders'] = $oStmt->fetchAll();

        // Get payments
        $pStmt = $pdo->prepare("SELECT p.*, o.order_no FROM payments p LEFT JOIN orders o ON p.order_id = o.id WHERE p.customer_id = ? ORDER BY p.id DESC");
        $pStmt->execute([$id]);
        $customer['payments'] = $pStmt->fetchAll();

        // Calculate summary
        $stmtSum = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as spent, COALESCE(SUM(balance_due), 0) as balance FROM orders WHERE customer_id = ?");
        $stmtSum->execute([$id]);
        $customer['summary'] = $stmtSum->fetch();

        echo json_encode(['success' => true, 'data' => $customer]);
        exit;

    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $alt_phone = trim($_POST['alt_phone'] ?? '');
        $city = trim($_POST['city'] ?? 'Lahore');
        $gender = trim($_POST['gender'] ?? 'male');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Customer name and phone number are required.']);
            exit;
        }

        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("
                UPDATE customers 
                SET name = ?, phone = ?, alt_phone = ?, city = ?, gender = ?, address = ?, notes = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $phone, $alt_phone ?: null, $city, $gender, $address ?: null, $notes ?: null, $id]);
            $customerId = $id;
            $msg = 'Customer details updated successfully.';
        } else {
            // Create
            $code = trim($_POST['customer_code'] ?? '');
            if (empty($code)) {
                $code = generate_customer_code($pdo);
            }

            $stmt = $pdo->prepare("
                INSERT INTO customers (customer_code, name, phone, alt_phone, city, gender, address, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$code, $name, $phone, $alt_phone ?: null, $city, $gender, $address ?: null, $notes ?: null]);
            $customerId = $pdo->lastInsertId();
            $msg = 'Customer created successfully with code: ' . $code;
        }

        echo json_encode([
            'success' => true,
            'id' => $customerId,
            'message' => $msg
        ]);
        exit;

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Customer deleted successfully.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
