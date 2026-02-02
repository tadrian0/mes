<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/RejectReasonManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new RejectReasonManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $categoryId = $_POST['category_id'] ?? 0;
            $plantId = !empty($_POST['plant_id']) ? (int)$_POST['plant_id'] : null;
            $sectionId = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;

            if ($manager->create($name, $categoryId, $plantId, $sectionId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create reject reason']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $categoryId = $_POST['category_id'] ?? 0;
            $plantId = !empty($_POST['plant_id']) ? (int)$_POST['plant_id'] : null;
            $sectionId = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;

            if ($manager->update($id, $name, $categoryId, $plantId, $sectionId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update reject reason']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->delete($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete reject reason']);
            }
            break;

        case 'list':
            $plantId = !empty($_GET['plant_id']) ? (int)$_GET['plant_id'] : null;
            $sectionId = !empty($_GET['section_id']) ? (int)$_GET['section_id'] : null;
            $categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;

            $reasons = $manager->listReasons($plantId, $sectionId, $categoryId);
            echo json_encode(['status' => 'success', 'data' => $reasons]);
            break;

        case 'get_categories':
            $categories = $manager->getCategories();
            echo json_encode(['status' => 'success', 'data' => $categories]);
            break;

        case 'replicate':
            $sourceId = $_POST['source_id'] ?? 0;
            $targets = $_POST['targets'] ?? [];
            if (is_string($targets)) {
                $targets = json_decode($targets, true);
            }

            if ($manager->replicateReason($sourceId, $targets)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to replicate reason']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>