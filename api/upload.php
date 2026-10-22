<?php
/**
 * ezyTailor - File & Audio Upload API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'fabric';

try {
    if ($type === 'audio') {
        if (!isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No audio file uploaded or upload error.']);
            exit;
        }

        $file = $_FILES['audio_file'];
        $original_name = $file['name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['webm', 'mp4', 'wav', 'ogg', 'mp3'])) {
            $ext = 'webm';
        }

        $filename = 'voice_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $destination = UPLOAD_AUDIO_PATH . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'url' => UPLOAD_URL . '/audio/' . $filename,
                'message' => 'Voice note uploaded successfully'
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move audio file to destination.']);
            exit;
        }

    } else {
        // Image / Fabric upload
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No image file uploaded.']);
            exit;
        }

        $file = $_FILES['photo'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image format. Only JPG, PNG, WEBP allowed.']);
            exit;
        }

        // Limit size: 8MB
        if ($file['size'] > 8 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Image exceeds 8MB size limit.']);
            exit;
        }

        $filename = 'fabric_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $destination = UPLOAD_FABRIC_PATH . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'url' => UPLOAD_URL . '/fabrics/' . $filename,
                'message' => 'Fabric image uploaded successfully'
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save uploaded image.']);
            exit;
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
