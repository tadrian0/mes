<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'CityManager.php';
require_once INCLUDE_PATH . 'CountryManager.php';

$isAdmin = isAdmin();
$cityManager = new CityManager($pdo);
$countryManager = new CountryManager($pdo);

// 1. Capture Filters
$filterCountry = isset($_GET['filter_country']) && $_GET['filter_country'] !== '' ? (int)$_GET['filter_country'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

// 2. Fetch Data
$cities = $cityManager->listAll($filterCountry, $search);
$countries = $countryManager->listAll();

$message = '';
$error = '';

// --- HANDLE POST REQUESTS ---
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    if (isset($_POST['create'])) {
        if ($cityManager->create($_POST['name'], (int)$_POST['country_id'], $_POST['postal_code'])) {
            header("Location: $redirectUrl&msg=created"); exit;
        } else { $error = 'Error creating city.'; }
    }

    if (isset($_POST['edit'])) {
        if ($cityManager->update((int)$_POST['city_id'], $_POST['edit_name'], (int)$_POST['edit_country_id'], $_POST['edit_postal_code'])) {
            header("Location: $redirectUrl&msg=updated"); exit;
        } else { $error = 'Error updating city.'; }
    }

    if (isset($_POST['delete'])) {
        if ($cityManager->delete((int)$_POST['city_id'])) {
            header("Location: $redirectUrl&msg=deleted"); exit;
        } else { $error = 'Error deleting city.'; }
    }
}

// Messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "City added successfully.";
    if ($_GET['msg'] === 'updated') $message = "City updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "City deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - Cities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Cities</h1>
        
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Filter</div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Country</label>
                        <select class="form-select" name="filter_country">
                            <option value="">All Countries</option>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?= $c['CountryID'] ?>" <?= $filterCountry == $c['CountryID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="City Name...">
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
                    <i class="fa-solid fa-plus"></i> Add City
                </button>
                
                <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">New City</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <form method="post">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Country</label>
                                        <select class="form-select" name="country_id" required>
                                            <option value="">Select...</option>
                                            <?php foreach ($countries as $c): ?>
                                                <option value="<?= $c['CountryID'] ?>"><?= htmlspecialchars($c['Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" name="postal_code">
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
                    <th>ID</th>
                    <th>City Name</th>
                    <th>Country</th>
                    <th>Postal Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cities as $row): ?>
                    <tr>
                        <td><?= $row['CityID'] ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($row['Name']) ?></td>
                        <td><?= htmlspecialchars($row['CountryName']) ?> <small class="text-muted">(<?= htmlspecialchars($row['ISOCode']) ?>)</small></td>
                        <td><?= htmlspecialchars($row['PostalCode'] ?? '-') ?></td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['CityID'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this city?');">
                                    <input type="hidden" name="city_id" value="<?= $row['CityID'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>

                                <div class="modal fade" id="editModal<?= $row['CityID'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header"><h5 class="modal-title">Edit City</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="city_id" value="<?= $row['CityID'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" class="form-control" name="edit_name" value="<?= htmlspecialchars($row['Name']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Country</label>
                                                        <select class="form-select" name="edit_country_id" required>
                                                            <?php foreach ($countries as $c): ?>
                                                                <option value="<?= $c['CountryID'] ?>" <?= $c['CountryID'] == $row['CountryID'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($c['Name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Postal Code</label>
                                                        <input type="text" class="form-control" name="edit_postal_code" value="<?= htmlspecialchars($row['PostalCode']) ?>">
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