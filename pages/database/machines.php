<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'MachineManager.php';

$isAdmin = isAdmin();

$machineManager = new MachineManager($pdo);

$machines = $machineManager->listMachines();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Machines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Machines</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <?php include INCLUDE_PATH . 'pages/machines/machines-add.php'; ?>
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
                    <th>Created</th>
                    <th>Updated</th>
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
                        <td><?php echo htmlspecialchars($machine['CreatedAt']); ?></td>
                        <td><?php echo htmlspecialchars($machine['UpdatedAt']); ?></td>
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
                                <?php include INCLUDE_PATH . 'pages/machines/machines-edit.php'; ?>
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