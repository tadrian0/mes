<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/Cors.php';
require_once '../includes/ApiAuth.php';
require_once '../includes/MachineManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new MachineManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $status = $_POST['status'] ?? 'Active';
            $capacity = (float)($_POST['capacity'] ?? 0.0);
            $lastMaintenanceDate = $_POST['last_maintenance_date'] ?? null;
            $location = $_POST['location'] ?? '';
            $model = $_POST['model'] ?? '';
            $plantId = !empty($_POST['plant_id']) ? (int)$_POST['plant_id'] : null;
            $sectionId = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;

            if ($manager->createMachine($name, $status, $capacity, $lastMaintenanceDate, $location, $model, $plantId, $sectionId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create machine']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? null;
            $status = $_POST['status'] ?? null;
            $capacity = isset($_POST['capacity']) ? (float)$_POST['capacity'] : null;
            $lastMaintenanceDate = $_POST['last_maintenance_date'] ?? null; // Can be empty string to set null
            $location = $_POST['location'] ?? null;
            $model = $_POST['model'] ?? null;
            $plantId = isset($_POST['plant_id']) ? (int)$_POST['plant_id'] : null;
            $sectionId = isset($_POST['section_id']) ? (int)$_POST['section_id'] : null;

            if ($manager->updateMachine($id, $name, $status, $capacity, $lastMaintenanceDate, $location, $model, $plantId, $sectionId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update machine']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteMachine($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete machine']);
            }
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $machine = $manager->getMachineById($id);
            if ($machine) {
                echo json_encode(['status' => 'success', 'data' => $machine]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Machine not found']);
            }
            break;

        case 'list':
            $machines = $manager->listMachines();
            echo json_encode(['status' => 'success', 'data' => $machines]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>