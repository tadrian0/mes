<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/AdjustmentManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new AdjustmentManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $orderId = $_POST['order_id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $quantity = (int)($_POST['quantity'] ?? 0);

            if ($manager->createAdjustment($orderId, $articleId, $quantity)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create adjustment']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $orderId = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;
            $articleId = !empty($_POST['article_id']) ? (int)$_POST['article_id'] : null;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : null;

            if ($manager->updateAdjustment($id, $orderId, $articleId, $quantity)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update adjustment']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteAdjustment($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete adjustment']);
            }
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $adjustment = $manager->getAdjustmentById($id);
            if ($adjustment) {
                echo json_encode(['status' => 'success', 'data' => $adjustment]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Adjustment not found']);
            }
            break;

        case 'list':
            $adjustments = $manager->listAdjustments();
            echo json_encode(['status' => 'success', 'data' => $adjustments]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>