<?php
/**
 * ezyTailor - Ledger Book & Expense/Income API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDB();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'summary') {
        $incStmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM ledger WHERE type = 'income'");
        $totalIncome = (float)$incStmt->fetch()['total'];

        $expStmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM ledger WHERE type = 'expense'");
        $totalExpense = (float)$expStmt->fetch()['total'];

        $netBalance = $totalIncome - $totalExpense;

        // Today's summary
        $today = date('Y-m-d');
        $todayInc = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM ledger WHERE type = 'income' AND date = '$today'")->fetch()['total'];
        $todayExp = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM ledger WHERE type = 'expense' AND date = '$today'")->fetch()['total'];

        echo json_encode([
            'success' => true,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_balance' => $netBalance,
            'today_income' => (float)$todayInc,
            'today_expense' => (float)$todayExp
        ]);
        exit;

    } elseif ($action === 'list') {
        $type = $_GET['type'] ?? 'all';
        $category = $_GET['category'] ?? 'all';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $q = trim($_GET['q'] ?? '');

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

        if ($q !== '') {
            $sql .= " AND (title LIKE ? OR notes LIKE ? OR category LIKE ?) ";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        $sql .= " ORDER BY date DESC, id DESC LIMIT 200 ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll();

        // Calculate summary for this filtered list
        $sumIncome = 0;
        $sumExpense = 0;
        foreach ($entries as $e) {
            if ($e['type'] === 'income') $sumIncome += (float)$e['amount'];
            else $sumExpense += (float)$e['amount'];
        }

        echo json_encode([
            'success' => true,
            'count' => count($entries),
            'sum_income' => $sumIncome,
            'sum_expense' => $sumExpense,
            'net_balance' => $sumIncome - $sumExpense,
            'data' => $entries
        ]);
        exit;

    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $type = trim($_POST['type'] ?? 'expense');
        $category = trim($_POST['category'] ?? 'General');
        $title = trim($_POST['title'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $notes = trim($_POST['notes'] ?? '');
        $referenceType = trim($_POST['reference_type'] ?? 'general');

        if (empty($title) || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Title and positive amount are required.']);
            exit;
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE ledger 
                SET type = ?, category = ?, title = ?, amount = ?, date = ?, notes = ?, reference_type = ?
                WHERE id = ?
            ");
            $stmt->execute([$type, $category, $title, $amount, $date, $notes ?: null, $referenceType, $id]);
            $msg = 'Ledger entry updated successfully.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO ledger (type, category, title, amount, date, reference_type, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$type, $category, $title, $amount, $date, $referenceType, $notes ?: null]);
            $msg = 'New ledger transaction added successfully.';
        }

        echo json_encode(['success' => true, 'message' => $msg]);
        exit;

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM ledger WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Ledger record removed successfully.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
