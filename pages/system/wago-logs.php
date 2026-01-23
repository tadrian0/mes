<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'WagoManager.php';
require_once INCLUDE_PATH . 'MachineManager.php';

$isAdmin = isAdmin();
$wagoManager = new WagoManager($pdo);
$machineManager = new MachineManager($pdo);

$filterMachine = isset($_GET['filter_machine']) && $_GET['filter_machine'] !== '' ? (int)$_GET['filter_machine'] : null;
$filterStartDate = $_GET['filter_start_date'] ?? date('Y-m-d'); 
$filterEndDate = $_GET['filter_end_date'] ?? null;

$logs = $wagoManager->listLogs($filterMachine, $filterStartDate, $filterEndDate);
$machines = $machineManager->listMachines();

$autoRefresh = isset($_GET['refresh']) ? $_GET['refresh'] : 'off';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php if($autoRefresh == 'on'): ?>
        <meta http-equiv="refresh" content="5"> <?php endif; ?>
    <title>MES - PLC Signals (Wago)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>PLC Signal Logs (Wago)</h1>
            <div>
                <a href="?<?= http_build_query(array_merge($_GET, ['refresh' => $autoRefresh == 'on' ? 'off' : 'on'])) ?>" 
                   class="btn btn-<?= $autoRefresh == 'on' ? 'success' : 'secondary' ?> btn-sm">
                   <i class="fa-solid fa-sync"></i> Auto-Refresh: <?= strtoupper($autoRefresh) ?>
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Search & Filter</div>
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="refresh" value="<?= $autoRefresh ?>">
                    
                    <div class="col-md-3">
                        <label class="form-label small">Machine</label>
                        <select class="form-select form-select-sm" name="filter_machine">
                            <option value="">All Machines</option>
                            <?php foreach ($machines as $m): ?>
                                <option value="<?= $m['MachineID'] ?>" <?= $filterMachine == $m['MachineID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="filter_start_date" class="form-control form-control-sm" value="<?= $filterStartDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="filter_end_date" class="form-control form-control-sm" value="<?= $filterEndDate ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-search"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Log ID</th>
                            <th>Timestamp</th>
                            <th>Machine</th>
                            <th>Count</th>
                            <th>Processed Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No signals found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $row): ?>
                                <tr>
                                    <td>#<?= $row['LogID'] ?></td>
                                    <td><?= date('H:i:s d-m-Y', strtotime($row['Timestamp'])) ?></td>
                                    <td class="fw-bold">
                                        <?= htmlspecialchars($row['MachineName']) ?>
                                        <div class="small text-muted fw-normal"><?= htmlspecialchars($row['Location'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6">+<?= $row['ProductionCount'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['Processed']): ?>
                                            <span class="badge bg-success">Processed</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending</span>
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