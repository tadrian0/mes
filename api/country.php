<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/CountryManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new CountryManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $isoCode = $_POST['iso_code'] ?? '';

            if ($manager->create($name, $isoCode)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create country']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $isoCode = $_POST['iso_code'] ?? '';

            if ($manager->update($id, $name, $isoCode)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update country']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->delete($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete country']);
            }
            break;

        case 'list':
            $search = $_GET['search'] ?? null;
            $countries = $manager->listAll($search);
            echo json_encode(['status' => 'success', 'data' => $countries]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>