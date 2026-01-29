<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/MachineStopManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new MachineStopManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'start_stop':
            $machineId = $_POST['machine_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $orderId = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;
            $startTime = $_POST['start_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->startStop($machineId, $operatorId, $orderId, $startTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to start machine stop']);
            }
            break;

        case 'end_stop':
            $stopId = $_POST['stop_id'] ?? 0;
            $categoryId = $_POST['category_id'] ?? 0;
            $reasonId = $_POST['reason_id'] ?? 0;
            $endTime = $_POST['end_time'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->endStop($stopId, $categoryId, $reasonId, $endTime, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to end machine stop']);
            }
            break;

        case 'update':
            $stopId = $_POST['stop_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $orderId = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;
            $catId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $reasonId = !empty($_POST['reason_id']) ? (int)$_POST['reason_id'] : null;
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? null;
            $notes = $_POST['notes'] ?? '';

            if ($manager->updateStop($stopId, $machineId, $operatorId, $orderId, $catId, $reasonId, $start, $end, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update machine stop']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteStop($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete machine stop']);
            }
            break;

        case 'list':
            $machineId = !empty($_GET['machine_id']) ? (int)$_GET['machine_id'] : null;
            $operatorId = !empty($_GET['operator_id']) ? (int)$_GET['operator_id'] : null;
            $catId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            $reasonId = !empty($_GET['reason_id']) ? (int)$_GET['reason_id'] : null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $stops = $manager->listStops($machineId, $operatorId, $catId, $reasonId, $startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $stops]);
            break;

        case 'get_categories':
            $categories = $manager->getCategories();
            echo json_encode(['status' => 'success', 'data' => $categories]);
            break;

        case 'get_reasons':
            $reasons = $manager->getReasons();
            echo json_encode(['status' => 'success', 'data' => $reasons]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>