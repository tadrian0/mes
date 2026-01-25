<?php
$activeOrder   = $data['activeOrder'];
$plannedOrders = $data['plannedOrders'];
$statusClass   = $data['statusClass'];
?>

<?php include 'totem/header.php'; ?>

<div id="main">
    <?php include 'totem/production_area.php'; ?>

    <?php include 'totem/machine_panel.php'; ?>
</div>

<div id="footer">
    <?php include 'totem/footer_qc.php'; ?>
    <?php include 'totem/footer_material.php'; ?>
    <?php include 'totem/footer_logistics.php'; ?>
</div>

<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-3">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-5 pt-2">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-user-circle fa-4x text-primary mb-3"></i>
                    <h3 class="fw-bold text-dark">Operator Login</h3>
                    <p class="text-muted">Enter credentials</p>
                </div>
                <div id="login-alert" class="alert alert-danger d-none d-flex align-items-center mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><span id="login-error-msg"></span>
                </div>
                <form id="loginForm" onsubmit="return false;">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="login-username" placeholder="User" required>
                        <label>Username</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="login-password" placeholder="Pass" required>
                        <label>Password</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" id="btn-perform-login" class="btn btn-primary btn-lg fw-bold shadow-sm">LOG IN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>