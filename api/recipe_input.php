<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/RecipeManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Note: RecipeInputManager is in RecipeManager.php
    $manager = new RecipeInputManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $recipeId = $_POST['recipe_id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $quantity = (float)($_POST['quantity'] ?? 1.0);
            $unit = $_POST['unit'] ?? 'unit';
            $inputType = $_POST['input_type'] ?? 'part';

            if ($manager->createInput($recipeId, $articleId, $quantity, $unit, $inputType)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create recipe input']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $recipeId = !empty($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : null;
            $articleId = !empty($_POST['article_id']) ? (int)$_POST['article_id'] : null;
            $quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : null;
            $unit = $_POST['unit'] ?? null;
            $inputType = $_POST['input_type'] ?? null;

            if ($manager->updateInput($id, $recipeId, $articleId, $quantity, $unit, $inputType)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update recipe input']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteInput($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete recipe input']);
            }
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $input = $manager->getInputById($id);
            if ($input) {
                echo json_encode(['status' => 'success', 'data' => $input]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Recipe input not found']);
            }
            break;

        case 'list':
            $inputs = $manager->listInputs();
            echo json_encode(['status' => 'success', 'data' => $inputs]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>