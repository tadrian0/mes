<?php
session_start();
require_once 'includes/UserManager.php';

// Check if logged in and has admin role
if (!isset($_SESSION['user_id']) || !str_contains($_SESSION['roles'], 'admin')) {
    header('Location: login.php');
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=mes', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $userManager = new UserManager($pdo);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $roles = trim($_POST['roles'] ?? 'operator');
        if ($userManager->createUser($username, $password, $roles)) {
            $message = 'User created successfully.';
        } else {
            $message = 'Error creating user.';
        }
    } elseif (isset($_POST['edit'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $username = trim($_POST['edit_username'] ?? '');
        $roles = trim($_POST['edit_roles'] ?? '');
        if ($userManager->updateUser($userId, $username, null, $roles)) {
            $message = 'User updated successfully.';
        } else {
            $message = 'Error updating user.';
        }
    } elseif (isset($_POST['delete'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId !== $_SESSION['user_id'] && $userManager->deleteUser($userId)) {
            $message = 'User deleted successfully.';
        } else {
            $message = 'Error deleting user or cannot delete self.';
        }
    }
}

$users = $userManager->listUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice - Users</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            padding-top: 20px;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }
        .sidebar a:hover {
            color: white;
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: #007bff;
        }
        .sidebar .collapse {
            background-color: #2c3238;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }
        .sidebar-header {
            font-size: 1.5rem;
            padding: 10px 20px;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">MES Backoffice</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="/mes/dashboard">Dashboard</a>
            <a class="nav-link" href="/mes/planning">Planning</a>
            <a class="nav-link" href="/mes/production">Production</a>
            <a class="nav-link" href="/mes/data-analysis">Data Analysis</a>
            <!-- Database Submenu -->
            <a class="nav-link active" data-bs-toggle="collapse" href="#databaseMenu" role="button" aria-expanded="true" aria-controls="databaseMenu">
                Database
            </a>
            <div class="collapse show" id="databaseMenu">
                <a class="nav-link active" href="/mes/users">Users</a>
                <a class="nav-link" href="/mes/articles">Articles</a>
                <a class="nav-link" href="/mes/cycles">Cycles</a>
                <a class="nav-link" href="/mes/machines">Machines</a>
            </div>
            <a class="nav-link" href="/mes/logout">Log Out</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Users</h1>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Add User Form -->
        <h3>Add New User</h3>
        <form method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="roles" class="form-label">Roles (e.g., admin;operator)</label>
                <input type="text" class="form-control" id="roles" name="roles" value="operator">
            </div>
            <button type="submit" name="create" class="btn btn-primary">Add User</button>
        </form>

        <!-- Users Table -->
        <h3 class="mt-4">User List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Roles</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['OperatorID']); ?></td>
                        <td><?php echo htmlspecialchars($user['OperatorUsername']); ?></td>
                        <td><?php echo htmlspecialchars($user['OperatorRoles']); ?></td>
                        <td><?php echo htmlspecialchars($user['CreatedAt']); ?></td>
                        <td><?php echo htmlspecialchars($user['UpdatedAt']); ?></td>
                        <td>
                            <!-- Edit Button (Modal Trigger) -->
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $user['OperatorID']; ?>">
                                Edit
                            </button>
                            <!-- Delete Form -->
                            <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['OperatorID']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger" <?php if ($user['OperatorID'] == $_SESSION['user_id']) echo 'disabled'; ?>>Delete</button>
                            </form>
                        </td>
                    </tr>
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $user['OperatorID']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $user['OperatorID']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel<?php echo $user['OperatorID']; ?>">Edit User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post" action="">
                                    <div class="modal-body">
                                        <input type="hidden" name="user_id" value="<?php echo $user['OperatorID']; ?>">
                                        <div class="mb-3">
                                            <label for="edit_username_<?php echo $user['OperatorID']; ?>" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="edit_username_<?php echo $user['OperatorID']; ?>" name="edit_username" value="<?php echo htmlspecialchars($user['OperatorUsername']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_roles_<?php echo $user['OperatorID']; ?>" class="form-label">Roles</label>
                                            <input type="text" class="form-control" id="edit_roles_<?php echo $user['OperatorID']; ?>" name="edit_roles" value="<?php echo htmlspecialchars($user['OperatorRoles']); ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>