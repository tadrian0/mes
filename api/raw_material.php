<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/RawMaterialManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new RawMaterialManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $orderId = $_POST['order_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $batchCode = $_POST['batch_code'] ?? '';
            $articleId = !empty($_POST['article_id']) ? (int)$_POST['article_id'] : null;
            $machineId = !empty($_POST['machine_id']) ? (int)$_POST['machine_id'] : null;
            $quantity = (float)($_POST['quantity'] ?? 1.0);
            $scanTime = $_POST['scan_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->createLog($orderId, $operatorId, $batchCode, $articleId, $machineId, $quantity, $scanTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create raw material log']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $orderId = $_POST['order_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $batchCode = $_POST['batch_code'] ?? '';
            $articleId = !empty($_POST['article_id']) ? (int)$_POST['article_id'] : null;
            $machineId = !empty($_POST['machine_id']) ? (int)$_POST['machine_id'] : null;
            $quantity = (float)($_POST['quantity'] ?? 1.0);
            $scanTime = $_POST['scan_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->updateLog($id, $orderId, $operatorId, $batchCode, $articleId, $machineId, $quantity, $scanTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update raw material log']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteLog($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete raw material log']);
            }
            break;

        case 'list':
            $orderId = !empty($_GET['order_id']) ? (int)$_GET['order_id'] : null;
            $batchFilter = $_GET['batch_code'] ?? null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $logs = $manager->listLogs($orderId, $batchFilter, $startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $logs]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>