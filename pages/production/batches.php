<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'BatchManager.php';
require_once INCLUDE_PATH . 'UserManager.php';
require_once INCLUDE_PATH . 'MachineManager.php';
require_once INCLUDE_PATH . 'ArticleManager.php'; 

$isAdmin = isAdmin();
$batchManager = new BatchManager($pdo);
$userManager = new UserManager($pdo);
$machineManager = new MachineManager($pdo);
$articleManager = new ArticleManager($pdo);

// 1. Capture Filters
$filterMachine = isset($_GET['filter_machine']) && $_GET['filter_machine'] !== '' ? (int)$_GET['filter_machine'] : null;
$filterOperator = isset($_GET['filter_operator']) && $_GET['filter_operator'] !== '' ? (int)$_GET['filter_operator'] : null;
$filterStartDate = $_GET['filter_start_date'] ?? null;
$filterEndDate = $_GET['filter_end_date'] ?? null;
$searchBatch = isset($_GET['search_batch']) ? trim($_GET['search_batch']) : null;

// 2. Fetch Data
$batches = $batchManager->listBatches($filterMachine, $filterOperator, $filterStartDate, $filterEndDate, $searchBatch);
$users = $userManager->listUsers();
$machines = $machineManager->listMachines();
$articles = $articleManager->listArticles(); 

$ordersStmt = $pdo->query("SELECT OrderID FROM production_order ORDER BY OrderID DESC LIMIT 100");
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

// --- HANDLE POST REQUESTS ---
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Helper to build redirect URL keeping filters
    $queryParams = $_GET;
    
    // CREATE
    if (isset($_POST['create'])) {
        $batchCode = trim($_POST['batch_code']);
        $batchType = $_POST['batch_type'];
        $orderId = (int)$_POST['order_id'];
        $articleId = (int)$_POST['article_id'];
        $operatorId = (int)$_POST['operator_id'];
        $machineId = (int)$_POST['machine_id'];
        $quantity = (float)$_POST['quantity'];
        $printTime = $_POST['print_time'];
        $notes = trim($_POST['notes']);

        if ($batchManager->createBatch($batchCode, $batchType, $orderId, $articleId, $operatorId, $machineId, $quantity, $printTime, $notes)) {
            $queryParams['msg'] = 'created';
            header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($queryParams));
            exit;
        } else {
            $error = 'Error creating batch (Code might already exist).';
        }
    }

    // EDIT
    if (isset($_POST['edit'])) {
        $batchId = (int)$_POST['batch_id'];
        $batchCode = trim($_POST['edit_batch_code']);
        $batchType = $_POST['edit_batch_type'];
        $orderId = (int)$_POST['edit_order_id'];
        $articleId = (int)$_POST['edit_article_id'];
        $operatorId = (int)$_POST['edit_operator_id'];
        $machineId = (int)$_POST['edit_machine_id'];
        $quantity = (float)$_POST['edit_quantity'];
        $printTime = $_POST['edit_print_time'];
        $notes = trim($_POST['edit_notes']);

        if ($batchManager->updateBatch($batchId, $batchCode, $batchType, $orderId, $articleId, $operatorId, $machineId, $quantity, $printTime, $notes)) {
            $queryParams['msg'] = 'updated';
            header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($queryParams));
            exit;
        } else {
            $error = 'Error updating batch.';
        }
    }

    // DELETE
    if (isset($_POST['delete'])) {
        $batchId = (int)$_POST['batch_id'];
        if ($batchManager->deleteBatch($batchId)) {
            $queryParams['msg'] = 'deleted';
            header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($queryParams));
            exit;
        } else {
            $error = 'Error deleting batch.';
        }
    }
}

