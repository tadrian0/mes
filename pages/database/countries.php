<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'CountryManager.php';

$isAdmin = isAdmin();
$countryManager = new CountryManager($pdo);

$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$countries = $countryManager->listAll($search);

$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    if (isset($_POST['create'])) {
        if ($countryManager->create($_POST['name'], $_POST['iso_code'])) {
            header("Location: $redirectUrl&msg=created");
            exit;
        } else {
            $error = 'Error creating country (ISO Code might exist).';
        }
    }

    if (isset($_POST['edit'])) {
        if ($countryManager->update((int)$_POST['country_id'], $_POST['edit_name'], $_POST['edit_iso_code'])) {
            header("Location: $redirectUrl&msg=updated");
            exit;
        } else {
            $error = 'Error updating country.';
        }
    }

    if (isset($_POST['delete'])) {
        if ($countryManager->delete((int)$_POST['country_id'])) {
            header("Location: $redirectUrl&msg=deleted");
            exit;
        } else {
            $error = 'Error deleting country (might be linked to cities).';
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Country added successfully.";
    if ($_GET['msg'] === 'updated') $message = "Country updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Country deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - Countries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Countries</h1>
        
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Filter</div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Name or ISO...">
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
                    <i class="fa-solid fa-plus"></i> Add Country
                </button>
                
                <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">New Country</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <form method="post">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Country Name</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">ISO Code (3 chars)</label>
                                        <input type="text" class="form-control" name="iso_code" maxlength="3" required style="text-transform:uppercase">
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
                    <th>Name</th>
                    <th>ISO Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($countries as $row): ?>
                    <tr>
                        <td><?= $row['CountryID'] ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($row['Name']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['ISOCode']) ?></span></td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['CountryID'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this country?');">
                                    <input type="hidden" name="country_id" value="<?= $row['CountryID'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>

                                <div class="modal fade" id="editModal<?= $row['CountryID'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header"><h5 class="modal-title">Edit Country</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="country_id" value="<?= $row['CountryID'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Country Name</label>
                                                        <input type="text" class="form-control" name="edit_name" value="<?= htmlspecialchars($row['Name']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">ISO Code</label>
                                                        <input type="text" class="form-control" name="edit_iso_code" value="<?= htmlspecialchars($row['ISOCode']) ?>" maxlength="3" required style="text-transform:uppercase">
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