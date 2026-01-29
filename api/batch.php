<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/BatchManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new BatchManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $batchCode = $_POST['batch_code'] ?? '';
            $batchType = $_POST['batch_type'] ?? '';
            $orderId = $_POST['order_id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $quantity = (float)($_POST['quantity'] ?? 0.0);
            $printTime = $_POST['print_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->createBatch($batchCode, $batchType, $orderId, $articleId, $operatorId, $machineId, $quantity, $printTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create batch']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $batchCode = $_POST['batch_code'] ?? '';
            $batchType = $_POST['batch_type'] ?? '';
            $orderId = $_POST['order_id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $quantity = (float)($_POST['quantity'] ?? 0.0);
            $printTime = $_POST['print_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->updateBatch($id, $batchCode, $batchType, $orderId, $articleId, $operatorId, $machineId, $quantity, $printTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update batch']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteBatch($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete batch']);
            }
            break;

        case 'list':
            $machineId = !empty($_GET['machine_id']) ? (int)$_GET['machine_id'] : null;
            $operatorId = !empty($_GET['operator_id']) ? (int)$_GET['operator_id'] : null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $search = $_GET['search'] ?? null;

            $batches = $manager->listBatches($machineId, $operatorId, $startDate, $endDate, $search);
            echo json_encode(['status' => 'success', 'data' => $batches]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>