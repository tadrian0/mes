<template>
  <div class="sidebar">
    <div class="sidebar-header">
      <i class="fa-solid fa-industry me-2"></i> MES Backoffice
    </div>
    <nav class="nav flex-column">
      <RouterLink to="/" class="nav-link" :class="{ active: $route.name === 'dashboard' }">
        <i class="fa-solid fa-gauge-high me-2" style="width: 20px; text-align: center;"></i> Dashboard
      </RouterLink>

      <a class="nav-link collapsed" data-bs-toggle="collapse" href="#planningMenu" role="button" aria-expanded="false" aria-controls="planningMenu">
        <i class="fa-solid fa-calendar-days me-2" style="width: 20px; text-align: center;"></i> ERP & Planning
        <i class="fa-solid fa-chevron-down float-end mt-1" style="font-size: 0.8rem;"></i>
      </a>
      <div class="collapse" id="planningMenu">
        <div class="ms-3 border-start ps-2">
             <a class="nav-link" href="#">Production Orders</a>
        </div>
      </div>

      <a class="nav-link collapsed" data-bs-toggle="collapse" href="#masterMenu" role="button" aria-expanded="false" aria-controls="masterMenu">
        <i class="fa-solid fa-database me-2" style="width: 20px; text-align: center;"></i> Master Data
        <i class="fa-solid fa-chevron-down float-end mt-1" style="font-size: 0.8rem;"></i>
      </a>
      <div class="collapse" id="masterMenu">
        <div class="ms-3 border-start ps-2">
             <RouterLink to="/database/articles" class="nav-link" :class="{ active: $route.name === 'articles' }">
                <i class="fa-solid fa-cube me-2" style="width: 20px; text-align: center;"></i> Articles / Products
             </RouterLink>
        </div>
      </div>

    </nav>
  </div>
</template>

<style scoped>
.sidebar {
    width: 250px;
    background-color: #343a40;
    color: white;
    position: fixed;
    top: 0;
    bottom: 0;
    padding-top: 20px;
    height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1000;
    padding-bottom: 50px;
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

.sidebar-header {
    font-size: 1.5rem;
    padding: 10px 20px;
    color: white;
}
</style>
