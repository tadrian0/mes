<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'RecipeManager.php';
require_once INCLUDE_PATH . 'ArticleManager.php';
require_once INCLUDE_PATH . 'MachineManager.php';

$isAdmin = isAdmin();
$recipeManager = new RecipeManager($pdo);
$articleManager = new ArticleManager($pdo);
$machineManager = new MachineManager($pdo);

// --- FILTERS ---
$filterArticle = isset($_GET['filter_article']) && $_GET['filter_article'] !== '' ? (int)$_GET['filter_article'] : null;
$filterMachine = isset($_GET['filter_machine']) && $_GET['filter_machine'] !== '' ? (int)$_GET['filter_machine'] : null;
$search = $_GET['search'] ?? null;

// --- FETCH DATA ---
$recipes = $recipeManager->listRecipes($filterArticle, $filterMachine, $search);
$articles = $articleManager->listArticles();
// Note: You might want to update listMachines to not require filters if passed null, assuming listMachines() exists
$machines = $machineManager->listMachines(); 

$message = '';
$error = '';

// --- POST HANDLERS ---
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    // CREATE
    if (isset($_POST['create'])) {
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        if ($recipeManager->createRecipe(
            (int)$_POST['article_id'],
            (int)$_POST['machine_id'],
            $_POST['version'],
            (float)$_POST['estimated_time'],
            $_POST['description'],
            $isActive,
            $_POST['notes']
        )) {
            header("Location: $redirectUrl&msg=created"); exit;
        } else {
            $error = "Error creating cycle.";
        }
    }

    // UPDATE
    if (isset($_POST['edit'])) {
        $isActive = isset($_POST['edit_is_active']) ? 1 : 0;
        if ($recipeManager->updateRecipe(
            (int)$_POST['recipe_id'],
            (int)$_POST['edit_article_id'],
            (int)$_POST['edit_machine_id'],
            $_POST['edit_version'],
            (float)$_POST['edit_estimated_time'],
            $_POST['edit_description'],
            $isActive,
            $_POST['edit_notes']
        )) {
            header("Location: $redirectUrl&msg=updated"); exit;
        } else {
            $error = "Error updating cycle.";
        }
    }

    // DELETE
    if (isset($_POST['delete'])) {
        if ($recipeManager->deleteRecipe((int)$_POST['recipe_id'])) {
            header("Location: $redirectUrl&msg=deleted"); exit;
        } else {
            $error = "Error deleting cycle.";
        }
    }
}

// MSG
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Cycle created successfully.";
    if ($_GET['msg'] === 'updated') $message = "Cycle updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Cycle deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - Production Cycles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Production Cycles (Recipes)</h1>
        
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Filter Cycles</div>
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Article</label>
                        <select class="form-select form-select-sm" name="filter_article">
                            <option value="">All Articles</option>
                            <?php foreach ($articles as $a): ?>
                                <option value="<?= $a['ArticleID'] ?>" <?= $filterArticle == $a['ArticleID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Machine</label>
                        <select class="form-select form-select-sm" name="filter_machine">
                            <option value="">All Machines</option>
                            <?php foreach ($machines as $m): ?>
                                <option value="<?= $m['MachineID'] ?>" <?= $filterMachine == $m['MachineID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control form-select-sm" placeholder="Version, Description..." value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-search"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fa-solid fa-plus"></i> New Cycle Version
            </button>

            <div class="modal fade" id="addModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Create Cycle / Version</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                        <form method="post">
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Article *</label>
                                        <select name="article_id" class="form-select" required>
                                            <option value="">Select Article...</option>
                                            <?php foreach ($articles as $a): ?>
                                                <option value="<?= $a['ArticleID'] ?>"><?= htmlspecialchars($a['Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Target Machine *</label>
                                        <select name="machine_id" class="form-select" required>
                                            <option value="">Select Machine...</option>
                                            <?php foreach ($machines as $m): ?>
                                                <option value="<?= $m['MachineID'] ?>"><?= htmlspecialchars($m['Name']) ?> (<?= htmlspecialchars($m['Location']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Version *</label>
                                        <input type="text" name="version" class="form-control" placeholder="e.g. 1.0, 2024-RevA" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cycle Time (Seconds) *</label>
                                        <input type="number" step="0.01" name="estimated_time" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="chkActive" checked>
                                            <label class="form-check-label" for="chkActive">Active / Approved</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description / Operation</label>
                                    <textarea name="description" class="form-control" rows="2" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="1"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer"><button type="submit" name="create" class="btn btn-primary">Save Cycle</button></div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Article</th>
                        <th>Version</th>
                        <th>Machine</th>
                        <th>Cycle Time</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recipes as $row): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($row['ArticleName']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['Version']) ?></span></td>
                            <td>
                                <div><?= htmlspecialchars($row['MachineName']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['MachineLoc']) ?></small>
                            </td>
                            <td><?= number_format($row['EstimatedTime'], 2) ?> s</td>
                            <td><?= htmlspecialchars($row['OperationDescription']) ?></td>
                            <td>
                                <?php if ($row['IsActive']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['RecipeID'] ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    
                                    <div class="modal fade" id="editModal<?= $row['RecipeID'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header"><h5 class="modal-title">Edit Cycle #<?= $row['RecipeID'] ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="recipe_id" value="<?= $row['RecipeID'] ?>">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Article</label>
                                                                <select name="edit_article_id" class="form-select" required>
                                                                    <?php foreach ($articles as $a): ?>
                                                                        <option value="<?= $a['ArticleID'] ?>" <?= $a['ArticleID'] == $row['ArticleID'] ? 'selected' : '' ?>><?= htmlspecialchars($a['Name']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Machine</label>
                                                                <select name="edit_machine_id" class="form-select" required>
                                                                    <?php foreach ($machines as $m): ?>
                                                                        <option value="<?= $m['MachineID'] ?>" <?= $m['MachineID'] == $row['MachineID'] ? 'selected' : '' ?>><?= htmlspecialchars($m['Name']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Version</label>
                                                                <input type="text" name="edit_version" class="form-control" value="<?= htmlspecialchars($row['Version']) ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Cycle Time (s)</label>
                                                                <input type="number" step="0.01" name="edit_estimated_time" class="form-control" value="<?= $row['EstimatedTime'] ?>" required>
                                                            </div>
                                                            <div class="col-md-4 d-flex align-items-end">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="edit_is_active" id="chkEdit<?= $row['RecipeID'] ?>" <?= $row['IsActive'] ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="chkEdit<?= $row['RecipeID'] ?>">Active</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="edit_description" class="form-control" required><?= htmlspecialchars($row['OperationDescription']) ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Notes</label>
                                                            <textarea name="edit_notes" class="form-control"><?= htmlspecialchars($row['Notes']) ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer"><button type="submit" name="edit" class="btn btn-warning">Save Changes</button></div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this version?');">
                                        <input type="hidden" name="recipe_id" value="<?= $row['RecipeID'] ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>