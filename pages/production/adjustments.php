<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'AdjustmentManager.php'; 

$isAdmin = isAdmin();

$adjustmentManager = new AdjustmentManager($pdo);

$adjustments = $adjustmentManager->listAdjustments();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete']) && $isAdmin) {
        $adjustmentId = (int) $_POST['adjustment_id'];
        if ($adjustmentManager->deleteAdjustment($adjustmentId)) {
            $message = "Adjustment deleted successfully.";
        } else {
            $message = "Failed to delete adjustment.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjustments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Adjustments</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <?php include INCLUDE_PATH . 'pages/adjustments/adjustment-add.php'; ?>
        <?php endif; ?>

        <h3 class="mt-4">Adjustment List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>AdjustmentID</th>
                    <th>ProductionOrderId</th>
                    <th>ArticleId</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adjustments as $adjustment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($adjustment['AdjustmentID']); ?></td>
                        <td><?php echo htmlspecialchars($adjustment['ProductionOrderId']); ?></td>
                        <td><?php echo htmlspecialchars($adjustment['ArticleId']); ?></td>
                        <td><?php echo htmlspecialchars($adjustment['Quantity']); ?></td>
                        <td>
                            <?php if ($isAdmin): ?>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editModal<?php echo $adjustment['AdjustmentID']; ?>">
                                    Edit
                                </button>
                                <form method="post" action="" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this adjustment?');">
                                    <input type="hidden" name="adjustment_id" value="<?php echo $adjustment['AdjustmentID']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                <?php include INCLUDE_PATH . 'pages/adjustments/adjustment-edit.php'; ?>
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