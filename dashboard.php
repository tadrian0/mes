<?php
require_once 'includes/Config.php';
require_once 'includes/IsAdmin.php';

$isAdmin = isAdmin();
$readOnly = !$isAdmin;

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MES Backoffice Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-header">MES Backoffice</div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="/mes/dashboard">Dashboard</a>
            <a class="nav-link" href="/mes/planning">Planning</a>
            <a class="nav-link" href="/mes/production">Production</a>
            <a class="nav-link" href="/mes/data-analysis">Data Analysis</a>
            <a class="nav-link" data-bs-toggle="collapse" href="#databaseMenu" role="button" aria-expanded="false"
                aria-controls="databaseMenu">
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

    <div class="content">
        <h1>Dashboard</h1>
        <p>Placeholder for dashboard content.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>