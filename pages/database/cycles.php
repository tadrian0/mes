<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'RecipeManager.php'; // Assumes RecipeManager and other classes are in this file

$isAdmin = isAdmin();

$recipeManager = new RecipeManager($pdo);

// List all main recipe steps
$recipes = $recipeManager->listRecipes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle the deletion of a recipe step
    if (isset($_POST['delete']) && $isAdmin) {
        $recipeId = (int) $_POST['recipe_id'];

        // Note: The provided RecipeManager->deleteRecipe only deletes the main recipe row.
        // For full data integrity, it should ideally also delete associated inputs/outputs,
        // or the DB should use ON DELETE CASCADE.
        if ($recipeManager->deleteRecipe($recipeId)) {
            $message = "Recipe step deleted successfully.";
            // Refresh the list after deletion
            $recipes = $recipeManager->listRecipes();
        } else {
            $message = "Failed to delete recipe step. It might be in use or a database error occurred.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Cycles (Recipes)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Cycles (Recipes)</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <?php
            include INCLUDE_PATH . 'pages/cycles/cycles-add.php';
            ?>
            <a href="#" class="btn btn-primary mb-3">Add New Recipe Step</a>
            <p class="text-muted small">Note: Add/Edit forms (cycles-add.php, cycles-edit.php) are not yet created.</p>
        <?php endif; ?>

        <h3 class="mt-4">Cycle List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sequence</th>
                    <th>Operation Description</th>
                    <th>Est. Time</th>
                    <th>Machine Type</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recipes as $recipe): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($recipe['RecipeID']); ?></td>
                        <td><?php echo htmlspecialchars($recipe['Sequence']); ?></td>
                        <td><?php echo htmlspecialchars($recipe['OperationDescription'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($recipe['EstimatedTime'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($recipe['MachineType'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($recipe['CreatedAt']); ?></td>
                        <td><?php echo htmlspecialchars($recipe['UpdatedAt']); ?></td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editModal<?php echo $recipe['RecipeID']; ?>">
                                    Edit
                                </button>
                                <form method="post" action="cycles.php" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this recipe step? This may also delete associated inputs and outputs.');">
                                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['RecipeID']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                <?php
                                include INCLUDE_PATH . 'pages/cycles/cycles-edit.php';
                                ?>
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