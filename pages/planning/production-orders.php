<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'ProductionOrderManager.php';
require_once INCLUDE_PATH . 'ArticleManager.php'; 

$isAdmin = isAdmin();
$orderManager = new ProductionOrderManager($pdo);
$articleManager = new ArticleManager($pdo);

$search = $_GET['search'] ?? null;
$filterArticle = isset($_GET['article_id']) && $_GET['article_id'] !== '' ? (int)$_GET['article_id'] : null;
$filterStatus = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
$filterStart = $_GET['start_date'] ?? null;
$filterEnd = $_GET['end_date'] ?? null;
$showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == '1';

$orders = $orderManager->listOrders($showDeleted, $search, $filterArticle, $filterStatus, $filterStart, $filterEnd);
$articles = $articleManager->listArticles();

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if ($orderManager->softDeleteOrder((int)$_POST['order_id'], $_SESSION['user_id'])) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=deleted");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - Production Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Production Orders</h1>
            <?php if ($isAdmin): ?>
                <a href="production-orders-form.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> New Order
                </a>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">Order moved to trash. <button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Search & Filter</div>
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="ID or Article Name..." value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Article</label>
                        <select name="article_id" class="form-select form-select-sm">
                            <option value="">All Articles</option>
                            <?php foreach ($articles as $art): ?>
                                <option value="<?= $art['ArticleID'] ?>" <?= $filterArticle == $art['ArticleID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($art['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="Planned" <?= $filterStatus == 'Planned' ? 'selected' : '' ?>>Planned</option>
                            <option value="Active" <?= $filterStatus == 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Closed" <?= $filterStatus == 'Closed' ? 'selected' : '' ?>>Closed</option>
                            <option value="Cancelled" <?= $filterStatus == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $filterStart ?>">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_deleted" value="1" id="chkDeleted" <?= $showDeleted ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="chkDeleted">Show Deleted</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-1">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Article</th>
                            <th>Target Qty</th>
                            <th>Routing (Machine)</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No orders found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $po): ?>
                                <tr class="<?= $po['IsDeleted'] ? 'table-danger' : '' ?>">
                                    <td><strong><?= $po['OrderID'] ?></strong></td>
                                    <td><?= htmlspecialchars($po['ArticleName'] ?? 'Unknown') ?></td>
                                    <td><?= number_format($po['TargetQuantity'], 0) ?></td>
                                    <td>
                                        <?php if ($po['RecipeVersion']): ?>
                                            <span class="badge bg-info text-dark"><?= htmlspecialchars($po['RecipeVersion']) ?></span>
                                            <div class="small text-muted">
                                                <i class="fa-solid fa-microchip"></i> <?= htmlspecialchars($po['TargetMachine'] ?? 'Unassigned') ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">No Recipe</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $badge = match($po['Status']) {
                                            'Active' => 'success', 'Planned' => 'primary', 'Closed' => 'secondary', 'Cancelled' => 'danger', default => 'light'
                                        };
                                        ?>
                                        <span class="badge text-bg-<?= $badge ?>"><?= $po['Status'] ?></span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($po['PlannedStartDate'])) ?></td>
                                    <td>
                                        <?php if ($po['IsDeleted']): ?>
                                            <small class="text-danger">Deleted by <?= htmlspecialchars($po['DeletedByUser']) ?></small>
                                        <?php elseif ($isAdmin): ?>
                                            <a href="production-orders-form.php?id=<?= $po['OrderID'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete Order #<?= $po['OrderID'] ?>?');">
                                                <input type="hidden" name="order_id" value="<?= $po['OrderID'] ?>">
                                                <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>