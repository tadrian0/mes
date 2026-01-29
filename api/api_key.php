<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/ApiKeyManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new ApiKeyManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $userId = $_POST['user_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $permissions = $_POST['permissions'] ?? 'ALL';
            $scope = $_POST['scope'] ?? 'ALL';

            $key = $manager->createKey($userId, $name, $permissions, $scope);
            if ($key) {
                echo json_encode(['status' => 'success', 'data' => ['key_string' => $key]]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create API key']);
            }
            break;

        case 'revoke':
            $keyId = $_POST['key_id'] ?? 0;
            $adminId = $_POST['admin_id'] ?? 0;
            if ($manager->revokeKey($keyId, $adminId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to revoke API key']);
            }
            break;

        case 'list':
            $userId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null;
            $keys = $manager->listKeys($userId);
            echo json_encode(['status' => 'success', 'data' => $keys]);
            break;

        case 'audit_logs':
            $keyId = !empty($_GET['key_id']) ? (int)$_GET['key_id'] : null;
            $filterAction = $_GET['filter_action'] ?? null;
            $logs = $manager->listAuditLogs($keyId, $filterAction);
            echo json_encode(['status' => 'success', 'data' => $logs]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>