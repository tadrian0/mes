<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'UserManager.php';

$isAdmin = isAdmin();
$userManager = new UserManager($pdo);

$search = $_GET['search'] ?? null;
$filterRole = $_GET['role'] ?? null;

$users = $userManager->listUsers($search, $filterRole);

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = strtok($_SERVER["REQUEST_URI"], '?');
    
    if (isset($_POST['create_user'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];

        if ($userManager->createUser($username, $password, $role)) {
            header("Location: $redirect?msg=created"); exit;
        } else {
            $error = "Failed to create user (Username might be taken).";
        }
    }

    if (isset($_POST['edit_user'])) {
        $id = (int)$_POST['user_id'];
        $username = trim($_POST['edit_username']);
        $role = $_POST['edit_role'];
        $password = !empty($_POST['edit_password']) ? trim($_POST['edit_password']) : null;

        if ($userManager->updateUser($id, $username, $password, $role)) {
            header("Location: $redirect?msg=updated"); exit;
        } else {
            $error = "Failed to update user.";
        }
    }

    if (isset($_POST['delete_user'])) {
        if ($userManager->deleteUser((int)$_POST['user_id'])) {
            header("Location: $redirect?msg=deleted"); exit;
        } else {
            $error = "Failed to delete user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>User Management</h1>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Action completed successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Search & Filter</div>
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small">Search Username</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="e.g. John" value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">All Roles</option>
                            <option value="operator" <?= $filterRole === 'operator' ? 'selected' : '' ?>>Operator</option>
                            <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-search"></i> Filter</button>
                    </div>
                    <div class="col-md-2">
                         <a href="users.php" class="btn btn-secondary btn-sm w-100">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fa-solid fa-plus"></i> New User
            </button>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['OperatorID']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($u['OperatorUsername']) ?></td>
                                <td>
                                    <?php if($u['OperatorRoles'] == 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Operator</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($u['CreatedAt']) ?></td>
                                <td>
                                    <?php if ($isAdmin): ?>
                                        <button class="btn btn-sm btn-warning btn-edit" 
                                                data-id="<?= $u['OperatorID'] ?>"
                                                data-username="<?= htmlspecialchars($u['OperatorUsername']) ?>"
                                                data-role="<?= htmlspecialchars($u['OperatorRoles']) ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                            <input type="hidden" name="user_id" value="<?= $u['OperatorID'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="operator">Operator</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="edit_username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="edit_role" id="edit_role" class="form-select" required>
                                <option value="operator">Operator</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password (Leave blank to keep current)</label>
                            <input type="password" name="edit_password" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_user" class="btn btn-warning">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.btn-edit').click(function() {
                let id = $(this).data('id');
                let username = $(this).data('username');
                let role = $(this).data('role');

                $('#edit_user_id').val(id);
                $('#edit_username').val(username);
                $('#edit_role').val(role);

                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            });
        });
    </script>
</body>
</html>