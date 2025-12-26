<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'SectionManager.php';
require_once INCLUDE_PATH . 'PlantManager.php';

$isAdmin = isAdmin();
$sectionManager = new SectionManager($pdo);
$plantManager = new PlantManager($pdo);

// Filters
$filterPlant = isset($_GET['filter_plant']) && $_GET['filter_plant'] !== '' ? (int)$_GET['filter_plant'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

// Fetch Data
$sections = $sectionManager->listAll($filterPlant, $search);
$plants = $plantManager->listAll();

$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    if (isset($_POST['create'])) {
        if ($sectionManager->create($_POST['name'], (int)$_POST['plant_id'], $_POST['desc'], (float)$_POST['area'], (int)$_POST['capacity'])) {
            header("Location: $redirectUrl&msg=created"); exit;
        } else { $error = 'Error creating section.'; }
    }

    if (isset($_POST['edit'])) {
        if ($sectionManager->update((int)$_POST['section_id'], $_POST['edit_name'], (int)$_POST['edit_plant_id'], $_POST['edit_desc'], (float)$_POST['edit_area'], (int)$_POST['edit_capacity'])) {
            header("Location: $redirectUrl&msg=updated"); exit;
        } else { $error = 'Error updating section.'; }
    }

    if (isset($_POST['delete'])) {
        if ($sectionManager->delete((int)$_POST['section_id'])) {
            header("Location: $redirectUrl&msg=deleted"); exit;
        } else { $error = 'Error deleting section.'; }
    }
}

// Messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Section added successfully.";
    if ($_GET['msg'] === 'updated') $message = "Section updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Section deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - Sections</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Sections</h1>
        
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Filter</div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Plant</label>
                        <select class="form-select" name="filter_plant">
                            <option value="">All Plants</option>
                            <?php foreach ($plants as $p): ?>
                                <option value="<?= $p['PlantID'] ?>" <?= $filterPlant == $p['PlantID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Section Name...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fa-solid fa-layer-group"></i> Add Section
                </button>
                
                <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">New Section</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <form method="post">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Section Name</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Plant</label>
                                        <select class="form-select" name="plant_id" required>
                                            <?php foreach ($plants as $p): ?>
                                                <option value="<?= $p['PlantID'] ?>"><?= htmlspecialchars($p['Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Area (m²)</label>
                                            <input type="number" step="0.01" class="form-control" name="area">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Max Capacity</label>
                                            <input type="number" class="form-control" name="capacity">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="desc"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="create" class="btn btn-success">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Plant</th>
                    <th>Location</th>
                    <th>Area / Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sections as $row): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($row['Name']) ?></td>
                        <td><?= htmlspecialchars($row['PlantName']) ?></td>
                        <td><?= htmlspecialchars($row['CityName'] ?? 'Unknown') ?></td>
                        <td>
                            <div>Area: <?= $row['FloorAreaSqM'] ?> m²</div>
                            <small class="text-muted">Cap: <?= $row['MaxCapacity'] ?></small>
                        </td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['SectionID'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this section?');">
                                    <input type="hidden" name="section_id" value="<?= $row['SectionID'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>

                                <div class="modal fade" id="editModal<?= $row['SectionID'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header"><h5 class="modal-title">Edit Section</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="section_id" value="<?= $row['SectionID'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" class="form-control" name="edit_name" value="<?= htmlspecialchars($row['Name']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Plant</label>
                                                        <select class="form-select" name="edit_plant_id" required>
                                                            <?php foreach ($plants as $p): ?>
                                                                <option value="<?= $p['PlantID'] ?>" <?= $p['PlantID'] == $row['PlantID'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($p['Name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Area (m²)</label>
                                                            <input type="number" step="0.01" class="form-control" name="edit_area" value="<?= $row['FloorAreaSqM'] ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Capacity</label>
                                                            <input type="number" class="form-control" name="edit_capacity" value="<?= $row['MaxCapacity'] ?>">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" name="edit_desc"><?= htmlspecialchars($row['Description']) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="edit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>