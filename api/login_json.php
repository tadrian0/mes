<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/ApiKeyManager.php';
require_once '../includes/Cors.php';

$userTableName = "user";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Try to read JSON input first
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? $_POST['username'] ?? '');
    $password = trim($input['password'] ?? $_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter both username and password.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT OperatorID, OperatorUsername, OperatorPassword, OperatorRoles FROM $userTableName WHERE OperatorUsername = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['OperatorPassword']) {
            $roles = explode(';', $user['OperatorRoles']);
            if (in_array('admin', $roles)) {
                try {
                    $keyMgr = new ApiKeyManager($pdo);
                    $newKey = $keyMgr->createKey(
                        $user['OperatorID'],
                        "Session Key " . date('Y-m-d H:i'),
                        'ALL',
                        'ALL'
                    );

                    echo json_encode([
                        'status' => 'success',
                        'api_key' => $newKey,
                        'user' => [
                            'id' => $user['OperatorID'],
                            'username' => $user['OperatorUsername'],
                            'roles' => $roles
                        ]
                    ]);
                } catch (Exception $e) {
                    echo json_encode(['status' => 'error', 'message' => 'Login successful, but failed to generate security token.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Access denied: Admin role required for backoffice.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    } catch (PDOException $e) {
         echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>