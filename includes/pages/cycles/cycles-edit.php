<?php
// This PHP logic block will run on every page load *for every recipe*
// which is inefficient but matches the provided `machines-edit.php` pattern.
// A better pattern would move this logic to the top of cycles.php.

if ($isAdmin) {
    // Handle the edit/update of a recipe step
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_recipe'])) {
        $recipeId = (int) ($_POST['recipe_id'] ?? 0);

        // Check if the submitted ID matches the recipe in the loop
        // This is a safeguard against processing the wrong update
        if ($recipeId === $recipe['RecipeID']) {
            $sequence = (int) ($_POST['edit_sequence'] ?? 0);
            $operationDescription = !empty(trim($_POST['edit_operation_description'])) ? trim($_POST['edit_operation_description']) : null;
            $estimatedTime = !empty(trim($_POST['edit_estimated_time'])) ? (float) trim($_POST['edit_estimated_time']) : null;
            $machineType = !empty(trim($_POST['edit_machine_type'])) ? trim($_POST['edit_machine_type']) : null;

            if ($recipeManager->updateRecipe($recipeId, $sequence, $operationDescription, $estimatedTime, $machineType)) {
                $message = 'Recipe step updated successfully.';
            } else {
                $message = 'Error updating recipe step.';
            }

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // Handle the deletion of a recipe step
    // Note: Your 'articles.php' example places this logic at the top of the main page.
    // Your 'machines-edit.php' example places it here. I am following the 'machines-edit.php' example.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recipe'])) {
        $recipeId = (int) ($_POST['recipe_id'] ?? 0);

        if ($recipeId === $recipe['RecipeID']) {
            if ($recipeManager->deleteRecipe($recipeId)) {
                $message = 'Recipe step deleted successfully.';
            } else {
                $message = 'Error deleting recipe step.';
            }

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}
?>

<div class="modal fade" id="editModal<?php echo $recipe['RecipeID']; ?>" tabindex="-1"
    aria-labelledby="editModalLabel<?php echo $recipe['RecipeID']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $recipe['RecipeID']; ?>">
                    Edit Recipe Step #<?php echo $recipe['RecipeID']; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="cycles.php">
                <div class="modal-body">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['RecipeID']; ?>">

                    <div class="mb-3">
                        <label for="edit_sequence_<?php echo $recipe['RecipeID']; ?>"
                            class="form-label">Sequence</label>
                        <input type="number" class="form-control" id="edit_sequence_<?php echo $recipe['RecipeID']; ?>"
                            name="edit_sequence" value="<?php echo htmlspecialchars($recipe['Sequence']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_operation_description_<?php echo $recipe['RecipeID']; ?>"
                            class="form-label">Operation Description</label>
                        <textarea class="form-control"
                            id="edit_operation_description_<?php echo $recipe['RecipeID']; ?>"
                            name="edit_operation_description"><?php echo htmlspecialchars($recipe['OperationDescription'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_estimated_time_<?php echo $recipe['RecipeID']; ?>" class="form-label">Estimated
                            Time</label>
                        <input type="number" step="0.01" class="form-control"
                            id="edit_estimated_time_<?php echo $recipe['RecipeID']; ?>" name="edit_estimated_time"
                            value="<?php echo htmlspecialchars($recipe['EstimatedTime'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="edit_machine_type_<?php echo $recipe['RecipeID']; ?>" class="form-label">Machine
                            Type</label>
                        <input type="text" class="form-control"
                            id="edit_machine_type_<?php echo $recipe['RecipeID']; ?>" name="edit_machine_type"
                            value="<?php echo htmlspecialchars($recipe['MachineType'] ?? ''); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_recipe" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>