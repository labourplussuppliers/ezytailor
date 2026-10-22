<?php
/**
 * ezyTailor - Smart Measurements & Templates API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$pdo = getDB();
$action = $_GET['action'] ?? 'templates';

try {
    if ($action === 'templates') {
        $stmt = $pdo->query("SELECT * FROM measurement_templates WHERE is_active = 1 ORDER BY id ASC");
        $templates = $stmt->fetchAll();

        foreach ($templates as &$t) {
            $t['fields'] = json_decode($t['fields_json'], true) ?: [];
            $t['styles'] = json_decode($t['styles_json'], true) ?: [];
        }

        echo json_encode(['success' => true, 'data' => $templates]);
        exit;

    } elseif ($action === 'get_template') {
        $key = trim($_GET['key'] ?? '');
        $stmt = $pdo->prepare("SELECT * FROM measurement_templates WHERE template_key = ? OR id = ? LIMIT 1");
        $stmt->execute([$key, (int)$key]);
        $template = $stmt->fetch();

        if ($template) {
            $template['fields'] = json_decode($template['fields_json'], true) ?: [];
            $template['styles'] = json_decode($template['styles_json'], true) ?: [];
            echo json_encode(['success' => true, 'data' => $template]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Template not found']);
        }
        exit;

    } elseif ($action === 'customer_measurements') {
        $customerId = (int)($_GET['customer_id'] ?? 0);
        if (!$customerId) {
            echo json_encode(['success' => false, 'message' => 'Customer ID required']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT cm.*, mt.title as template_name, mt.icon as template_icon
            FROM customer_measurements cm
            LEFT JOIN measurement_templates mt ON cm.template_id = mt.id
            WHERE cm.customer_id = ?
            ORDER BY cm.id DESC
        ");
        $stmt->execute([$customerId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['measurements'] = json_decode($r['measurements_json'], true) ?: [];
            $r['styles'] = json_decode($r['styles_json'], true) ?: [];
        }

        echo json_encode(['success' => true, 'data' => $rows]);
        exit;

    } elseif ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT cm.*, c.name as customer_name, c.phone as customer_phone
            FROM customer_measurements cm
            JOIN customers c ON cm.customer_id = c.id
            WHERE cm.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row) {
            $row['measurements'] = json_decode($row['measurements_json'], true) ?: [];
            $row['styles'] = json_decode($row['styles_json'], true) ?: [];
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Measurement not found']);
        }
        exit;

    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $templateId = !empty($_POST['template_id']) ? (int)$_POST['template_id'] : null;
        $garmentType = trim($_POST['garment_type'] ?? 'male_shalwar_kameez');
        $title = trim($_POST['title'] ?? 'Standard Fit');
        $fabricPhoto = trim($_POST['fabric_photo'] ?? '');
        $audioNote = trim($_POST['audio_note'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Measurements & Styles data
        $measurementsData = $_POST['measurements'] ?? [];
        if (is_string($measurementsData)) {
            $measurementsJson = $measurementsData;
        } else {
            $measurementsJson = json_encode($measurementsData);
        }

        $stylesData = $_POST['styles'] ?? [];
        if (is_string($stylesData)) {
            $stylesJson = $stylesData;
        } else {
            $stylesJson = json_encode($stylesData);
        }

        if (!$customerId) {
            echo json_encode(['success' => false, 'message' => 'Please select a customer.']);
            exit;
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE customer_measurements 
                SET template_id = ?, garment_type = ?, title = ?, measurements_json = ?, styles_json = ?, fabric_photo = ?, audio_note = ?, notes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $templateId, $garmentType, $title, $measurementsJson, $stylesJson,
                $fabricPhoto ?: null, $audioNote ?: null, $notes ?: null, $id
            ]);
            $savedId = $id;
            $msg = 'Measurement updated successfully.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO customer_measurements (customer_id, template_id, garment_type, title, measurements_json, styles_json, fabric_photo, audio_note, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $customerId, $templateId, $garmentType, $title, $measurementsJson, $stylesJson,
                $fabricPhoto ?: null, $audioNote ?: null, $notes ?: null
            ]);
            $savedId = $pdo->lastInsertId();
            $msg = 'Measurement saved successfully for customer.';
        }

        echo json_encode([
            'success' => true,
            'id' => $savedId,
            'message' => $msg
        ]);
        exit;

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM customer_measurements WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Measurement removed successfully.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
