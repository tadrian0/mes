<?php
if (!file_exists(__DIR__ . '/includes/Database.php')) {
    header('Location: install.php');
    return;
}
require_once 'includes/Config.php';
require_once 'includes/Database.php';
require_once 'includes/CountryManager.php';

session_start();

$machineId = isset($_GET['machine_id']) ? (int)$_GET['machine_id'] : 0;
$viewMode  = ($machineId > 0) ? 'interface' : 'selection';

$data = [];

if ($viewMode === 'selection') {
    $countryManager = new CountryManager($pdo);
    $data['countries'] = $countryManager->listAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Totem</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php if ($viewMode === 'selection'): ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="totem/css/style.css">
    <?php endif; ?>

    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; overflow: hidden; height: 100vh; }
        .wrapper { height: 100%; width: 100%; }
        body.selection-mode { overflow: auto; }
    </style>
</head>
<body class="<?= $viewMode === 'selection' ? 'selection-mode' : '' ?>">

    <div class="wrapper">
        <?php
        if ($viewMode === 'selection') {
            include 'totem/views/selection.php';
        } else {
            include 'totem/views/interface-compact.php';
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($viewMode === 'selection'): ?>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="totem/js/selection.js"></script>
    <?php else: ?>
        <script>const MACHINE_ID = <?= $machineId ?>;</script>
        <script src="totem/js/app-compact.js"></script>
    <?php endif; ?>

</body>
</html>
