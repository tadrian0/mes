<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'PlantManager.php';
require_once INCLUDE_PATH . 'CityManager.php';

$isAdmin = isAdmin();
$plantManager = new PlantManager($pdo);
$cityManager = new CityManager($pdo);

$filterCity = isset($_GET['filter_city']) && $_GET['filter_city'] !== '' ? (int)$_GET['filter_city'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$plants = $plantManager->listAll($filterCity, $search);
$cities = $cityManager->listAll();

$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    if (isset($_POST['create'])) {
        if ($plantManager->create($_POST['name'], $_POST['desc'], (int)$_POST['city_id'], $_POST['address'], $_POST['email'], $_POST['phone'], $_POST['manager'], $_POST['status'])) {
            header("Location: $redirectUrl&msg=created"); exit;
        } else { $error = 'Error creating plant.'; }
    }

    if (isset($_POST['edit'])) {
        if ($plantManager->update((int)$_POST['plant_id'], $_POST['edit_name'], $_POST['edit_desc'], (int)$_POST['edit_city_id'], $_POST['edit_address'], $_POST['edit_email'], $_POST['edit_phone'], $_POST['edit_manager'], $_POST['edit_status'])) {
            header("Location: $redirectUrl&msg=updated"); exit;
        } else { $error = 'Error updating plant.'; }
    }

    if (isset($_POST['delete'])) {
        if ($plantManager->delete((int)$_POST['plant_id'])) {
            header("Location: $redirectUrl&msg=deleted"); exit;
        } else { $error = 'Error deleting plant.'; }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Plant added successfully.";
    if ($_GET['msg'] === 'updated') $message = "Plant updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Plant deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - Plants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Plants</h1>
        
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Filter</div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <select class="form-select" name="filter_city">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?= $c['CityID'] ?>" <?= $filterCity == $c['CityID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['Name'] . ', ' . $c['ISOCode']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Plant Name...">
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
                    <i class="fa-solid fa-industry"></i> Add Plant
                </button>
                
                <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">New Plant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <form method="post">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Name *</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">City *</label>
                                            <select class="form-select" name="city_id" required>
                                                <?php foreach ($cities as $c): ?>
                                                    <option value="<?= $c['CityID'] ?>"><?= htmlspecialchars($c['Name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Address *</label>
                                        <input type="text" class="form-control" name="address" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="phone">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Manager</label>
                                            <input type="text" class="form-control" name="manager">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                                <option value="Construction">Construction</option>
                                            </select>
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
                    <th>Location</th>
                    <th>Contact</th>
                    <th>Manager</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plants as $row): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($row['Name']) ?></td>
                        <td>
                            <div><?= htmlspecialchars($row['CityName']) ?> <span class="badge bg-secondary"><?= htmlspecialchars($row['ISOCode']) ?></span></div>
                            <small class="text-muted"><?= htmlspecialchars($row['Address']) ?></small>
                        </td>
                        <td>
                            <?php if ($row['ContactEmail']): ?><div><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($row['ContactEmail']) ?></div><?php endif; ?>
                            <?php if ($row['ContactPhone']): ?><div><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($row['ContactPhone']) ?></div><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['ManagerName'] ?? '-') ?></td>
                        <td>
                            <?php $badge = match($row['Status']) { 'Active'=>'success', 'Inactive'=>'secondary', default=>'warning' }; ?>
                            <span class="badge text-bg-<?= $badge ?>"><?= $row['Status'] ?></span>
                        </td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['PlantID'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this plant?');">
                                    <input type="hidden" name="plant_id" value="<?= $row['PlantID'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>

                                <div class="modal fade" id="editModal<?= $row['PlantID'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header"><h5 class="modal-title">Edit Plant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="plant_id" value="<?= $row['PlantID'] ?>">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" class="form-control" name="edit_name" value="<?= htmlspecialchars($row['Name']) ?>" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">City</label>
                                                            <select class="form-select" name="edit_city_id" required>
                                                                <?php foreach ($cities as $c): ?>
                                                                    <option value="<?= $c['CityID'] ?>" <?= $c['CityID'] == $row['CityID'] ? 'selected' : '' ?>><?= htmlspecialchars($c['Name']) ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Address</label>
                                                        <input type="text" class="form-control" name="edit_address" value="<?= htmlspecialchars($row['Address']) ?>" required>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Email</label>
                                                            <input type="email" class="form-control" name="edit_email" value="<?= htmlspecialchars($row['ContactEmail']) ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Phone</label>
                                                            <input type="text" class="form-control" name="edit_phone" value="<?= htmlspecialchars($row['ContactPhone']) ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Manager</label>
                                                            <input type="text" class="form-control" name="edit_manager" value="<?= htmlspecialchars($row['ManagerName']) ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select class="form-select" name="edit_status">
                                                                <option value="Active" <?= $row['Status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                                                                <option value="Inactive" <?= $row['Status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                                                <option value="Construction" <?= $row['Status'] == 'Construction' ? 'selected' : '' ?>>Construction</option>
                                                            </select>
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