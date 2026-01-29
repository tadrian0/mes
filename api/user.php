<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/UserManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new UserManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'Operator';

            if ($manager->createUser($username, $password, $role)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create user (username might be taken)']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? null; // Optional
            $role = $_POST['role'] ?? 'Operator';

            if ($manager->updateUser($id, $username, $password, $role)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteUser($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
            }
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $user = $manager->getUserById($id);
            if ($user) {
                // Don't return password
                unset($user['OperatorPassword']);
                echo json_encode(['status' => 'success', 'data' => $user]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
            }
            break;

        case 'list':
            $search = $_GET['search'] ?? null;
            $role = $_GET['role'] ?? null;
            $users = $manager->listUsers($search, $role);
            // Remove passwords from list
            foreach ($users as &$user) {
                unset($user['OperatorPassword']);
            }
            echo json_encode(['status' => 'success', 'data' => $users]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>