<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'OperatorLogsManager.php';
require_once INCLUDE_PATH . 'UserManager.php'; 
require_once INCLUDE_PATH . 'MachineManager.php'; 

$isAdmin = isAdmin();
$logsManager = new OperatorLogsManager($pdo);
$userManager = new UserManager($pdo);
$machineManager = new MachineManager($pdo);

$filterMachine = isset($_GET['filter_machine']) && $_GET['filter_machine'] !== '' ? (int)$_GET['filter_machine'] : null;
$filterOperator = isset($_GET['filter_operator']) && $_GET['filter_operator'] !== '' ? (int)$_GET['filter_operator'] : null;
$filterStartDate = $_GET['filter_start_date'] ?? null;
$filterEndDate = $_GET['filter_end_date'] ?? null;

$logs = $logsManager->listLogs($filterMachine, $filterOperator, $filterStartDate, $filterEndDate);
$users = $userManager->listUsers(); 
$machines = $machineManager->listMachines();

$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $operatorId = (int)$_POST['operator_id'];
        $machineId = (int)$_POST['machine_id'];
        $loginTime = $_POST['login_time'];
        $logoutTime = $_POST['logout_time'] ?? null;
        $notes = trim($_POST['notes'] ?? '');

        if ($logsManager->createLogManual($operatorId, $machineId, $loginTime, $logoutTime, $notes)) {
            $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET) . '&msg=created';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $error = 'Error creating log entry.';
        }
    }

    if (isset($_POST['edit'])) {
        $logId = (int)$_POST['log_id'];
        $operatorId = (int)$_POST['edit_operator_id'];
        $machineId = (int)$_POST['edit_machine_id'];
        $loginTime = $_POST['edit_login_time'];
        $logoutTime = $_POST['edit_logout_time'] ?? null;
        $notes = trim($_POST['edit_notes'] ?? '');

        if ($logsManager->updateLog($logId, $operatorId, $machineId, $loginTime, $logoutTime, $notes)) {
            $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET) . '&msg=updated';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $error = 'Error updating log entry.';
        }
    }

    if (isset($_POST['delete'])) {
        $logId = (int)$_POST['log_id'];
        if ($logsManager->deleteLog($logId)) {
            $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET) . '&msg=deleted';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $error = 'Error deleting log entry.';
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Log entry created successfully.";
    if ($_GET['msg'] === 'updated') $message = "Log entry updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Log entry deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Operator Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Operator Logs</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <i class="fa-solid fa-filter me-1"></i> Filter Logs
            </div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Machine</label>
                        <select class="form-select" name="filter_machine">
                            <option value="">All Machines</option>
                            <?php foreach ($machines as $m): ?>
                                <option value="<?= $m['MachineID'] ?>" <?= $filterMachine == $m['MachineID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Operator</label>
                        <select class="form-select" name="filter_operator">
                            <option value="">All Operators</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['OperatorID'] ?>" <?= $filterOperator == $u['OperatorID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['OperatorUsername']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From</label>
                        <input type="date" class="form-control" name="filter_start_date" value="<?= htmlspecialchars($filterStartDate ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To</label>
                        <input type="date" class="form-control" name="filter_end_date" value="<?= htmlspecialchars($filterEndDate ?? '') ?>">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 mb-1"><i class="fa-solid fa-search"></i> Filter</button>
                        <a href="operator-logs.php" class="btn btn-outline-secondary w-100 btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addLogModal">
                    <i class="fa-solid fa-plus"></i> Add Manual Entry
                </button>

                <div class="modal fade" id="addLogModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Manual Log Entry</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Operator</label>
                                        <select class="form-control" name="operator_id" required>
                                            <option value="">Select Operator...</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['OperatorID'] ?>"><?= htmlspecialchars($user['OperatorUsername']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Machine</label>
                                        <select class="form-control" name="machine_id" required>
                                            <option value="">Select Machine...</option>
                                            <?php foreach ($machines as $machine): ?>
                                                <option value="<?= $machine['MachineID'] ?>"><?= htmlspecialchars($machine['Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Login Time</label>
                                            <input type="datetime-local" class="form-control" name="login_time" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Logout Time</label>
                                            <input type="datetime-local" class="form-control" name="logout_time">
                                            <small class="text-muted">Leave empty if active.</small>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="create" class="btn btn-primary">Save Log</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h3 class="mt-4">Logs History</h3>
        
        <?php if (empty($logs)): ?>
            <div class="alert alert-warning">No logs found for the selected criteria.</div>
        <?php else: ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Operator</th>
                        <th>Machine</th>
                        <th>Login</th>
                        <th>Logout</th>
                        <th>Duration</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['LogID']) ?></td>
                            <td><?= htmlspecialchars($log['OperatorName'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($log['MachineName'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($log['LoginTime']) ?></td>
                            <td>
                                <?php if ($log['LogoutTime']): ?>
                                    <?= htmlspecialchars($log['LogoutTime']) ?>
                                <?php else: ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    if ($log['DurationMinutes']) {
                                        echo number_format($log['DurationMinutes'], 0) . ' min';
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td><small><?= htmlspecialchars($log['Notes']) ?></small></td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editLogModal<?= $log['LogID'] ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this log entry?');">
                                        <input type="hidden" name="log_id" value="<?= $log['LogID'] ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>

                                    <div class="modal fade" id="editLogModal<?= $log['LogID'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Log #<?= $log['LogID'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="post" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="log_id" value="<?= $log['LogID'] ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Operator</label>
                                                            <select class="form-control" name="edit_operator_id" required>
                                                                <?php foreach ($users as $user): ?>
                                                                    <option value="<?= $user['OperatorID'] ?>" <?= $user['OperatorID'] == $log['OperatorID'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($user['OperatorUsername']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Machine</label>
                                                            <select class="form-control" name="edit_machine_id" required>
                                                                <?php foreach ($machines as $machine): ?>
                                                                    <option value="<?= $machine['MachineID'] ?>" <?= $machine['MachineID'] == $log['MachineID'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($machine['Name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Login Time</label>
                                                                <input type="datetime-local" class="form-control" name="edit_login_time" value="<?= date('Y-m-d\TH:i', strtotime($log['LoginTime'])) ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Logout Time</label>
                                                                <input type="datetime-local" class="form-control" name="edit_logout_time" 
                                                                    value="<?= $log['LogoutTime'] ? date('Y-m-d\TH:i', strtotime($log['LogoutTime'])) : '' ?>">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Notes</label>
                                                            <textarea class="form-control" name="edit_notes"><?= htmlspecialchars($log['Notes']) ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>