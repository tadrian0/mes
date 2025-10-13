<?php
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

function sidebarLink($href, $text, $currentPage)
{
    $activeClass = ($currentPage === basename($href, '.php')) ? 'active' : '';
    return '<a class="nav-link ' . $activeClass . '" href="/mes/' . $href . '">' . htmlspecialchars($text) . '</a>';
}
?>

<div class="sidebar">
    <div class="sidebar-header">MES Backoffice</div>
    <nav class="nav flex-column">
        <?php echo sidebarLink('dashboard', 'Dashboard', $currentPage); ?>
        <?php echo sidebarLink('planning', 'Planning', $currentPage); ?>
        <a class="nav-link" data-bs-toggle="collapse" href="#productionMenu" role="button" aria-expanded="false"
            aria-controls="productionMenu">
            Production
        </a>
        <div class="collapse" id="productionMenu">
            <?php echo sidebarLink('pages/production/operator-logs', 'Operator Logs', $currentPage); ?>
            <?php echo sidebarLink('pages/production/production-logs', 'Production Logs', $currentPage); ?>
            <?php echo sidebarLink('pages/production/machine-stops', 'Machine Stops', $currentPage); ?>
            <?php echo sidebarLink('pages/production/raw-materials', 'Raw Materials', $currentPage); ?>
            <?php echo sidebarLink('pages/production/rejects', 'Rejects', $currentPage); ?>
            <?php echo sidebarLink('pages/production/batches', 'Batches', $currentPage); ?>
            <?php echo sidebarLink('pages/production/adjustments', 'Adjustments', $currentPage); ?>
        </div>
        <?php echo sidebarLink('data-analysis', 'Data Analysis', $currentPage); ?>
        <a class="nav-link" data-bs-toggle="collapse" href="#databaseMenu" role="button" aria-expanded="false"
            aria-controls="databaseMenu">
            Database
        </a>
        <div class="collapse" id="databaseMenu">
            <?php echo sidebarLink('pages/database/users', 'Users', $currentPage); ?>
            <?php echo sidebarLink('pages/database/articles', 'Articles', $currentPage); ?>
            <?php echo sidebarLink('pages/database/cycles', 'Cycles', $currentPage); ?>
            <?php echo sidebarLink('pages/database/machines', 'Machines', $currentPage); ?>
        </div>
        <?php echo sidebarLink('logout', 'Log Out', $currentPage); ?>
    </nav>
</div>