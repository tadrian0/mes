<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'RejectManager.php';
require_once INCLUDE_PATH . 'UserManager.php';
require_once INCLUDE_PATH . 'MachineManager.php';
require_once INCLUDE_PATH . 'ArticleManager.php';

$isAdmin = isAdmin();
$rejectManager = new RejectManager($pdo);
$userManager = new UserManager($pdo);
$machineManager = new MachineManager($pdo);
$articleManager = new ArticleManager($pdo);

$filterOrder = isset($_GET['filter_order']) && $_GET['filter_order'] !== '' ? (int)$_GET['filter_order'] : null;
$filterArticle = isset($_GET['filter_article']) && $_GET['filter_article'] !== '' ? (int)$_GET['filter_article'] : null;
$filterOperator = isset($_GET['filter_operator']) && $_GET['filter_operator'] !== '' ? (int)$_GET['filter_operator'] : null;
$filterCategory = isset($_GET['filter_category']) && $_GET['filter_category'] !== '' ? (int)$_GET['filter_category'] : null;
$filterReason = isset($_GET['filter_reason']) && $_GET['filter_reason'] !== '' ? (int)$_GET['filter_reason'] : null;
$filterStartDate = $_GET['filter_start_date'] ?? null;
$filterEndDate = $_GET['filter_end_date'] ?? null;

$rejects = $rejectManager->listRejects($filterOrder, $filterArticle, $filterOperator, $filterCategory, $filterReason, $filterStartDate, $filterEndDate);

$users = $userManager->listUsers();
$machines = $machineManager->listMachines();
$articles = $articleManager->listArticles();
$categories = $rejectManager->getCategories();
$reasons = $rejectManager->getReasons();

