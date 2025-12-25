<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'RawMaterialManager.php';
require_once INCLUDE_PATH . 'UserManager.php';
require_once INCLUDE_PATH . 'MachineManager.php';
// Assuming you have these, otherwise query DB directly
require_once INCLUDE_PATH . 'ArticleManager.php'; 

$isAdmin = isAdmin();
$rmManager = new RawMaterialManager($pdo);
$userManager = new UserManager($pdo);
$machineManager = new MachineManager($pdo);
$articleManager = new ArticleManager($pdo); // Required for article dropdown

// 1. Capture Filter Inputs
$filterOrder = isset($_GET['filter_order']) && $_GET['filter_order'] !== '' ? (int)$_GET['filter_order'] : null;
$filterBatch = $_GET['filter_batch'] ?? null;
$filterStartDate = $_GET['filter_start_date'] ?? null;
$filterEndDate = $_GET['filter_end_date'] ?? null;

// 2. Fetch Data
$logs = $rmManager->listLogs($filterOrder, $filterBatch, $filterStartDate, $filterEndDate);
$users = $userManager->listUsers();
$machines = $machineManager->listMachines();
$articles = $articleManager->listArticles(); // Assuming this method exists

// Fetch Order IDs for dropdown (Simple query if no Manager exists)
$ordersStmt = $pdo->query("SELECT OrderID FROM production_order ORDER BY OrderID DESC LIMIT 100");
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

// --- HANDLE POST REQUESTS ---
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREATE
    if (isset($_POST['create'])) {
        $orderId = (int)$_POST['order_id'];
        $operatorId = (int)$_POST['operator_id'];
        $batchCode = trim($_POST['batch_code']);
        $articleId = !empty($_POST['article_id']) ? (int)$_POST['article_id'] : null;
        $machineId = !empty($_POST['machine_id']) ? (int)$_POST['machine_id'] : null;
        $quantity = (float)$_POST['quantity'];
        $scanTime = $_POST['scan_time'];
        $notes = trim($_POST['notes']);

        if ($rmManager->createLog($orderId, $operatorId, $batchCode, $articleId, $machineId, $quantity, $scanTime, $notes)) {
            $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET) . '&msg=created';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $error = "Error creating log.";
        }
    }

    // EDIT
    if (isset($_POST['edit'])) {
        $logId = (int)$_POST['log_id'];
        $orderId = (int)$_POST['edit_order_id'];
        $operatorId = (int)$_POST['edit_operator_id'];
        $batchCode = trim($_POST['edit_batch_code']);
        $articleId = !empty($_POST['edit_article_id']) ? (int)$_POST['edit_article_id'] : null;
        $machineId = !empty($_POST['edit_machine_id']) ? (int)$_POST['edit_machine_id'] : null;
        $quantity = (float)$_POST['edit_quantity'];
        $scanTime = $_POST['edit_scan_time'];
        $notes = trim($_POST['edit_notes']);

        if ($rmManager->updateLog($logId, $orderId, $operatorId, $batchCode, $articleId, $machineId, $quantity, $scanTime, $notes)) {
            $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET) . '&msg=updated';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $error = "Error updating log.";
        }
    }

    // DELETE
    if (isset($_POST['delete'])) {
        $logId = (int)$_POST['log_id'];
        if ($rmManager->deleteLog($logId)) {
            $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET) . '&msg=deleted';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $error = "Error deleting log.";
        }
    }
}

