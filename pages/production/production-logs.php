<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'ProductionLogsManager.php';
require_once INCLUDE_PATH . 'UserManager.php';
require_once INCLUDE_PATH . 'MachineManager.php';

$isAdmin = isAdmin();
$logsManager = new ProductionLogsManager($pdo);
$userManager = new UserManager($pdo);
$machineManager = new MachineManager($pdo);

$filterMachine = isset($_GET['filter_machine']) && $_GET['filter_machine'] !== '' ? (int)$_GET['filter_machine'] : null;
$filterOperator = isset($_GET['filter_operator']) && $_GET['filter_operator'] !== '' ? (int)$_GET['filter_operator'] : null;
$filterOrder = isset($_GET['filter_order']) && $_GET['filter_order'] !== '' ? (int)$_GET['filter_order'] : null;
$filterStartDate = $_GET['filter_start_date'] ?? null;
$filterEndDate = $_GET['filter_end_date'] ?? null;

$logs = $logsManager->listLogs($filterMachine, $filterOperator, $filterOrder, $filterStartDate, $filterEndDate);
$users = $userManager->listUsers();
$machines = $machineManager->listMachines();

$ordersStmt = $pdo->query("SELECT OrderID FROM production_order WHERE Status IN ('Planned','Active') ORDER BY OrderID DESC");
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    if (isset($_POST['start_run'])) {
        $orderId = (int)$_POST['order_id'];
        $machineId = (int)$_POST['machine_id'];
        $startOpId = (int)$_POST['start_operator_id'];
        $startTime = $_POST['start_time'];
        $notes = trim($_POST['notes']);

        if ($logsManager->startLog($orderId, $machineId, $startOpId, $startTime, $notes)) {
            header("Location: $redirectUrl&msg=started");
            exit;
        } else {
            $error = 'Error starting run.';
        }
    }

    if (isset($_POST['stop_run'])) {
        $logId = (int)$_POST['log_id'];
        $endOpId = (int)$_POST['end_operator_id'];
        $endTime = $_POST['end_time'];
        $shiftCount = (float)($_POST['shift_count'] ?? 0);

        if ($logsManager->stopLog($logId, $endOpId, $endTime, $shiftCount)) {
            header("Location: $redirectUrl&msg=stopped");
            exit;
        } else {
            $error = 'Error stopping run.';
        }
    }

    if (isset($_POST['delete'])) {
        if ($logsManager->deleteLog((int)$_POST['log_id'])) {
            header("Location: $redirectUrl&msg=deleted");
            exit;
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'started') $message = "Production run started.";
    if ($_GET['msg'] === 'stopped') $message = "Production run stopped.";
    if ($_GET['msg'] === 'deleted') $message = "Log deleted.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES Backoffice - Production Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Production Logs (Runs)</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Filter Runs</div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Order ID</label>
                        <input type="number" name="filter_order" class="form-control" value="<?= htmlspecialchars($filterOrder ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Machine</label>
                        <select class="form-select" name="filter_machine">
                            <option value="">All</option>
                            <?php foreach ($machines as $m): ?>
                                <option value="<?= $m['MachineID'] ?>" <?= $filterMachine == $m['MachineID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Operator</label>
                        <select class="form-select" name="filter_operator">
                            <option value="">All</option>
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
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#startRunModal">
                    <i class="fa-solid fa-play"></i> Start New Run
                </button>
                
                <div class="modal fade" id="startRunModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Start Production Run</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Production Order <span class="text-danger">*</span></label>
                                        <select class="form-select" name="order_id" required>
                                            <option value="">Select Order...</option>
                                            <?php foreach ($orders as $ord): ?>
                                                <option value="<?= $ord['OrderID'] ?>">#<?= $ord['OrderID'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Machine <span class="text-danger">*</span></label>
                                        <select class="form-select" name="machine_id" required>
                                            <?php foreach ($machines as $m): ?>
                                                <option value="<?= $m['MachineID'] ?>"><?= htmlspecialchars($m['Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Started By <span class="text-danger">*</span></label>
                                        <select class="form-select" name="start_operator_id" required>
                                            <?php foreach ($users as $u): ?>
                                                <option value="<?= $u['OperatorID'] ?>"><?= htmlspecialchars($u['OperatorUsername']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Start Time</label>
                                        <input type="datetime-local" class="form-control" name="start_time" value="<?= date('Y-m-d\TH:i') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="start_run" class="btn btn-success">Start Run</button>
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
                    <th>Order #</th>
                    <th>Article</th>
                    <th>Machine</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No production logs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['LogID'] ?></td>
                            <td><a href="#">#<?= $log['ProductionOrderID'] ?></a></td>
                            <td><?= htmlspecialchars($log['ArticleName'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($log['MachineName'] ?? '-') ?></td>
                            <td>
                                <div><?= date('d/m H:i', strtotime($log['StartTime'])) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($log['StartOperatorName']) ?></small>
                            </td>
                            <td>
                                <?php if ($log['EndTime']): ?>
                                    <div><?= date('d/m H:i', strtotime($log['EndTime'])) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($log['EndOperatorName']) ?></small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    if ($log['DurationMinutes']) {
                                        $h = floor($log['DurationMinutes'] / 60);
                                        $m = $log['DurationMinutes'] % 60;
                                        echo "{$h}h {$m}m";
                                    } else {
                                        echo '<span class="text-muted">Running...</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if ($log['Status'] === 'Active'): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <?php if ($log['Status'] === 'Active'): ?>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#stopRunModal<?= $log['LogID'] ?>">
                                            <i class="fa-solid fa-stop"></i> Stop
                                        </button>

                                        <div class="modal fade" id="stopRunModal<?= $log['LogID'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Stop Run #<?= $log['LogID'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="log_id" value="<?= $log['LogID'] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Stopped By</label>
                                                                <select class="form-select" name="end_operator_id" required>
                                                                    <?php foreach ($users as $u): ?>
                                                                        <option value="<?= $u['OperatorID'] ?>"><?= htmlspecialchars($u['OperatorUsername']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">End Time</label>
                                                                <input type="datetime-local" class="form-control" name="end_time" value="<?= date('Y-m-d\TH:i') ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Shift Count (Estimate)</label>
                                                                <input type="number" step="0.5" class="form-control" name="shift_count" value="0">
                                                                <small class="text-muted">e.g. 1.0 for full shift, 0.5 for half</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" name="stop_run" class="btn btn-danger">Confirm Stop</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this log?');">
                                            <input type="hidden" name="log_id" value="<?= $log['LogID'] ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>