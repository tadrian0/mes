<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/OperatorLogsManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new OperatorLogsManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'login':
            $operatorId = $_POST['operator_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;

            if ($manager->loginOperator($operatorId, $machineId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to login operator']);
            }
            break;

        case 'logout':
            $operatorId = $_POST['operator_id'] ?? 0;
            if ($manager->logoutOperator($operatorId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to logout operator']);
            }
            break;

        case 'create_manual':
            $operatorId = $_POST['operator_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $loginTime = $_POST['login_time'] ?? '';
            $logoutTime = $_POST['logout_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->createLogManual($operatorId, $machineId, $loginTime, $logoutTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create manual log']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $loginTime = $_POST['login_time'] ?? '';
            $logoutTime = $_POST['logout_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->updateLog($id, $operatorId, $machineId, $loginTime, $logoutTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update log']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteLog($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete log']);
            }
            break;

        case 'list':
            $machineId = !empty($_GET['machine_id']) ? (int)$_GET['machine_id'] : null;
            $operatorId = !empty($_GET['operator_id']) ? (int)$_GET['operator_id'] : null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $logs = $manager->listLogs($machineId, $operatorId, $startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $logs]);
            break;

        case 'get_active':
            $machineId = $_GET['machine_id'] ?? 0;
            $active = $manager->getActiveOperators($machineId);
            echo json_encode(['status' => 'success', 'data' => $active]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>