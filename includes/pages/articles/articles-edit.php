<?php
// This block runs for each article in the loop (inefficient but matches your pattern)
if ($isAdmin) {
    // Handle update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_article'])) {
        $articleId = (int) ($_POST['article_id'] ?? 0);

        if ($articleId === $article['ArticleID']) {
            $name = trim($_POST['edit_name'] ?? '');
            $description = !empty(trim($_POST['edit_description'])) ? trim($_POST['edit_description']) : null;
            $imagePath = !empty(trim($_POST['edit_image_path'])) ? trim($_POST['edit_image_path']) : null;
            $qualityControl = in_array($_POST['edit_quality_control'] ?? '', ['Pending', 'Approved', 'Rejected'])
                ? $_POST['edit_quality_control']
                : null;

            if ($articleManager->updateArticle($articleId, $name, $description, $imagePath, $qualityControl)) {
                $message = 'Article updated successfully.';
            } else {
                $message = 'Error updating article.';
            }

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // Handle delete (mirroring machines-edit.php style)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article'])) {
        $articleId = (int) ($_POST['article_id'] ?? 0);

        if ($articleId === $article['ArticleID']) {
            if ($articleManager->deleteArticle($articleId)) {
                $message = 'Article deleted successfully.';
            } else {
                $message = 'Error deleting article.';
            }

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}
?>

<div class="modal fade" id="editModal<?php echo $article['ArticleID']; ?>" tabindex="-1"
    aria-labelledby="editModalLabel<?php echo $article['ArticleID']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $article['ArticleID']; ?>">
                    Edit Article #<?php echo $article['ArticleID']; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="articles.php">
                <div class="modal-body">
                    <input type="hidden" name="article_id" value="<?php echo $article['ArticleID']; ?>">

                    <div class="mb-3">
                        <label for="edit_name_<?php echo $article['ArticleID']; ?>" class="form-label">Name <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name_<?php echo $article['ArticleID']; ?>"
                            name="edit_name" value="<?php echo htmlspecialchars($article['Name']); ?>" required
                            maxlength="150">
                    </div>

                    <div class="mb-3">
                        <label for="edit_description_<?php echo $article['ArticleID']; ?>"
                            class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description_<?php echo $article['ArticleID']; ?>"
                            name="edit_description"
                            rows="3"><?php echo htmlspecialchars($article['Description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_image_path_<?php echo $article['ArticleID']; ?>" class="form-label">Image
                            Path</label>
                        <input type="text" class="form-control"
                            id="edit_image_path_<?php echo $article['ArticleID']; ?>" name="edit_image_path"
                            value="<?php echo htmlspecialchars($article['ImagePath'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="edit_quality_control_<?php echo $article['ArticleID']; ?>"
                            class="form-label">Quality Control</label>
                        <select class="form-select" id="edit_quality_control_<?php echo $article['ArticleID']; ?>"
                            name="edit_quality_control">
                            <option value="Pending" <?php echo ($article['QualityControl'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo ($article['QualityControl'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo ($article['QualityControl'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_article" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form (in table Actions column) -->
<form method="post" action="" style="display: inline;"
    onsubmit="return confirm('Are you sure you want to delete this article?');">
    <input type="hidden" name="article_id" value="<?php echo $article['ArticleID']; ?>">
    <button type="submit" name="delete_article" class="btn btn-sm btn-danger">Delete</button>
</form>