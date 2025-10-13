<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=mes', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT OperatorID, OperatorUsername, OperatorPassword, OperatorRoles FROM Users WHERE OperatorUsername = ?');
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
    <title>MES Backoffice Login</title>
</head>

<body>
    <h2>MES Backoffice Login</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>

</html>