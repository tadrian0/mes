<?php
if ($isAdmin) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_adjustment'])) {
        $adjustmentId = (int) ($_POST['adjustment_id'] ?? 0);

        if ($adjustmentId === $adjustment['AdjustmentID']) {
            $productionOrderId = trim($_POST['edit_production_order_id'] ?? '');
            $articleId = !empty(trim($_POST['edit_article_id'])) ? trim($_POST['edit_article_id']) : null;
            $quantity = !empty(trim($_POST['edit_quantity'])) ? trim($_POST['edit_quantity']) : null;
           
            if ($adjustmentManager->updateAdjustment($adjustmentId, $productionOrderId, $articleId, $quantity)) {
                $message = 'Adjustment updated successfully.';
            } else {
                $message = 'Error updating adjustment.';
            }

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_adjustment'])) {
        $adjustmentId = (int) ($_POST['adjustment_id'] ?? 0);

        if ($adjustmentId === $adjustment['AdjustmentID']) {
            if ($adjustmentManager->deleteAdjustment($adjustmentId)) {
                $message = 'Adjustment deleted successfully.';
            } else {
                $message = 'Error deleting adjustment.';
            }

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}
?>

<div class="modal fade" id="editModal<?php echo $adjustment['AdjustmentID']; ?>" tabindex="-1"
    aria-labelledby="editModalLabel<?php echo $adjustment['AdjustmentID']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $adjustment['AdjustmentID']; ?>">
                    Edit Adjustment #<?php echo $adjustment['AdjustmentID']; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="adjustments.php">
                <div class="modal-body">
                    <input type="hidden" name="adjustment_id" value="<?php echo $adjustment['AdjustmentID']; ?>">

                    <div class="mb-3">
                        <label for="edit_production_order_id_<?php echo $adjustment['AdjustmentID']; ?>" class="form-label">Production Order: <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_production_order_id_<?php echo $adjustment['AdjustmentID']; ?>"
                            name="edit_production_order_id" value="<?php echo htmlspecialchars($adjustment['ProductionOrderId']); ?>" required />
                    </div>
                    <div class="mb-3">
                        <label for="edit_article_id_<?php echo $adjustment['AdjustmentID']; ?>" class="form-label">Article: <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_article_id_<?php echo $adjustment['AdjustmentID']; ?>"
                            name="edit_article_id" value="<?php echo htmlspecialchars($adjustment['ArticleId']); ?>" required />
                    </div>
                    <div class="mb-3">
                        <label for="edit_quantity_<?php echo $adjustment['AdjustmentID']; ?>" class="form-label">Quantity: <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_quantity_<?php echo $adjustment['AdjustmentID']; ?>"
                            name="edit_quantity" value="<?php echo htmlspecialchars($adjustment['Quantity']); ?>" required />
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_adjustment" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" action="" style="display: inline;"
    onsubmit="return confirm('Are you sure you want to delete this adjustment?');">
    <input type="hidden" name="adjustment_id" value="<?php echo $adjustment['AdjustmentID']; ?>">
    <button type="submit" name="delete_adjustment" class="btn btn-sm btn-danger">Delete</button>
</form>