<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/RejectManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new RejectManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $orderId = $_POST['order_id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $categoryId = $_POST['category_id'] ?? 0;
            $reasonId = $_POST['reason_id'] ?? 0;
            $quantity = (int)($_POST['quantity'] ?? 0);
            $rejectDate = $_POST['reject_date'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->createReject($orderId, $articleId, $operatorId, $machineId, $categoryId, $reasonId, $quantity, $rejectDate, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create reject']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $orderId = $_POST['order_id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $operatorId = $_POST['operator_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $categoryId = $_POST['category_id'] ?? 0;
            $reasonId = $_POST['reason_id'] ?? 0;
            $quantity = (int)($_POST['quantity'] ?? 0);
            $rejectDate = $_POST['reject_date'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if ($manager->updateReject($id, $orderId, $articleId, $operatorId, $machineId, $categoryId, $reasonId, $quantity, $rejectDate, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update reject']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteReject($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete reject']);
            }
            break;

        case 'list':
            $orderId = !empty($_GET['order_id']) ? (int)$_GET['order_id'] : null;
            $articleId = !empty($_GET['article_id']) ? (int)$_GET['article_id'] : null;
            $operatorId = !empty($_GET['operator_id']) ? (int)$_GET['operator_id'] : null;
            $catId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            $reasonId = !empty($_GET['reason_id']) ? (int)$_GET['reason_id'] : null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $rejects = $manager->listRejects($orderId, $articleId, $operatorId, $catId, $reasonId, $startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $rejects]);
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