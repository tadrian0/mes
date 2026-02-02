<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/ProductionOrderManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new ProductionOrderManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $articleId = $_POST['article_id'] ?? 0;
            $recipeId = !empty($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : null;
            $quantity = (float)($_POST['quantity'] ?? 0.0);
            $startDate = $_POST['start_date'] ?? date('Y-m-d');
            $endDate = $_POST['end_date'] ?? null;
            $status = $_POST['status'] ?? 'Planned';

            if ($manager->createOrder($articleId, $recipeId, $quantity, $startDate, $endDate, $status)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create order']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $recipeId = !empty($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : null;
            $quantity = (float)($_POST['quantity'] ?? 0.0);
            $startDate = $_POST['start_date'] ?? date('Y-m-d');
            $endDate = $_POST['end_date'] ?? null;
            $status = $_POST['status'] ?? 'Planned';

            if ($manager->updateOrder($id, $articleId, $recipeId, $quantity, $startDate, $endDate, $status)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update order']);
            }
            break;

        case 'delete':
            // Soft delete
            $id = $_POST['id'] ?? 0;
            $userId = $_POST['user_id'] ?? 0; // Required for soft delete tracking
            if ($manager->softDeleteOrder($id, $userId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete order']);
            }
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $order = $manager->getOrderById($id);
            if ($order) {
                echo json_encode(['status' => 'success', 'data' => $order]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Order not found']);
            }
            break;

        case 'list':
            $showDeleted = !empty($_GET['show_deleted']);
            $search = $_GET['search'] ?? null;
            $filterArticle = !empty($_GET['article_id']) ? (int)$_GET['article_id'] : null;
            $filterStatus = $_GET['status'] ?? null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $orders = $manager->listOrders($showDeleted, $search, $filterArticle, $filterStatus, $startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $orders]);
            break;

        case 'get_active':
            $machineId = $_GET['machine_id'] ?? 0;
            $order = $manager->getActiveOrderForMachine($machineId);
            if ($order) {
                echo json_encode(['status' => 'success', 'data' => $order]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No active order found']);
            }
            break;

        case 'get_planned':
            $machineId = $_GET['machine_id'] ?? 0;
            $orders = $manager->getPlannedOrders($machineId);
            echo json_encode(['status' => 'success', 'data' => $orders]);
            break;

        case 'start':
            $id = $_POST['id'] ?? 0;
            if ($manager->startOrder($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to start order']);
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