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
        <?php echo sidebarLink('production', 'Production', $currentPage); ?>
        <?php echo sidebarLink('data-analysis', 'Data Analysis', $currentPage); ?>
        <a class="nav-link" data-bs-toggle="collapse" href="#databaseMenu" role="button" aria-expanded="false"
            aria-controls="databaseMenu">
            Database
        </a>
        <div class="collapse" id="databaseMenu">
            <?php echo sidebarLink('users', 'Users', $currentPage); ?>
            <?php echo sidebarLink('articles', 'Articles', $currentPage); ?>
            <?php echo sidebarLink('cycles', 'Cycles', $currentPage); ?>
            <?php echo sidebarLink('machines', 'Machines', $currentPage); ?>
        </div>
        <?php echo sidebarLink('logout', 'Log Out', $currentPage); ?>
    </nav>
</div>