<?php
require_once 'includes/Config.php';
require_once 'includes/Database.php';
require_once 'includes/ApiKeyManager.php';

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

                try {
                    $keyMgr = new ApiKeyManager($pdo);
                    $newKey = $keyMgr->createKey(
                        $user['OperatorID'], 
                        "Session Key " . date('Y-m-d H:i'), 
                        'ALL', 
                        'ALL'
                    );
                    
                    $_SESSION['fresh_api_key'] = $newKey;
                    
                    header('Location: dashboard.php');
                    exit;
                } catch (Exception $e) {
                    $error = 'Login successful, but failed to generate security token.';
                }
            } else {
                $error = 'Access denied: Admin role required for backoffice.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
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
    <link rel="stylesheet" href="styles/backoffice_login.css" />
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