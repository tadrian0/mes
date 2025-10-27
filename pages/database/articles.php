<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'ArticleManager.php'; // Assuming a new ArticleManager.php similar to MachineManager.php

$isAdmin = isAdmin();

$articleManager = new ArticleManager($pdo);

$articles = $articleManager->listArticles();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete']) && $isAdmin) {
        $articleId = (int) $_POST['article_id'];
        if ($articleManager->deleteArticle($articleId)) {
            $message = "Article deleted successfully.";
        } else {
            $message = "Failed to delete article.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Articles</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <?php include INCLUDE_PATH . 'pages/articles/articles-add.php'; ?>
        <?php endif; ?>

        <h3 class="mt-4">Article List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image Path</th>
                    <th>Quality Control</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($article['ArticleID']); ?></td>
                        <td><?php echo htmlspecialchars($article['Name']); ?></td>
                        <td><?php echo htmlspecialchars($article['Description'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($article['ImagePath'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($article['QualityControl']); ?></td>
                        <td><?php echo htmlspecialchars($article['CreatedAt']); ?></td>
                        <td><?php echo htmlspecialchars($article['UpdatedAt']); ?></td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editModal<?php echo $article['ArticleID']; ?>">
                                    Edit
                                </button>
                                <form method="post" action="" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this article?');">
                                    <input type="hidden" name="article_id" value="<?php echo $article['ArticleID']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                <?php include INCLUDE_PATH . 'pages/articles/articles-edit.php'; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>