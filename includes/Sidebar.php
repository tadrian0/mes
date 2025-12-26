<?php
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

$productionPages = ['operator-logs', 'production-logs', 'machine-stops', 'raw-materials', 'rejects', 'batches', 'adjustments'];
$databasePages   = ['users', 'articles', 'cycles', 'machines', 'countries', 'cities', 'plants', 'sections'];

$isProductionOpen = in_array($currentPage, $productionPages);
$isDatabaseOpen   = in_array($currentPage, $databasePages);

function sidebarLink($href, $text, $currentPage, $iconClass = 'fa-circle-dot')
{
    $linkPage = basename($href); 
    
    $activeClass = ($currentPage === $linkPage) ? 'active' : '';
    
    return '
    <a class="nav-link ' . $activeClass . '" href="/mes/' . $href . '">
        <i class="fa-solid ' . $iconClass . ' me-2" style="width: 20px; text-align: center;"></i> ' . htmlspecialchars($text) . '
    </a>';
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-industry me-2"></i> MES Backoffice
    </div>

    <nav class="nav flex-column">
        
        <?php echo sidebarLink('dashboard', 'Dashboard', $currentPage, 'fa-gauge-high'); ?>
        
        <?php echo sidebarLink('planning', 'Planning', $currentPage, 'fa-calendar-days'); ?>

        <a class="nav-link <?php echo $isProductionOpen ? '' : 'collapsed'; ?>" 
           data-bs-toggle="collapse" 
           href="#productionMenu" 
           role="button" 
           aria-expanded="<?php echo $isProductionOpen ? 'true' : 'false'; ?>"
           aria-controls="productionMenu">
            <i class="fa-solid fa-gears me-2" style="width: 20px; text-align: center;"></i> 
            Production
            <i class="fa-solid fa-chevron-down float-end mt-1" style="font-size: 0.8rem;"></i>
        </a>
        
        <div class="collapse <?php echo $isProductionOpen ? 'show' : ''; ?>" id="productionMenu">
            <div class="ms-3 border-start ps-2"> <?php echo sidebarLink('pages/production/operator-logs', 'Operator Logs', $currentPage, 'fa-clipboard-user'); ?>
                <?php echo sidebarLink('pages/production/production-logs', 'Production Logs', $currentPage, 'fa-file-lines'); ?>
                <?php echo sidebarLink('pages/production/machine-stops', 'Machine Stops', $currentPage, 'fa-triangle-exclamation'); ?>
                <?php echo sidebarLink('pages/production/raw-materials', 'Raw Materials', $currentPage, 'fa-boxes-stacked'); ?>
                <?php echo sidebarLink('pages/production/rejects', 'Rejects', $currentPage, 'fa-ban'); ?>
                <?php echo sidebarLink('pages/production/batches', 'Batches', $currentPage, 'fa-layer-group'); ?>
                <?php echo sidebarLink('pages/production/adjustments', 'Adjustments', $currentPage, 'fa-sliders'); ?>
            </div>
        </div>

        <?php echo sidebarLink('data-analysis', 'Data Analysis', $currentPage, 'fa-chart-line'); ?>

        <a class="nav-link <?php echo $isDatabaseOpen ? '' : 'collapsed'; ?>" 
           data-bs-toggle="collapse" 
           href="#databaseMenu" 
           role="button" 
           aria-expanded="<?php echo $isDatabaseOpen ? 'true' : 'false'; ?>"
           aria-controls="databaseMenu">
            <i class="fa-solid fa-database me-2" style="width: 20px; text-align: center;"></i> 
            Database
            <i class="fa-solid fa-chevron-down float-end mt-1" style="font-size: 0.8rem;"></i>
        </a>
        
        <div class="collapse <?php echo $isDatabaseOpen ? 'show' : ''; ?>" id="databaseMenu">
            <div class="ms-3 border-start ps-2">
                <?php echo sidebarLink('pages/database/users', 'Users', $currentPage, 'fa-users'); ?>
                <?php echo sidebarLink('pages/database/articles', 'Articles', $currentPage, 'fa-barcode'); ?>
                <?php echo sidebarLink('pages/database/cycles', 'Cycles', $currentPage, 'fa-rotate'); ?>
                <?php echo sidebarLink('pages/database/machines', 'Machines', $currentPage, 'fa-robot'); ?>
                <?php echo sidebarLink('pages/database/countries', 'Countries', $currentPage, 'fa-globe'); ?>
                <?php echo sidebarLink('pages/database/cities', 'Cities', $currentPage, 'fa-location-dot'); ?>
                <?php echo sidebarLink('pages/database/plants', 'Plants', $currentPage, 'fa-industry'); ?>
                <?php echo sidebarLink('pages/database/sections', 'Sections', $currentPage, 'fa-gear'); ?>
            </div>
        </div>

        <hr class="text-light">

        <?php echo sidebarLink('logout', 'Log Out', $currentPage, 'fa-right-from-bracket'); ?>
    </nav>
</div>