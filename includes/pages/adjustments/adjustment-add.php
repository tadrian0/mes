<?php
if (!$isAdmin)
    return;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_adjustment'])) {
    $productionOrderId = trim($_POST['production_order_id'] ?? '');
    $articleId = trim($_POST['article_id']) ?? '';
    $quantity = trim($_POST['quantity']) ?? '';

    if ($adjustmentManager->createAdjustment($productionOrderId, $articleId, $quantity)) {
        $message = 'Adjustment created successfully.';
    } else {
        $message = 'Error creating adjustment.';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdjustmentModal">
        Add New Adjustment
    </button>
</div>

<div class="modal fade" id="addAdjustmentModal" tabindex="-1" aria-labelledby="addAdjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdjustmentModalLabel">Add New Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="Adjustments.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="production_order_id" class="form-label">Production Order <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="production_order_id" name="production_order_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="article_id" class="form-label">Article <span class="text-danger">*</label>
                        <input type="number" class="form-control" id="article_id" name="article_id" rows="3" required/>
                    </div>
                     <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" rows="3" required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="create_adjustment" class="btn btn-primary">Add Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>