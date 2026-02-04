<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/OperatorLogsManager.php';
require_once '../includes/UserManager.php';
require_once '../includes/ProductionOrderManager.php';
require_once '../includes/ProductionLogsManager.php';
require_once '../includes/RejectManager.php';
require_once '../includes/RejectCategoryManager.php';
require_once '../includes/RejectReasonManager.php';

header('Content-Type: application/json');

function sendJson($data) {
    echo json_encode($data);
    exit;
}

$action = $_POST['action'] ?? '';
$machineId = isset($_POST['machine_id']) ? (int)$_POST['machine_id'] : 0;

$logManager = new OperatorLogsManager($pdo);
$userManager = new UserManager($pdo);
$poManager = new ProductionOrderManager($pdo);
$prodLogManager = new ProductionLogsManager($pdo);
$rejectManager = new RejectManager($pdo);
$rejectCatManager = new RejectCategoryManager($pdo);
$rejectReasonManager = new RejectReasonManager($pdo);

try {
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            sendJson(['status' => 'error', 'message' => 'Username and Password are required.']);
        }

        $user = $userManager->getUserByUsername($username);

        if (!$user) {
            sendJson(['status' => 'error', 'message' => 'User not found.']);
        }

        if ($user['OperatorPassword'] === $password) {
            if ($logManager->loginOperator($user['OperatorID'], $machineId)) {
                sendJson(['status' => 'success']);
            } else {
                sendJson(['status' => 'error', 'message' => 'Machine is full or user already logged in.']);
            }
        } else {
            sendJson(['status' => 'error', 'message' => 'Invalid password.']);
        }
    }

    elseif ($action === 'logout') {
        $operatorId = (int)$_POST['operator_id'];
        if ($logManager->logoutOperator($operatorId)) {
            sendJson(['status' => 'success']);
        } else {
            sendJson(['status' => 'error', 'message' => 'Logout failed.']);
        }
    }

    elseif ($action === 'fetch_operators') {
        $operators = $logManager->getActiveOperators($machineId);
        sendJson(['status' => 'success', 'operators' => $operators]);
    }

    elseif ($action === 'start_production') {
        $orderId = (int)$_POST['order_id'];
        
        $operators = $logManager->getActiveOperators($machineId);
        if (empty($operators)) {
            sendJson(['status' => 'error', 'message' => 'An operator must be logged in to start production.']);
        }
        $operatorId = $operators[0]['OperatorID'];

        if ($poManager->startOrder($orderId)) {
            if ($prodLogManager->startLog($orderId, $machineId, $operatorId)) {
                sendJson(['status' => 'success']);
            } else {
                sendJson(['status' => 'error', 'message' => 'Order started but failed to start time log.']);
            }
        } else {
            sendJson(['status' => 'error', 'message' => 'Failed to update order status.']);
        }
    }

    elseif ($action === 'fetch_reject_data') {
        $categories = $rejectCatManager->listCategories();
        $reasons    = $rejectReasonManager->listReasons();

        sendJson([
            'status' => 'success',
            'categories' => $categories,
            'reasons' => $reasons
        ]);
    }

    elseif ($action === 'submit_reject') {
        $qty = (int)$_POST['quantity'];
        $reasonId = (int)$_POST['reason_id'];
        $categoryId = (int)$_POST['category_id'];
        $notes = $_POST['notes'] ?? '';

        if ($qty <= 0 || $reasonId <= 0 || $categoryId <= 0) {
            sendJson(['status' => 'error', 'message' => 'Invalid quantity, category or reason.']);
        }

        $activeOrder = $poManager->getActiveOrderForMachine($machineId);
        if (!$activeOrder) {
            sendJson(['status' => 'error', 'message' => 'No active order.']);
        }

        $operators = $logManager->getActiveOperators($machineId);
        if (empty($operators)) {
            sendJson(['status' => 'error', 'message' => 'No operator logged in.']);
        }
        $operatorId = $operators[0]['OperatorID'];

        if ($rejectManager->createReject(
            $activeOrder['OrderID'],
            $activeOrder['ArticleID'],
            $operatorId,
            $machineId,
            $categoryId,
            $reasonId,
            $qty,
            null,
            $notes
        )) {
            sendJson(['status' => 'success']);
        } else {
            sendJson(['status' => 'error', 'message' => 'Failed to save reject record.']);
        }
    }

    else {
        sendJson(['status' => 'error', 'message' => 'Invalid action.']);
    }

} catch (Exception $e) {
    sendJson(['status' => 'error', 'message' => $e->getMessage()]);
}
?>