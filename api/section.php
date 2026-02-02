<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/SectionManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new SectionManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $plantId = $_POST['plant_id'] ?? 0;
            $desc = $_POST['description'] ?? '';
            $floorArea = $_POST['floor_area'] ?? 0.0;
            $capacity = $_POST['max_capacity'] ?? 0;

            if ($manager->create($name, $plantId, $desc, $floorArea, $capacity)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create section']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $plantId = $_POST['plant_id'] ?? 0;
            $desc = $_POST['description'] ?? '';
            $floorArea = $_POST['floor_area'] ?? 0.0;
            $capacity = $_POST['max_capacity'] ?? 0;

            if ($manager->update($id, $name, $plantId, $desc, $floorArea, $capacity)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update section']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->delete($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete section']);
            }
            break;

        case 'list':
            $plantId = isset($_GET['plant_id']) ? (int)$_GET['plant_id'] : null;
            $search = $_GET['search'] ?? null;
            $sections = $manager->listAll($plantId, $search);
            echo json_encode(['status' => 'success', 'data' => $sections]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>