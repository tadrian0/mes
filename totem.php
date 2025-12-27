<?php
require_once 'includes/Config.php';
require_once 'includes/Database.php';
require_once 'includes/MachineManager.php';

session_start();

$machineId = isset($_GET['machine_id']) ? (int)$_GET['machine_id'] : 0;
$machineManager = new MachineManager($pdo);
$machine = $machineManager->getMachineById($machineId);

if (!$machine) {
    die('<div style="color:white; background:red; padding:20px; text-align:center; font-family:sans-serif;">
            <h1>Error</h1>
            <p>Machine ID not found or invalid.</p>
            <a href="login.php" style="color:white;">Go Back</a>
         </div>');
}

$statusClass = match($machine['Status']) {
    'Active' => 'status-working',
    'Inactive' => 'status-stopped',
    'Maintenance' => 'status-maintenance',
    default => ''
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($machine['Name']) ?></title>
    <link rel="stylesheet" href="totem.css"/>
</head>

<body>

<div id="header">
    <div class="auth-btn-wrapper">
        <button id="btn-login">Login</button>
    </div>

    <div id="operators">
        <div class="operator-slot empty">Op Slot 1</div>
        <div class="operator-slot empty">Op Slot 2</div>
        <div class="operator-slot empty">Op Slot 3</div>
        <div class="operator-slot empty">Op Slot 4</div>
        <div class="operator-slot empty">Op Slot 5</div>
        <div class="operator-slot empty">Op Slot 6</div>
    </div>

    <div id="header-right">
        <button class="secondary" id="btn-logout">Logout</button>
    </div>
</div>

<div id="main">
    <div id="production-area">

        <div class="panel">
            <h3>Current Production</h3>
            <p>Order No: <strong>PO-9999</strong></p>
            <p>Part: <strong>Sample Component A</strong></p>
            
            <div class="progress-container">
                <div class="progress-bar" style="width: 45%;">45%</div>
            </div>
            
            <div style="margin-top:15px; display:flex; gap:10px;">
                <button class="large-btn">Stop Production</button>
                <button class="large-btn secondary">Suspend</button>
            </div>
        </div>

    </div>

    <div id="machine-panel">
        <div class="panel h-100">
            <h3>Machine Info</h3>
            
            <div class="machine-detail">
                <span class="label">Name:</span>
                <span class="value"><?= htmlspecialchars($machine['Name']) ?></span>
            </div>

            <div class="machine-detail">
                <span class="label">Model:</span>
                <span class="value"><?= htmlspecialchars($machine['Model']) ?></span>
            </div>

            <div class="machine-detail">
                <span class="label">Loc:</span>
                <span class="value"><?= htmlspecialchars($machine['Location']) ?></span>
            </div>

            <hr>

            <div class="machine-detail">
                <span class="label">Status:</span>
                <span class="value <?= $statusClass ?>"><?= htmlspecialchars($machine['Status']) ?></span>
            </div>

            <div class="machine-detail">
                <span class="label">Capacity:</span>
                <span class="value"><?= htmlspecialchars($machine['Capacity']) ?> t/h</span>
            </div>

            <div class="mt-auto">
                <button class="secondary w-100 mb-2">Declare Breakdown</button>
                <button class="secondary w-100">Call Maintenance</button>
            </div>
        </div>
    </div>

</div>

<div id="footer">
    <div id="qc" class="footer-section">
        <h3>Quality</h3>
        <button class="btn-qc">Reject Parts</button>
    </div>

    <div id="raw-material" class="footer-section">
        <h3>Material</h3>
        <button>Scan Batch</button>
    </div>

    <div id="labels" class="footer-section">
        <h3>Logistics</h3>
        <button>Print Label</button>
    </div>
</div>

</body>
</html>