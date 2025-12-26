<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'MachineStopManager.php';
require_once INCLUDE_PATH . 'UserManager.php';
require_once INCLUDE_PATH . 'MachineManager.php';

$isAdmin = isAdmin();
$stopManager = new MachineStopManager($pdo);
$userManager = new UserManager($pdo);
$machineManager = new MachineManager($pdo);

$filterMachine = isset($_GET['filter_machine']) && $_GET['filter_machine'] !== '' ? (int)$_GET['filter_machine'] : null;
$filterOperator = isset($_GET['filter_operator']) && $_GET['filter_operator'] !== '' ? (int)$_GET['filter_operator'] : null;
$filterCategory = isset($_GET['filter_category']) && $_GET['filter_category'] !== '' ? (int)$_GET['filter_category'] : null;
$filterReason = isset($_GET['filter_reason']) && $_GET['filter_reason'] !== '' ? (int)$_GET['filter_reason'] : null;
$filterStartDate = $_GET['filter_start_date'] ?? null;
$filterEndDate = $_GET['filter_end_date'] ?? null;

$stops = $stopManager->listStops($filterMachine, $filterOperator, $filterCategory, $filterReason, $filterStartDate, $filterEndDate);
$users = $userManager->listUsers();
$machines = $machineManager->listMachines();
$categories = $stopManager->getCategories();
$reasons = $stopManager->getReasons();

