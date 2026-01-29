<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/ProductionLogsManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new ProductionLogsManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'start_log':
            $orderId = $_POST['order_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $startTime = $_POST['start_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->startLog($orderId, $machineId, $operatorId, $startTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to start production log']);
            }
            break;

        case 'stop_log':
            $logId = $_POST['log_id'] ?? 0;
            $endOperatorId = $_POST['operator_id'] ?? 0;
            $endTime = $_POST['end_time'] ?? null;
            $shiftCount = (float)($_POST['shift_count'] ?? 0.0);

            if ($manager->stopLog($logId, $endOperatorId, $endTime, $shiftCount)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to stop production log']);
            }
            break;

        case 'update':
            $logId = $_POST['id'] ?? 0;
            $orderId = $_POST['order_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $startOpId = $_POST['start_operator_id'] ?? 0;
            $endOpId = !empty($_POST['end_operator_id']) ? (int)$_POST['end_operator_id'] : null;
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? null;
            $status = $_POST['status'] ?? 'Active';
            $notes = $_POST['notes'] ?? '';

            if ($manager->updateLog($logId, $orderId, $machineId, $startOpId, $endOpId, $start, $end, $status, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update production log']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteLog($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete production log']);
            }
            break;

        case 'list':
            $machineId = !empty($_GET['machine_id']) ? (int)$_GET['machine_id'] : null;
            $operatorId = !empty($_GET['operator_id']) ? (int)$_GET['operator_id'] : null;
            $orderId = !empty($_GET['order_id']) ? (int)$_GET['order_id'] : null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $logs = $manager->listLogs($machineId, $operatorId, $orderId, $startDate, $endDate);
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