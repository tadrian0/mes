<?php
session_start();

// Check if logged in and has admin role
if (!isset($_SESSION['user_id']) || !str_contains($_SESSION['roles'], 'admin')) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MES Backoffice Dashboard</title>
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
            <a class="nav-link active" href="/mes/dashboard">Dashboard</a>
            <a class="nav-link" href="/mes/planning">Planning</a>
            <a class="nav-link" href="/mes/production">Production</a>
            <a class="nav-link" href="/mes/data-analysis">Data Analysis</a>
            <!-- Database Submenu -->
            <a class="nav-link" data-bs-toggle="collapse" href="#databaseMenu" role="button" aria-expanded="false" aria-controls="databaseMenu">
                Database
            </a>
            <div class="collapse" id="databaseMenu">
                <a class="nav-link" href="/mes/users">Users</a>
                <a class="nav-link" href="/mes/articles">Articles</a>
                <a class="nav-link" href="/mes/cycles">Cycles</a>
                <a class="nav-link" href="/mes/machines">Machines</a>
            </div>
            <a class="nav-link" href="/mes/logout">Log Out</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Dashboard</h1>
        <p>Placeholder for dashboard content.</p>
    </div>

    <!-- Bootstrap 5 JS via CDN (for collapse functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>