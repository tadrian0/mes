<?php
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