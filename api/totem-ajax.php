<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/OperatorLogsManager.php';
require_once '../includes/UserManager.php';
require_once '../includes/ProductionOrderManager.php';
require_once '../includes/ProductionLogsManager.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$machineId = isset($_POST['machine_id']) ? (int)$_POST['machine_id'] : 0;

$logManager = new OperatorLogsManager($pdo);
$userManager = new UserManager($pdo);
$prodOrderManager = new ProductionOrderManager($pdo);
$prodLogManager = new ProductionLogsManager($pdo);

try {
    if ($action === 'start_production') {
        $orderId = (int)$_POST['order_id'];

        $operators = $logManager->getActiveOperators($machineId);
        if (empty($operators)) {
            echo json_encode(['status' => 'error', 'message' => 'No operator logged in to start production.']);
            exit;
        }
        $operatorId = $operators[0]['OperatorID'];

        // Check if there is already an active order on this machine
        $activeOrder = $prodOrderManager->getActiveOrderForMachine($machineId);
        if ($activeOrder && $activeOrder['OrderID'] != $orderId) {
             echo json_encode(['status' => 'error', 'message' => 'Another order is already active on this machine.']);
             exit;
        }

        if ($prodOrderManager->startOrder($orderId)) {
             // Check if log already active
             $activeLog = $prodLogManager->getActiveLog($orderId);
             if (!$activeLog) {
                 if ($prodLogManager->startLog($orderId, $machineId, $operatorId)) {
                    echo json_encode(['status' => 'success']);
                 } else {
                    echo json_encode(['status' => 'error', 'message' => 'Order started but failed to start time log.']);
                 }
             } else {
                 // Log already active, just return success
                 echo json_encode(['status' => 'success']);
             }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update order status.']);
        }
    }

    elseif ($action === 'stop_order') {
        $orderId = (int)$_POST['order_id'];

        $operators = $logManager->getActiveOperators($machineId);
        if (empty($operators)) {
            echo json_encode(['status' => 'error', 'message' => 'No operator logged in to stop production.']);
            exit;
        }
        $operatorId = $operators[0]['OperatorID'];

        if ($prodOrderManager->finishOrder($orderId)) {
            $activeLog = $prodLogManager->getActiveLog($orderId);
            if ($activeLog) {
                $prodLogManager->stopLog($activeLog['LogID'], $operatorId);
            }
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to finish order.']);
        }
    }

    elseif ($action === 'suspend_order') {
        $orderId = (int)$_POST['order_id'];

        $operators = $logManager->getActiveOperators($machineId);
        if (empty($operators)) {
            echo json_encode(['status' => 'error', 'message' => 'No operator logged in to suspend production.']);
            exit;
        }
        $operatorId = $operators[0]['OperatorID'];

        if ($prodOrderManager->suspendOrder($orderId)) {
            $activeLog = $prodLogManager->getActiveLog($orderId);
            if ($activeLog) {
                $prodLogManager->stopLog($activeLog['LogID'], $operatorId);
            }
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to suspend order.']);
        }
    }

    elseif ($action === 'fetch_operators') {
        $operators = $logManager->getActiveOperators($machineId);
        echo json_encode(['status' => 'success', 'operators' => $operators]);
    }

    elseif ($action === 'login') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $user = $userManager->getUserByUsername($username);

        if ($user && $user['OperatorPassword'] === $password) {
            if ($logManager->loginOperator($user['OperatorID'], $machineId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Machine is full (Max 6) or System Error.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    }

    elseif ($action === 'logout') {
        $operatorId = (int)$_POST['operator_id'];
        if ($logManager->logoutOperator($operatorId)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Logout failed']);
        }
    }

    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>