// Display Messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Batch created successfully.";
    if ($_GET['msg'] === 'updated') $message = "Batch updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Batch deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Batches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Batches / Labels</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <i class="fa-solid fa-filter me-1"></i> Filter & Search
            </div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    
                    <div class="col-md-3">
                        <label class="form-label">Search Batch Code</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-barcode"></i></span>
                            <input type="text" class="form-control" name="search_batch" value="<?= htmlspecialchars($searchBatch ?? '') ?>" placeholder="e.g. P001...">
                        </div>
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
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <label class="form-label">To</label>
                                <input type="date" class="form-control" name="filter_end_date" value="<?= htmlspecialchars($filterEndDate ?? '') ?>">
                            </div>
                            <div class="d-flex align-items-end">
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i></button>
                                <a href="batches.php" class="btn btn-outline-secondary ms-1" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                    <i class="fa-solid fa-print"></i> Add / Generate Batch
                </button>

                <div class="modal fade" id="addBatchModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Batch Label</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Batch Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="batch_code" required placeholder="Unique ID">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Type</label>
                                            <select class="form-select" name="batch_type" required>
                                                <option value="Finished Product">Finished Product</option>
                                                <option value="Partial Product">Partial Product</option>
                                                <option value="Raw Material Remnant">Raw Material Remnant</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Order ID <span class="text-danger">*</span></label>
                                            <select class="form-select" name="order_id" required>
                                                <option value="">Select Order...</option>
                                                <?php foreach ($orders as $ord): ?>
                                                    <option value="<?= $ord['OrderID'] ?>">#<?= $ord['OrderID'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Article <span class="text-danger">*</span></label>
                                            <select class="form-select" name="article_id" required>
                                                <?php foreach ($articles as $art): ?>
                                                    <option value="<?= $art['ArticleID'] ?>"><?= htmlspecialchars($art['Name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Operator</label>
                                            <select class="form-select" name="operator_id" required>
                                                <?php foreach ($users as $u): ?>
                                                    <option value="<?= $u['OperatorID'] ?>"><?= htmlspecialchars($u['OperatorUsername']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Machine</label>
                                            <select class="form-select" name="machine_id" required>
                                                <?php foreach ($machines as $m): ?>
                                                    <option value="<?= $m['MachineID'] ?>"><?= htmlspecialchars($m['Name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" step="0.01" class="form-control" name="quantity" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Print Time</label>
                                            <input type="datetime-local" class="form-control" name="print_time" value="<?= date('Y-m-d\TH:i') ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="create" class="btn btn-primary">Save Batch</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Type</th>
                        <th>Article</th>
                        <th>Qty</th>
                        <th>Order</th>
                        <th>Operator</th>
                        <th>Machine</th>
                        <th>Printed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($batches)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">No batches found matching criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach ($batches as $batch): ?>
                            <tr>
                                <td class="fw-bold font-monospace"><?= htmlspecialchars($batch['BatchCode']) ?></td>
                                <td>
                                    <?php 
                                    $badgeClass = match($batch['BatchType']) {
                                        'Finished Product' => 'text-bg-success',
                                        'Partial Product' => 'text-bg-warning',
                                        'Raw Material Remnant' => 'text-bg-info',
                                        default => 'text-bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($batch['BatchType']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($batch['ArticleName'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($batch['Quantity']) ?></td>
                                <td><a href="#" class="text-decoration-none">#<?= htmlspecialchars($batch['ProductionOrderID']) ?></a></td>
                                <td><?= htmlspecialchars($batch['OperatorUsername'] ?? '?') ?></td>
                                <td><?= htmlspecialchars($batch['MachineName'] ?? '?') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($batch['PrintTime'])) ?></td>
                                <td>
                                    <?php if ($isAdmin): ?>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editBatchModal<?= $batch['BatchID'] ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this batch?');">
                                            <input type="hidden" name="batch_id" value="<?= $batch['BatchID'] ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>

                                        <div class="modal fade" id="editBatchModal<?= $batch['BatchID'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Batch #<?= $batch['BatchID'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="batch_id" value="<?= $batch['BatchID'] ?>">
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Batch Code</label>
                                                                    <input type="text" class="form-control" name="edit_batch_code" value="<?= htmlspecialchars($batch['BatchCode']) ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Type</label>
                                                                    <select class="form-select" name="edit_batch_type" required>
                                                                        <option value="Finished Product" <?= $batch['BatchType'] == 'Finished Product' ? 'selected' : '' ?>>Finished Product</option>
                                                                        <option value="Partial Product" <?= $batch['BatchType'] == 'Partial Product' ? 'selected' : '' ?>>Partial Product</option>
                                                                        <option value="Raw Material Remnant" <?= $batch['BatchType'] == 'Raw Material Remnant' ? 'selected' : '' ?>>Raw Material Remnant</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Order ID</label>
                                                                    <input type="number" class="form-control" name="edit_order_id" value="<?= $batch['ProductionOrderID'] ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Article</label>
                                                                    <select class="form-select" name="edit_article_id" required>
                                                                        <?php foreach ($articles as $art): ?>
                                                                            <option value="<?= $art['ArticleID'] ?>" <?= $art['ArticleID'] == $batch['ArticleID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($art['Name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Operator</label>
                                                                    <select class="form-select" name="edit_operator_id" required>
                                                                        <?php foreach ($users as $u): ?>
                                                                            <option value="<?= $u['OperatorID'] ?>" <?= $u['OperatorID'] == $batch['OperatorID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($u['OperatorUsername']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Machine</label>
                                                                    <select class="form-select" name="edit_machine_id" required>
                                                                        <?php foreach ($machines as $m): ?>
                                                                            <option value="<?= $m['MachineID'] ?>" <?= $m['MachineID'] == $batch['MachineID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($m['Name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Quantity</label>
                                                                    <input type="number" step="0.01" class="form-control" name="edit_quantity" value="<?= $batch['Quantity'] ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Print Time</label>
                                                                    <input type="datetime-local" class="form-control" name="edit_print_time" value="<?= date('Y-m-d\TH:i', strtotime($batch['PrintTime'])) ?>">
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Notes</label>
                                                                <textarea class="form-control" name="edit_notes"><?= htmlspecialchars($batch['Notes']) ?></textarea>
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