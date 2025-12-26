<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'ApiKeyManager.php';
require_once INCLUDE_PATH . 'UserManager.php';

$isAdmin = isAdmin();
$keyManager = new ApiKeyManager($pdo);
$userManager = new UserManager($pdo);

$filterUser = isset($_GET['filter_user']) ? (int)$_GET['filter_user'] : null;
$tab = $_GET['tab'] ?? 'keys'; 

$apiKeys = $keyManager->listKeys($filterUser);
$auditLogs = ($tab === 'audit') ? $keyManager->listAuditLogs(null, null) : [];
$users = $userManager->listUsers();

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
    
    if (isset($_POST['revoke'])) {
        if ($keyManager->revokeKey((int)$_POST['key_id'], $_SESSION['user_id'])) {
            header("Location: $redirectUrl?msg=revoked"); exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - API Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>API Security & Audit</h1>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'keys' ? 'active' : '' ?>" href="?tab=keys">Active Keys</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'audit' ? 'active' : '' ?>" href="?tab=audit">Audit Logs</a>
            </li>
        </ul>

        <?php if ($tab === 'keys'): ?>
            <div class="card mb-3">
                <div class="card-header">Manage API Keys</div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end mb-3">
                        <input type="hidden" name="tab" value="keys">
                        <div class="col-md-3">
                            <label>Filter by Owner</label>
                            <select name="filter_user" class="form-select" onchange="this.form.submit()">
                                <option value="">All Users</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['OperatorID'] ?>" <?= $filterUser == $u['OperatorID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['OperatorUsername']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Key Prefix</th>
                                <th>Owner</th>
                                <th>Created</th>
                                <th>Last Used</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apiKeys as $key): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($key['Name']) ?></td>
                                    <td><code class="text-primary"><?= substr($key['KeyString'], 0, 8) ?>...</code></td>
                                    <td><?= htmlspecialchars($key['OperatorUsername']) ?></td>
                                    <td><?= $key['CreatedAt'] ?></td>
                                    <td><?= $key['LastUsedAt'] ?? '<span class="text-muted">Never</span>' ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Revoke this key?');">
                                            <input type="hidden" name="key_id" value="<?= $key['KeyID'] ?>">
                                            <button type="submit" name="revoke" class="btn btn-sm btn-danger"><i class="fa-solid fa-ban"></i> Revoke</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
        <?php elseif ($tab === 'audit'): ?>
            <div class="card">
                <div class="card-header bg-warning text-dark"><i class="fa-solid fa-shield-halved"></i> Access Logs (Last 500)</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Action</th>
                                <th>Key Name</th>
                                <th>User</th>
                                <th>Endpoint</th>
                                <th>IP</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditLogs as $log): ?>
                                <tr>
                                    <td><?= $log['Timestamp'] ?></td>
                                    <td>
                                        <?php 
                                        $color = match($log['Action']) {
                                            'Created' => 'success',
                                            'Deleted' => 'danger',
                                            'Used' => 'info',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge text-bg-<?= $color ?>"><?= $log['Action'] ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($log['KeyName'] ?? 'Unknown Key') ?></td>
                                    <td><?= htmlspecialchars($log['OperatorUsername'] ?? 'System') ?></td>
                                    <td><?= htmlspecialchars($log['Endpoint'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($log['IPAddress']) ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($log['Details'] ?? '') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>