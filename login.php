<?php
require_once 'includes/Config.php';
require_once 'includes/Database.php';

$userTableName = "user";

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT OperatorID, OperatorUsername, OperatorPassword, OperatorRoles FROM $userTableName WHERE OperatorUsername = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['OperatorPassword']) {
            $roles = explode(';', $user['OperatorRoles']);
            if (in_array('admin', $roles)) {
                $_SESSION['user_id'] = $user['OperatorID'];
                $_SESSION['username'] = $user['OperatorUsername'];
                $_SESSION['roles'] = $user['OperatorRoles'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Access denied: Admin role required for backoffice.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    
    <style>
        body {
            background-color: #f4f6f9; /* Light grey background for contrast */
            /* Override sidebar flex layout for this specific page since there is no sidebar */
            display: block; 
            min-height: 100vh;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .card-header {
            background-color: #343a40; /* Matching sidebar color */
            color: white;
            text-align: center;
            padding: 1.5rem 1rem;
            border-radius: 8px 8px 0 0 !important;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .icon-large {
            font-size: 3rem;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container login-container">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-industry icon-large"></i>
                    <h4 class="mb-0 mt-2">MES Backoffice</h4>
                    <small class="text-white-50">Manufacturing Execution System</small>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label text-secondary fw-bold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label text-secondary fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">
                                <i class="fa-solid fa-right-to-bracket me-2"></i> Login
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-light text-muted small">
                    &copy; <?= date('Y') ?> MES System | Secure Access
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>