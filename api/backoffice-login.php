<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/ApiKeyManager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!$pdo) {
     echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
     exit;
}

// Support both JSON and Form Data
$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? $_POST['username'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Username and password required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT OperatorID, OperatorUsername, OperatorPassword, OperatorRoles FROM user WHERE OperatorUsername = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['OperatorPassword']) {
        $roles = explode(';', $user['OperatorRoles']);
        // Check for admin role (case insensitive or whatever convention is used. login.php checked for 'admin')
        if (in_array('admin', $roles) || in_array('Admin', $roles)) {
            try {
                $keyMgr = new ApiKeyManager($pdo);
                $newKey = $keyMgr->createKey(
                    $user['OperatorID'],
                    "Backoffice App Login " . date('Y-m-d H:i'),
                    'ALL',
                    'ALL'
                );

                // Remove password
                unset($user['OperatorPassword']);

                echo json_encode([
                    'status' => 'success',
                    'api_key' => $newKey,
                    'user' => $user
                ]);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to generate API key: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Access denied: Admin role required']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>