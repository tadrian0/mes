<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/CityManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new CityManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $countryId = $_POST['country_id'] ?? 0;
            $postalCode = $_POST['postal_code'] ?? '';

            if ($manager->create($name, $countryId, $postalCode)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create city']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $countryId = $_POST['country_id'] ?? 0;
            $postalCode = $_POST['postal_code'] ?? '';

            if ($manager->update($id, $name, $countryId, $postalCode)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update city']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->delete($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete city']);
            }
            break;

        case 'list':
            $countryId = isset($_GET['country_id']) ? (int)$_GET['country_id'] : null;
            $search = $_GET['search'] ?? null;
            $cities = $manager->listAll($countryId, $search);
            echo json_encode(['status' => 'success', 'data' => $cities]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>