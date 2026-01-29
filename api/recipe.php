<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/RecipeManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new RecipeManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $articleId = $_POST['article_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $version = $_POST['version'] ?? '';
            $estimatedTime = (float)($_POST['estimated_time'] ?? 0.0);
            $opDesc = $_POST['operation_description'] ?? '';
            $isActive = (int)($_POST['is_active'] ?? 1);
            $notes = $_POST['notes'] ?? null;

            if ($manager->createRecipe($articleId, $machineId, $version, $estimatedTime, $opDesc, $isActive, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create recipe']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $articleId = $_POST['article_id'] ?? 0;
            $machineId = $_POST['machine_id'] ?? 0;
            $version = $_POST['version'] ?? '';
            $estimatedTime = (float)($_POST['estimated_time'] ?? 0.0);
            $opDesc = $_POST['operation_description'] ?? '';
            $isActive = (int)($_POST['is_active'] ?? 1);
            $notes = $_POST['notes'] ?? null;

            if ($manager->updateRecipe($id, $articleId, $machineId, $version, $estimatedTime, $opDesc, $isActive, $notes)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update recipe']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteRecipe($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete recipe']);
            }
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $recipe = $manager->getRecipeById($id);
            if ($recipe) {
                echo json_encode(['status' => 'success', 'data' => $recipe]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Recipe not found']);
            }
            break;

        case 'list':
            $filterArticle = !empty($_GET['article_id']) ? (int)$_GET['article_id'] : null;
            $filterMachine = !empty($_GET['machine_id']) ? (int)$_GET['machine_id'] : null;
            $search = $_GET['search'] ?? null;

            $recipes = $manager->listRecipes($filterArticle, $filterMachine, $search);
            echo json_encode(['status' => 'success', 'data' => $recipes]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>