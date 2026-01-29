<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/PlantManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new PlantManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $desc = $_POST['description'] ?? '';
            $cityId = $_POST['city_id'] ?? 0;
            $address = $_POST['address'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $managerName = $_POST['manager_name'] ?? '';
            $status = $_POST['status'] ?? 'Active';

            if ($manager->create($name, $desc, $cityId, $address, $email, $phone, $managerName, $status)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create plant']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $desc = $_POST['description'] ?? '';
            $cityId = $_POST['city_id'] ?? 0;
            $address = $_POST['address'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $managerName = $_POST['manager_name'] ?? '';
            $status = $_POST['status'] ?? 'Active';

            if ($manager->update($id, $name, $desc, $cityId, $address, $email, $phone, $managerName, $status)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update plant']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->delete($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete plant']);
            }
            break;

        case 'list':
            $cityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : null;
            $search = $_GET['search'] ?? null;
            $plants = $manager->listAll($cityId, $search);
            echo json_encode(['status' => 'success', 'data' => $plants]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>