$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    if (isset($_POST['create'])) {
        if ($stopManager->startStop(
            (int)$_POST['machine_id'],
            (int)$_POST['operator_id'],
            !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null,
            $_POST['start_time'],
            trim($_POST['notes'])
        )) {
            header("Location: $redirectUrl&msg=created");
            exit;
        } else {
            $error = 'Error logging stop.';
        }
    }

    if (isset($_POST['close_stop'])) {
        if ($stopManager->endStop(
            (int)$_POST['stop_id'],
            (int)$_POST['category_id'],
            (int)$_POST['reason_id'],
            $_POST['end_time'],
            trim($_POST['notes'])
        )) {
            header("Location: $redirectUrl&msg=closed");
            exit;
        } else {
            $error = 'Error closing stop.';
        }
    }

    if (isset($_POST['edit'])) {
        if ($stopManager->updateStop(
            (int)$_POST['stop_id'],
            (int)$_POST['edit_machine_id'],
            (int)$_POST['edit_operator_id'],
            !empty($_POST['edit_order_id']) ? (int)$_POST['edit_order_id'] : null,
            !empty($_POST['edit_category_id']) ? (int)$_POST['edit_category_id'] : null,
            !empty($_POST['edit_reason_id']) ? (int)$_POST['edit_reason_id'] : null,
            $_POST['edit_start_time'],
            $_POST['edit_end_time'],
            trim($_POST['edit_notes'])
        )) {
            header("Location: $redirectUrl&msg=updated");
            exit;
        } else {
            $error = 'Error updating log.';
        }
    }

    if (isset($_POST['delete'])) {
        if ($stopManager->deleteStop((int)$_POST['stop_id'])) {
            header("Location: $redirectUrl&msg=deleted");
            exit;
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Stop logged successfully.";
    if ($_GET['msg'] === 'closed') $message = "Stop closed and categorized.";
    if ($_GET['msg'] === 'updated') $message = "Log updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Log deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES Backoffice - Machine Stops</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Machine Stops (>3min)</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Filter Downtime</div>
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
                        <label class="form-label">Category</label>
                        <select class="form-select" name="filter_category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['CategoryID'] ?>" <?= $filterCategory == $c['CategoryID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['CategoryName']) ?>
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
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#startStopModal">
                    <i class="fa-solid fa-stopwatch"></i> Log New Stop
                </button>

                <div class="modal fade" id="startStopModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Log Machine Stop</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Machine *</label>
                                        <select class="form-select" name="machine_id" required>
                                            <?php foreach ($machines as $m): ?>
                                                <option value="<?= $m['MachineID'] ?>"><?= htmlspecialchars($m['Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Reported By *</label>
                                        <select class="form-select" name="operator_id" required>
                                            <?php foreach ($users as $u): ?>
                                                <option value="<?= $u['OperatorID'] ?>"><?= htmlspecialchars($u['OperatorUsername']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Order ID (Optional)</label>
                                        <input type="number" class="form-control" name="order_id" placeholder="Associated Production Order">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Start Time *</label>
                                        <input type="datetime-local" class="form-control" name="start_time" value="<?= date('Y-m-d\TH:i') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Initial Notes</label>
                                        <textarea class="form-control" name="notes"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="create" class="btn btn-danger">Start Timer</button>
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
                    <th>Machine</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Duration</th>
                    <th>Reason</th>
                    <th>Operator</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stops)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No stops found.</td></tr>
                <?php else: ?>
                    <?php foreach ($stops as $row): ?>
                        <tr>
                            <td><?= $row['StopID'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['MachineName'] ?? 'Unknown') ?></td>
                            <td><?= date('d/m H:i', strtotime($row['StartTime'])) ?></td>
                            <td>
                                <?php if ($row['EndTime']): ?>
                                    <?= date('d/m H:i', strtotime($row['EndTime'])) ?>
                                <?php else: ?>
                                    <span class="badge text-bg-danger fa-fade">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    if ($row['DurationMinutes']) {
                                        echo number_format($row['DurationMinutes'], 0) . ' min';
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if ($row['CategoryName']): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($row['CategoryName']) ?></span>
                                    <div class="small text-muted"><?= htmlspecialchars($row['ReasonName'] ?? '') ?></div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Uncategorized</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['OperatorUsername'] ?? '') ?></td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <?php if (empty($row['EndTime'])): ?>
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#closeStopModal<?= $row['StopID'] ?>">
                                            <i class="fa-solid fa-check"></i> Close
                                        </button>

                                        <div class="modal fade" id="closeStopModal<?= $row['StopID'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Close Stop #<?= $row['StopID'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="stop_id" value="<?= $row['StopID'] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">End Time</label>
                                                                <input type="datetime-local" class="form-control" name="end_time" value="<?= date('Y-m-d\TH:i') ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Category *</label>
                                                                <select class="form-select" name="category_id" required>
                                                                    <option value="">Select...</option>
                                                                    <?php foreach ($categories as $c): ?>
                                                                        <option value="<?= $c['CategoryID'] ?>"><?= htmlspecialchars($c['CategoryName']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Reason *</label>
                                                                <select class="form-select" name="reason_id" required>
                                                                    <option value="">Select...</option>
                                                                    <?php foreach ($reasons as $r): ?>
                                                                        <option value="<?= $r['ReasonID'] ?>"><?= htmlspecialchars($r['ReasonName']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Notes</label>
                                                                <textarea class="form-control" name="notes"><?= htmlspecialchars($row['Notes']) ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" name="close_stop" class="btn btn-success">Save & Close</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editStopModal<?= $row['StopID'] ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this stop?');">
                                            <input type="hidden" name="stop_id" value="<?= $row['StopID'] ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>

                                        <div class="modal fade" id="editStopModal<?= $row['StopID'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Stop #<?= $row['StopID'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="stop_id" value="<?= $row['StopID'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Category</label>
                                                                <select class="form-select" name="edit_category_id">
                                                                    <option value="">Uncategorized</option>
                                                                    <?php foreach ($categories as $c): ?>
                                                                        <option value="<?= $c['CategoryID'] ?>" <?= $c['CategoryID'] == $row['CategoryID'] ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($c['CategoryName']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Reason</label>
                                                                <select class="form-select" name="edit_reason_id">
                                                                    <option value="">Unspecified</option>
                                                                    <?php foreach ($reasons as $r): ?>
                                                                        <option value="<?= $r['ReasonID'] ?>" <?= $r['ReasonID'] == $row['ReasonID'] ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($r['ReasonName']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6 mb-3">
                                                                    <label class="form-label">Start</label>
                                                                    <input type="datetime-local" class="form-control" name="edit_start_time" value="<?= date('Y-m-d\TH:i', strtotime($row['StartTime'])) ?>" required>
                                                                </div>
                                                                <div class="col-6 mb-3">
                                                                    <label class="form-label">End</label>
                                                                    <input type="datetime-local" class="form-control" name="edit_end_time" value="<?= $row['EndTime'] ? date('Y-m-d\TH:i', strtotime($row['EndTime'])) : '' ?>">
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Machine</label>
                                                                <select class="form-select" name="edit_machine_id" required>
                                                                    <?php foreach ($machines as $m): ?>
                                                                        <option value="<?= $m['MachineID'] ?>" <?= $m['MachineID'] == $row['MachineID'] ? 'selected' : '' ?>><?= htmlspecialchars($m['Name']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Operator</label>
                                                                <select class="form-select" name="edit_operator_id" required>
                                                                    <?php foreach ($users as $u): ?>
                                                                        <option value="<?= $u['OperatorID'] ?>" <?= $u['OperatorID'] == $row['OperatorID'] ? 'selected' : '' ?>><?= htmlspecialchars($u['OperatorUsername']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Notes</label>
                                                                <textarea class="form-control" name="edit_notes"><?= htmlspecialchars($row['Notes']) ?></textarea>
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