$ordersStmt = $pdo->query("SELECT OrderID FROM production_order ORDER BY OrderID DESC LIMIT 100");
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    if (isset($_POST['create'])) {
        if ($rejectManager->createReject(
            (int)$_POST['order_id'],
            (int)$_POST['article_id'],
            (int)$_POST['operator_id'],
            (int)$_POST['machine_id'],
            (int)$_POST['category_id'],
            (int)$_POST['reason_id'],
            (int)$_POST['quantity'],
            $_POST['reject_date'],
            trim($_POST['notes'])
        )) {
            header("Location: $redirectUrl&msg=created");
            exit;
        } else {
            $error = 'Error adding reject.';
        }
    }

    if (isset($_POST['edit'])) {
        if ($rejectManager->updateReject(
            (int)$_POST['reject_id'],
            (int)$_POST['edit_order_id'],
            (int)$_POST['edit_article_id'],
            (int)$_POST['edit_operator_id'],
            (int)$_POST['edit_machine_id'],
            (int)$_POST['edit_category_id'],
            (int)$_POST['edit_reason_id'],
            (int)$_POST['edit_quantity'],
            $_POST['edit_reject_date'],
            trim($_POST['edit_notes'])
        )) {
            header("Location: $redirectUrl&msg=updated");
            exit;
        } else {
            $error = 'Error updating reject.';
        }
    }

    if (isset($_POST['delete'])) {
        if ($rejectManager->deleteReject((int)$_POST['reject_id'])) {
            header("Location: $redirectUrl&msg=deleted");
            exit;
        } else {
            $error = 'Error deleting reject.';
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Reject recorded successfully.";
    if ($_GET['msg'] === 'updated') $message = "Reject updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Reject deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Rejects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Rejects Management</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <i class="fa-solid fa-filter me-1"></i> Filter Rejects
            </div>
            <div class="card-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Order ID</label>
                        <input type="number" name="filter_order" class="form-control" value="<?= htmlspecialchars($filterOrder ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Article</label>
                        <select class="form-select" name="filter_article">
                            <option value="">All Articles</option>
                            <?php foreach ($articles as $a): ?>
                                <option value="<?= $a['ArticleID'] ?>" <?= $filterArticle == $a['ArticleID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['Name']) ?>
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
                        <label class="form-label">Reason</label>
                        <select class="form-select" name="filter_reason">
                            <option value="">All Reasons</option>
                            <?php foreach ($reasons as $r): ?>
                                <option value="<?= $r['ReasonID'] ?>" <?= $filterReason == $r['ReasonID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['ReasonName']) ?>
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
                    <div class="col-md-2 offset-md-6">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-3">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addRejectModal">
                    <i class="fa-solid fa-triangle-exclamation"></i> Report Reject
                </button>

                <div class="modal fade" id="addRejectModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Report New Reject</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Order ID *</label>
                                            <select class="form-select" name="order_id" required>
                                                <?php foreach ($orders as $ord): ?>
                                                    <option value="<?= $ord['OrderID'] ?>">#<?= $ord['OrderID'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Article *</label>
                                            <select class="form-select" name="article_id" required>
                                                <?php foreach ($articles as $art): ?>
                                                    <option value="<?= $art['ArticleID'] ?>"><?= htmlspecialchars($art['Name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Category *</label>
                                            <select class="form-select" name="category_id" required>
                                                <?php foreach ($categories as $c): ?>
                                                    <option value="<?= $c['CategoryID'] ?>"><?= htmlspecialchars($c['CategoryName']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Reason *</label>
                                            <select class="form-select" name="reason_id" required>
                                                <?php foreach ($reasons as $r): ?>
                                                    <option value="<?= $r['ReasonID'] ?>"><?= htmlspecialchars($r['ReasonName']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Quantity *</label>
                                            <input type="number" class="form-control" name="quantity" required min="1">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Operator *</label>
                                            <select class="form-select" name="operator_id" required>
                                                <?php foreach ($users as $u): ?>
                                                    <option value="<?= $u['OperatorID'] ?>"><?= htmlspecialchars($u['OperatorUsername']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Machine *</label>
                                            <select class="form-select" name="machine_id" required>
                                                <?php foreach ($machines as $m): ?>
                                                    <option value="<?= $m['MachineID'] ?>"><?= htmlspecialchars($m['Name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="datetime-local" class="form-control" name="reject_date" value="<?= date('Y-m-d\TH:i') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" placeholder="Additional details..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="create" class="btn btn-danger">Save Reject</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Order</th>
                        <th>Article</th>
                        <th>Category</th>
                        <th>Reason</th>
                        <th>Qty</th>
                        <th>Operator</th>
                        <th>Machine</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rejects)): ?>
                        <tr><td colspan="11" class="text-center py-4 text-muted">No rejects found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rejects as $row): ?>
                            <tr>
                                <td><?= $row['RejectID'] ?></td>
                                <td><?= date('d/m H:i', strtotime($row['RejectDate'])) ?></td>
                                <td>#<?= $row['OrderID'] ?></td>
                                <td><?= htmlspecialchars($row['ArticleName'] ?? 'N/A') ?></td>
                                <td><span class="badge text-bg-warning"><?= htmlspecialchars($row['CategoryName'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($row['ReasonName'] ?? '') ?></td>
                                <td class="fw-bold text-danger"><?= $row['Quantity'] ?></td>
                                <td><?= htmlspecialchars($row['OperatorUsername'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['MachineName'] ?? '') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($row['Notes']) ?></small></td>
                                <td>
                                    <?php if ($isAdmin): ?>
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editRejectModal<?= $row['RejectID'] ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this record?');">
                                            <input type="hidden" name="reject_id" value="<?= $row['RejectID'] ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>

                                        <div class="modal fade" id="editRejectModal<?= $row['RejectID'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Reject #<?= $row['RejectID'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="post" action="">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="reject_id" value="<?= $row['RejectID'] ?>">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Order ID</label>
                                                                    <input type="number" class="form-control" name="edit_order_id" value="<?= $row['OrderID'] ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Article</label>
                                                                    <select class="form-select" name="edit_article_id" required>
                                                                        <?php foreach ($articles as $art): ?>
                                                                            <option value="<?= $art['ArticleID'] ?>" <?= $art['ArticleID'] == $row['ArticleID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($art['Name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Category</label>
                                                                    <select class="form-select" name="edit_category_id" required>
                                                                        <?php foreach ($categories as $c): ?>
                                                                            <option value="<?= $c['CategoryID'] ?>" <?= $c['CategoryID'] == $row['CategoryID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($c['CategoryName']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Reason</label>
                                                                    <select class="form-select" name="edit_reason_id" required>
                                                                        <?php foreach ($reasons as $r): ?>
                                                                            <option value="<?= $r['ReasonID'] ?>" <?= $r['ReasonID'] == $row['ReasonID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($r['ReasonName']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Quantity</label>
                                                                    <input type="number" class="form-control" name="edit_quantity" value="<?= $row['Quantity'] ?>" required>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Operator</label>
                                                                    <select class="form-select" name="edit_operator_id" required>
                                                                        <?php foreach ($users as $u): ?>
                                                                            <option value="<?= $u['OperatorID'] ?>" <?= $u['OperatorID'] == $row['OperatorID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($u['OperatorUsername']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Machine</label>
                                                                    <select class="form-select" name="edit_machine_id" required>
                                                                        <?php foreach ($machines as $m): ?>
                                                                            <option value="<?= $m['MachineID'] ?>" <?= $m['MachineID'] == $row['MachineID'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($m['Name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Date</label>
                                                                <input type="datetime-local" class="form-control" name="edit_reject_date" value="<?= date('Y-m-d\TH:i', strtotime($row['RejectDate'])) ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Notes</label>
                                                                <textarea class="form-control" name="edit_notes"><?= htmlspecialchars($row['Notes']) ?></textarea>
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