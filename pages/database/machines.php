<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'MachineManager.php';

$isAdmin = isAdmin();
$machineManager = new MachineManager($pdo);
$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- CREATE ---
    if (isset($_POST['create'])) {
        $name = trim($_POST['name'] ?? '');
        $status = trim($_POST['status'] ?? 'Active');
        $capacity = floatval(trim($_POST['capacity'] ?? 0));
        $lastMaintenanceDate = trim($_POST['last_maintenance_date'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $model = trim($_POST['model'] ?? '');

        if ($machineManager->createMachine($name, $status, $capacity, $lastMaintenanceDate, $location, $model)) {
            header('Location: ' . $siteBaseUrl . 'pages/database/machines.php?msg=created');
            exit;
        } else {
            $error = 'Error creating machine.';
        }
    }

    // --- EDIT ---
    if (isset($_POST['edit'])) {
        $machineId = (int) ($_POST['machine_id'] ?? 0);
        $name = trim($_POST['edit_name'] ?? '');
        $status = trim($_POST['edit_status'] ?? '');
        $capacity = floatval(trim($_POST['edit_capacity'] ?? 0));
        $lastMaintenanceDate = trim($_POST['edit_last_maintenance_date'] ?? '');
        $location = trim($_POST['edit_location'] ?? '');
        $model = trim($_POST['edit_model'] ?? '');

        if ($machineManager->updateMachine($machineId, $name, $status, $capacity, $lastMaintenanceDate, $location, $model)) {
            header('Location: ' . $siteBaseUrl . 'pages/database/machines.php?msg=updated');
            exit;
        } else {
            $error = 'Error updating machine.';
        }
    }

    // --- DELETE ---
    if (isset($_POST['delete'])) {
        $machineId = (int) ($_POST['machine_id'] ?? 0);
        if ($machineManager->deleteMachine($machineId)) {
            header('Location: ' . $siteBaseUrl . 'includes/pages/machines/machines.php?msg=deleted');
            exit;
        } else {
            $error = 'Error deleting machine.';
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Machine created successfully.";
    if ($_GET['msg'] === 'updated') $message = "Machine updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Machine deleted successfully.";
}

$machines = $machineManager->listMachines();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Machines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Machines</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMachineModal">
                    Add New Machine
                </button>

                <div class="modal fade" id="addMachineModal" tabindex="-1" aria-labelledby="addMachineModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addMachineModalLabel">Add New Machine</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Maintenance">Maintenance</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="capacity" class="form-label">Capacity (tons)</label>
                                        <input type="number" step="0.01" class="form-control" id="capacity" name="capacity" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="last_maintenance_date" class="form-label">Last Maintenance Date</label>
                                        <input type="date" class="form-control" id="last_maintenance_date" name="last_maintenance_date">
                                    </div>
                                    <div class="mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="location" name="location" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" class="form-control" id="model" name="model" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="create" class="btn btn-primary">Add Machine</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h3 class="mt-4">Machine List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Capacity (tons)</th>
                    <th>Last Maintenance</th>
                    <th>Location</th>
                    <th>Model</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($machines as $machine): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($machine['MachineID']); ?></td>
                        <td><?php echo htmlspecialchars($machine['Name']); ?></td>
                        <td><?php echo htmlspecialchars($machine['Status']); ?></td>
                        <td><?php echo htmlspecialchars($machine['Capacity']); ?></td>
                        <td><?php echo htmlspecialchars($machine['LastMaintenanceDate'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($machine['Location']); ?></td>
                        <td><?php echo htmlspecialchars($machine['Model']); ?></td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editModal<?php echo $machine['MachineID']; ?>">
                                    Edit
                                </button>
                                
                                <form method="post" action="" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this machine?');">
                                    <input type="hidden" name="machine_id" value="<?php echo $machine['MachineID']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
                                </form>

                                <div class="modal fade" id="editModal<?php echo $machine['MachineID']; ?>" tabindex="-1"
                                    aria-labelledby="editModalLabel<?php echo $machine['MachineID']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel<?php echo $machine['MachineID']; ?>">Edit Machine</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="post" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="machine_id" value="<?php echo $machine['MachineID']; ?>">
                                                    <div class="mb-3">
                                                        <label for="edit_name" class="form-label">Name</label>
                                                        <input type="text" class="form-control" name="edit_name" value="<?php echo htmlspecialchars($machine['Name']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_status" class="form-label">Status</label>
                                                        <select class="form-control" name="edit_status" required>
                                                            <option value="Active" <?php echo $machine['Status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                                            <option value="Inactive" <?php echo $machine['Status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                            <option value="Maintenance" <?php echo $machine['Status'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_capacity" class="form-label">Capacity (tons)</label>
                                                        <input type="number" step="0.01" class="form-control" name="edit_capacity" value="<?php echo htmlspecialchars($machine['Capacity']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_last_maintenance_date" class="form-label">Last Maintenance Date</label>
                                                        <input type="date" class="form-control" name="edit_last_maintenance_date" value="<?php echo htmlspecialchars($machine['LastMaintenanceDate'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_location" class="form-label">Location</label>
                                                        <input type="text" class="form-control" name="edit_location" value="<?php echo htmlspecialchars($machine['Location']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_model" class="form-label">Model</label>
                                                        <input type="text" class="form-control" name="edit_model" value="<?php echo htmlspecialchars($machine['Model']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>