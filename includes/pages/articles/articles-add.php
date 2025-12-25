<?php
if (!$isAdmin)
    return;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_article'])) {
    $name = trim($_POST['name'] ?? '');
    $description = !empty(trim($_POST['description'])) ? trim($_POST['description']) : null;
    $imagePath = !empty(trim($_POST['image_path'])) ? trim($_POST['image_path']) : null;
    $qualityControl = in_array($_POST['quality_control'] ?? '', ['Pending', 'Approved', 'Rejected'])
        ? $_POST['quality_control']
        : 'Pending';

    if ($articleManager->createArticle($name, $description, $imagePath, $qualityControl)) {
        $message = 'Article created successfully.';
    } else {
        $message = 'Error creating article.';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addArticleModal">
        Add New Article
    </button>
</div>

<div class="modal fade" id="addArticleModal" tabindex="-1" aria-labelledby="addArticleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addArticleModalLabel">Add New Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="articles.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image_path" class="form-label">Image Path</label>
                        <input type="text" class="form-control" id="image_path" name="image_path"
                            placeholder="e.g., /images/articles/part-123.jpg">
                    </div>
                    <div class="mb-3">
                        <label for="quality_control" class="form-label">Quality Control</label>
                        <select class="form-select" id="quality_control" name="quality_control">
                            <option value="Pending" selected>Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="create_article" class="btn btn-primary">Add Article</button>
                </div>
            </form>
        </div>
    </div>
</div>