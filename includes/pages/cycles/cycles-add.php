<?php
if (!$isAdmin)
    return;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_recipe'])) {
    $sequence = (int) ($_POST['sequence'] ?? 0);
    $operationDescription = !empty(trim($_POST['operation_description'])) ? trim($_POST['operation_description']) : null;
    $estimatedTime = !empty(trim($_POST['estimated_time'])) ? (float) trim($_POST['estimated_time']) : null;
    $machineType = !empty(trim($_POST['machine_type'])) ? trim($_POST['machine_type']) : null;

    if ($recipeManager->createRecipe($sequence, $operationDescription, $estimatedTime, $machineType)) {
        $message = 'Recipe step created successfully.';
    } else {
        $message = 'Error creating recipe step.';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCycleModal">
        Add New Recipe Step
    </button>
</div>

<div class="modal fade" id="addCycleModal" tabindex="-1" aria-labelledby="addCycleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCycleModalLabel">Add New Recipe Step</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="cycles.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="sequence" class="form-label">Sequence</label>
                        <input type="number" class="form-control" id="sequence" name="sequence" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="operation_description" class="form-label">Operation Description</label>
                        <textarea class="form-control" id="operation_description"
                            name="operation_description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="estimated_time" class="form-label">Estimated Time (decimal)</label>
                        <input type="number" step="0.01" class="form-control" id="estimated_time" name="estimated_time"
                            placeholder="e.g., 1.50">
                    </div>
                    <div class="mb-3">
                        <label for="machine_type" class="form-label">Machine Type</label>
                        <input type="text" class="form-control" id="machine_type" name="machine_type"
                            placeholder="e.g., CNC_MILL_V2">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="create_recipe" class="btn btn-primary">Add Recipe Step</button>
                </div>
            </form>
        </div>
    </div>
</div>