<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/Cors.php';
require_once '../includes/ApiAuth.php';
require_once '../includes/ArticleManager.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $manager = new ArticleManager($pdo);

    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? null;
            $imagePath = $_POST['image_path'] ?? null;
            $qualityControl = $_POST['quality_control'] ?? 'Pending';

            if ($manager->createArticle($name, $description, $imagePath, $qualityControl)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create article']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? null;
            $description = $_POST['description'] ?? null;
            $imagePath = $_POST['image_path'] ?? null;
            $qualityControl = $_POST['quality_control'] ?? null;

            if ($manager->updateArticle($id, $name, $description, $imagePath, $qualityControl)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update article']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($manager->deleteArticle($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete article']);
            }
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $article = $manager->getArticleById($id);
            if ($article) {
                echo json_encode(['status' => 'success', 'data' => $article]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Article not found']);
            }
            break;

        case 'list':
            $articles = $manager->listArticles();
            echo json_encode(['status' => 'success', 'data' => $articles]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>