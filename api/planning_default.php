<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/PlanningDefaultManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new PlanningDefaultManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'ensure_defaults':
            $dates = $_POST['dates'] ?? [];
            if (is_string($dates)) {
                $dates = json_decode($dates, true);
            }
            $machineId = $_POST['machine_id'] ?? '';

            // ensureDefaults is void return type, assume success if no exception
            $manager->ensureDefaults($dates, $machineId);
            echo json_encode(['status' => 'success']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>