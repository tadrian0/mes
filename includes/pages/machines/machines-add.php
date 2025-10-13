<?php
if (!$isAdmin)
    return;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $name = trim($_POST['name'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');
    $capacity = floatval(trim($_POST['capacity'] ?? 0));
    $lastMaintenanceDate = trim($_POST['last_maintenance_date'] ?? null);
    $location = trim($_POST['location'] ?? '');
    $model = trim($_POST['model'] ?? '');
    if ($machineManager->createMachine($name, $status, $capacity, $lastMaintenanceDate, $location, $model)) {
        $message = 'Machine created successfully.';
    } else {
        $message = 'Error creating machine.';
    }
    header('Location: ' . $siteBaseUrl . '/pages/database/machines.php');
    exit;
}
?>

<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMachineModal">
        Add New Machine
    </button>

    <div class="modal fade" id="addMachineModal" tabindex="-1" aria-labelledby="addMachineModalLabel"
        aria-hidden="true">
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
                            <input type="number" step="0.01" class="form-control" id="capacity" name="capacity"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="last_maintenance_date" class="form-label">Last Maintenance Date</label>
                            <input type="date" class="form-control" id="last_maintenance_date"
                                name="last_maintenance_date">
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