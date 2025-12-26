<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'ApiKeyManager.php';
require_once INCLUDE_PATH . 'UserManager.php';

$isAdmin = isAdmin();
$keyManager = new ApiKeyManager($pdo);
$userManager = new UserManager($pdo);

$filterAction = isset($_GET['filter_action']) && $_GET['filter_action'] !== '' ? $_GET['filter_action'] : null;
$filterUser   = isset($_GET['filter_user']) && $_GET['filter_user'] !== '' ? (int)$_GET['filter_user'] : null;

$logs = $keyManager->listAuditLogs(null, $filterAction); 
$users = $userManager->listUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - API Audits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>API Usage Audits</h1>

        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Action</label>
                        <select name="filter_action" class="form-select">
                            <option value="">All Actions</option>
                            <option value="Created" <?= $filterAction=='Created'?'selected':'' ?>>Created</option>
                            <option value="Used" <?= $filterAction=='Used'?'selected':'' ?>>Used</option>
                            <option value="Deleted" <?= $filterAction=='Deleted'?'selected':'' ?>>Deleted</option>
                            <option value="Updated" <?= $filterAction=='Updated'?'selected':'' ?>>Updated</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">Latest Activity</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>Action</th>
                            <th>Key Name</th>
                            <th>User</th>
                            <th>Endpoint / Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap"><?= $log['Timestamp'] ?></td>
                                <td>
                                    <?php 
                                    $badge = match($log['Action']) {
                                        'Created' => 'success',
                                        'Deleted' => 'danger',
                                        'Used' => 'info',
                                        'PermissionChange' => 'warning',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge text-bg-<?= $badge ?>"><?= $log['Action'] ?></span>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($log['KeyName'] ?? 'Unknown/Deleted') ?></td>
                                <td><?= htmlspecialchars($log['OperatorUsername'] ?? 'System') ?></td>
                                <td>
                                    <?php if ($log['Endpoint']): ?>
                                        <code class="text-primary"><?= htmlspecialchars($log['Endpoint']) ?></code>
                                    <?php endif; ?>
                                    <?php if ($log['Details']): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($log['Details']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><small class="font-monospace"><?= htmlspecialchars($log['IPAddress']) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>