// Handle Messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Entry created successfully.";
    if ($_GET['msg'] === 'updated') $message = "Entry updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Entry deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Raw Materials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Raw Material Logs</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <i class="fa-solid fa-filter me-1"></i> Filter Scans
            </div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Batch Code</label>
                        <input type="text" class="form-control" name="filter_batch" value="<?= htmlspecialchars($filterBatch ?? '') ?>" placeholder="Search Batch...">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Order ID</label>
                        <input type="number" class="form-control" name="filter_order" value="<?= htmlspecialchars($filterOrder ?? '') ?>" placeholder="Order #">
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
                        <a href="raw-materials.php" class="btn btn-outline-secondary w-100 btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fa-solid fa-plus"></i> Manual Scan Entry
                </button>

                <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Manual Scan Entry</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Production Order <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="order_id" required placeholder="Enter Order ID">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Batch Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="batch_code" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Operator <span class="text-danger">*</span></label>
                                            <select class="form-select" name="operator_id" required>
                                                <?php foreach ($users as $u): ?>
                                                    <option value="<?= $u['OperatorID'] ?>"><?= htmlspecialchars($u['OperatorUsername']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Machine</label>
                                            <select class="form-select" name="machine_id">
                                                <option value="">None</option>
                                                <?php foreach ($machines as $m): ?>
                                                    <option value="<?= $m['MachineID'] ?>"><?= htmlspecialchars($m['Name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Article / Material Type</label>
                                        <select class="form-select" name="article_id">
                                            <option value="">Unknown / Generic</option>
                                            <?php foreach ($articles as $a): ?>
                                                <option value="<?= $a['ArticleID'] ?>"><?= htmlspecialchars($a['Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" step="0.01" class="form-control" name="quantity" value="1.00">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Scan Time</label>
                                            <input type="datetime-local" class="form-control" name="scan_time" value="<?= date('Y-m-d\TH:i') ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="create" class="btn btn-primary">Save Entry</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Scan Time</th>
                        <th>Batch Code</th>
                        <th>Order #</th>
                        <th>Article</th>
                        <th>Qty</th>
                        <th>Operator</th>
                        <th>Machine</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="9" class="text-center py-4">No scan logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['LogID']) ?></td>
                                <td><?= htmlspecialchars($log['ScanTime']) ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($log['BatchCode']) ?></td>
                                <td><?= htmlspecialchars($log['ProductionOrderID']) ?></td>
                                <td><?= htmlspecialchars($log['ArticleName'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($log['Quantity']) ?></td>
                                <td><?= htmlspecialchars($log['OperatorUsername'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($log['MachineName'] ?? '-') ?></td>
                                <td>
                                    <?php if ($isAdmin): ?>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $log['LogID'] ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this scan?');">
                                            <input type="hidden" name="log_id" value="<?= $log['LogID'] ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>

                                        <div class="modal fade" id="editModal<?= $log['LogID'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Scan #<?= $log['LogID'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="log_id" value="<?= $log['LogID'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Order ID</label>
                                                                <input type="number" class="form-control" name="edit_order_id" value="<?= htmlspecialchars($log['ProductionOrderID']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Batch Code</label>
                                                                <input type="text" class="form-control" name="edit_batch_code" value="<?= htmlspecialchars($log['BatchCode']) ?>" required>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Operator</label>
                                                                    <select class="form-select" name="edit_operator_id" required>
                                                                        <?php foreach ($users as $u): ?>
                                                                            <option value="<?= $u['OperatorID'] ?>" <?= $u['OperatorID'] == $log['OperatorID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($u['OperatorUsername']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Machine</label>
                                                                    <select class="form-select" name="edit_machine_id">
                                                                        <option value="">None</option>
                                                                        <?php foreach ($machines as $m): ?>
                                                                            <option value="<?= $m['MachineID'] ?>" <?= $m['MachineID'] == $log['MachineID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($m['Name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Article</label>
                                                                <select class="form-select" name="edit_article_id">
                                                                    <option value="">Unknown</option>
                                                                    <?php foreach ($articles as $a): ?>
                                                                        <option value="<?= $a['ArticleID'] ?>" <?= $a['ArticleID'] == $log['ArticleID'] ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($a['Name']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Quantity</label>
                                                                    <input type="number" step="0.01" class="form-control" name="edit_quantity" value="<?= htmlspecialchars($log['Quantity']) ?>">
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Scan Time</label>
                                                                    <input type="datetime-local" class="form-control" name="edit_scan_time" value="<?= date('Y-m-d\TH:i', strtotime($log['ScanTime'])) ?>">
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
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>