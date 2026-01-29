<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/RecipeManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Note: RecipeOutputManager is in RecipeManager.php
    $manager = new RecipeOutputManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $recipeId = $_POST['recipe_id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $quantity = (float)($_POST['quantity'] ?? 1.0);
            $unit = $_POST['unit'] ?? 'unit';
            $isPrimary = !empty($_POST['is_primary']);

            if ($manager->createOutput($recipeId, $articleId, $quantity, $unit, $isPrimary)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create recipe output']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $recipeId = !empty($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : null;
            $articleId = !empty($_POST['article_id']) ? (int)$_POST['article_id'] : null;
            $quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : null;
            $unit = $_POST['unit'] ?? null;
            $isPrimary = isset($_POST['is_primary']) ? (bool)$_POST['is_primary'] : null;

            if ($manager->updateOutput($id, $recipeId, $articleId, $quantity, $unit, $isPrimary)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update recipe output']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteOutput($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete recipe output']);
            }
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $output = $manager->getOutputById($id);
            if ($output) {
                echo json_encode(['status' => 'success', 'data' => $output]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Recipe output not found']);
            }
            break;

        case 'list':
            $outputs = $manager->listOutputs();
            echo json_encode(['status' => 'success', 'data' => $outputs]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>