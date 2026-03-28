<?php
if (!file_exists(__DIR__ . '/includes/Database.php')) {
    header('Location: install.php');
    exit;
}

session_start();

if (isset($_SESSION['user_id'])) {
    $roles = explode(';', $_SESSION['roles']);
    if (in_array('admin', $roles)) {
        header('Location: dashboard.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>