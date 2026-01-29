<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/WagoManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new WagoManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'log_signal':
            $machineId = $_POST['machine_id'] ?? 0;
            $count = $_POST['count'] ?? 0;

            if ($manager->logSignal($machineId, $count)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to log signal']);
            }
            break;

        case 'list':
            $machineId = !empty($_GET['machine_id']) ? (int)$_GET['machine_id'] : null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $logs = $manager->listLogs($machineId, $startDate, $endDate);
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