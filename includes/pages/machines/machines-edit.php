<?php
if (!$isAdmin)
    return;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $machineId = (int) ($_POST['machine_id'] ?? 0);
    $name = trim($_POST['edit_name'] ?? '');
    $status = trim($_POST['edit_status'] ?? 'Active');
    $capacity = floatval(trim($_POST['edit_capacity'] ?? 0));
    $lastMaintenanceDate = trim($_POST['edit_last_maintenance_date'] ?? null);
    $location = trim($_POST['edit_location'] ?? '');
    $model = trim($_POST['edit_model'] ?? '');
    if ($machineManager->updateMachine($machineId, $name, $status, $capacity, $lastMaintenanceDate, $location, $model)) {
        $message = 'Machine updated successfully.';
    } else {
        $message = 'Error updating machine.';
    }
    header('Location: ' . $siteBaseUrl . 'includes/pages/machines/machines.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $machineId = (int) ($_POST['machine_id'] ?? 0);
    if ($machineManager->deleteMachine($machineId)) {
        $message = 'Machine deleted successfully.';
    } else {
        $message = 'Error deleting machine.';
    }
    header('Location: ' . $siteBaseUrl . 'includes/pages/machines/machines.php');
    exit;
}
?>

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
                        <label for="edit_name_<?php echo $machine['MachineID']; ?>" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name_<?php echo $machine['MachineID']; ?>"
                            name="edit_name" value="<?php echo htmlspecialchars($machine['Name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status_<?php echo $machine['MachineID']; ?>" class="form-label">Status</label>
                        <select class="form-control" id="edit_status_<?php echo $machine['MachineID']; ?>"
                            name="edit_status" required>
                            <option value="Active" <?php echo $machine['Status'] === 'Active' ? 'selected' : ''; ?>>Active
                            </option>
                            <option value="Inactive" <?php echo $machine['Status'] === 'Inactive' ? 'selected' : ''; ?>>
                                Inactive</option>
                            <option value="Maintenance" <?php echo $machine['Status'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_capacity_<?php echo $machine['MachineID']; ?>" class="form-label">Capacity
                            (tons)</label>
                        <input type="number" step="0.01" class="form-control"
                            id="edit_capacity_<?php echo $machine['MachineID']; ?>" name="edit_capacity"
                            value="<?php echo htmlspecialchars($machine['Capacity']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_maintenance_date_<?php echo $machine['MachineID']; ?>"
                            class="form-label">Last Maintenance Date</label>
                        <input type="date" class="form-control"
                            id="edit_last_maintenance_date_<?php echo $machine['MachineID']; ?>"
                            name="edit_last_maintenance_date"
                            value="<?php echo htmlspecialchars($machine['LastMaintenanceDate'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="edit_location_<?php echo $machine['MachineID']; ?>"
                            class="form-label">Location</label>
                        <input type="text" class="form-control" id="edit_location_<?php echo $machine['MachineID']; ?>"
                            name="edit_location" value="<?php echo htmlspecialchars($machine['Location']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_model_<?php echo $machine['MachineID']; ?>" class="form-label">Model</label>
                        <input type="text" class="form-control" id="edit_model_<?php echo $machine['MachineID']; ?>"
                            name="edit_model" value="<?php echo htmlspecialchars($machine['Model']); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>