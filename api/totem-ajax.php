<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/OperatorLogsManager.php';
require_once '../includes/UserManager.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$machineId = isset($_POST['machine_id']) ? (int)$_POST['machine_id'] : 0;

$logManager = new OperatorLogsManager($pdo);
$userManager = new UserManager($pdo);

try {
    if ($action === 'fetch_operators') {
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