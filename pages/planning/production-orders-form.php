<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'ProductionOrderManager.php';
require_once INCLUDE_PATH . 'ArticleManager.php';

$isAdmin = isAdmin();
if (!$isAdmin) { header('Location: production-orders.php'); exit; }

$orderManager = new ProductionOrderManager($pdo);
$articleManager = new ArticleManager($pdo);

// Initialize Variables
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = !empty($orderId);
$order = $isEdit ? $orderManager->getOrderById($orderId) : null;
$articles = $articleManager->listArticles();

// Error Handling: Invalid ID
if ($isEdit && !$order) {
    die('<div class="container mt-5"><div class="alert alert-danger">Order not found. <a href="production-orders.php">Go back</a></div></div>');
}

$error = '';

// --- HANDLE SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleId = (int)$_POST['article_id'];
    $quantity = (float)$_POST['quantity'];
    $startDate = $_POST['start_date'];
    $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $status = $isEdit ? $_POST['status'] : 'Planned';

    if ($quantity <= 0) {
        $error = "Error: Target Quantity must be greater than 0.";
    } 
    elseif ($endDate && strtotime($endDate) < strtotime($startDate)) {
        $error = "Error: Due Date cannot be earlier than the Start Date.";
    } 
    else {
        if ($isEdit) {
            if ($orderManager->updateOrder($orderId, $articleId, $quantity, $startDate, $endDate, $status)) {
                header("Location: production-orders.php?msg=updated"); exit;
            } else {
                $error = "Database Error: Failed to update order.";
            }
        } else {
            if ($orderManager->createOrder($articleId, $quantity, $startDate, $endDate, $status)) {
                header("Location: production-orders.php?msg=created"); exit;
            } else {
                $error = "Database Error: Failed to create order.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - <?= $isEdit ? 'Edit' : 'Create' ?> Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <div class="container-fluid" style="max-width: 800px;">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fa-solid <?= $isEdit ? 'fa-pen-to-square' : 'fa-plus-circle' ?> me-2"></i>
                        <?= $isEdit ? "Edit Order #$orderId" : "Create New Production Order" ?>
                    </h4>
                    <a href="production-orders.php" class="btn btn-outline-light btn-sm">Back to List</a>
                </div>
                <div class="card-body">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Article / Product <span class="text-danger">*</span></label>
                            <select name="article_id" class="form-select" required>
                                <option value="">Select Article...</option>
                                <?php foreach ($articles as $art): ?>
                                    <?php $selected = ($isEdit && $order['ArticleID'] == $art['ArticleID']) ? 'selected' : ''; ?>
                                    <option value="<?= $art['ArticleID'] ?>" <?= $selected ?>><?= htmlspecialchars($art['Name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Target Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" step="0.01" class="form-control" required min="0.01"
                                       value="<?= $isEdit ? $order['TargetQuantity'] : '' ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <?php if ($isEdit): ?>
                                    <select name="status" class="form-select" required>
                                        <?php foreach (['Planned', 'Active', 'Closed', 'Cancelled'] as $s): ?>
                                            <option value="<?= $s ?>" <?= ($order['Status'] == $s) ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" value="Planned" disabled>
                                    <input type="hidden" name="status" value="Planned">
                                    <div class="form-text">New orders start as <strong>Planned</strong>.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Start Date (Planned) <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_date" class="form-control" required
                                       value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($order['PlannedStartDate'])) : date('Y-m-d\T08:00') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Due Date (Optional)</label>
                                <input type="datetime-local" name="end_date" class="form-control"
                                       value="<?= ($isEdit && $order['PlannedEndDate']) ? date('Y-m-d\TH:i', strtotime($order['PlannedEndDate'])) : '' ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="production-orders.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success px-4"><i class="fa-solid fa-save me-2"></i> <?= $isEdit ? 'Update' : 'Create' ?></button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>