<?php
require_once 'includes/Config.php';
require_once 'includes/Database.php';
require_once 'includes/MachineManager.php';

session_start();

$machineId = isset($_GET['machine_id']) ? (int) $_GET['machine_id'] : 0;
$machineManager = new MachineManager($pdo);
$machine = $machineManager->getMachineById($machineId);

if (!$machine) {
    die('<div style="color:white; background:red; padding:20px; text-align:center; font-family:sans-serif;">
            <h1>Error</h1>
            <p>Machine ID not found or invalid.</p>
            <a href="login.php" style="color:white;">Go Back</a>
         </div>');
}

$statusClass = match ($machine['Status']) {
    'Active' => 'status-working',
    'Inactive' => 'status-stopped',
    'Maintenance' => 'status-maintenance',
    default => ''
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Totem - <?= htmlspecialchars($machine['Name']) ?></title>
    <link rel="stylesheet" href="totem.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

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

    <div id="login-modal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <h2>Operator Login</h2>
            <input type="text" id="login-username" placeholder="Username / Badge ID" class="totem-input">
            <input type="password" id="login-password" placeholder="Password" class="totem-input">
            <div class="modal-actions">
                <button id="btn-perform-login" class="large-btn">Login</button>
                <button id="btn-cancel-login" class="large-btn secondary">Cancel</button>
            </div>
            <p id="login-error" style="color:red; margin-top:10px;"></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        const MACHINE_ID = <?= $machineId ?>;

        $(document).ready(function () {
            // 1. Initial Load & Polling
            fetchOperators();
            setInterval(fetchOperators, 5000);

            // 2. Modal Logic
            $('#btn-login-modal').click(function () {
                $('#login-username').val('');
                $('#login-password').val('');
                $('#login-error').text('');
                $('#login-modal').css('display', 'flex');
            });

            $('#btn-cancel-login').click(function () {
                $('#login-modal').hide();
            });

            // 3. Perform Login
            $('#btn-perform-login').click(function () {
                const user = $('#login-username').val();
                const pass = $('#login-password').val();

                if (!user || !pass) {
                    $('#login-error').text("Please enter credentials.");
                    return;
                }

                $.post('api/totem-ajax.php', {
                    action: 'login',
                    machine_id: MACHINE_ID,
                    username: user,
                    password: pass
                }, function (res) {
                    if (res.status === 'success') {
                        $('#login-modal').hide();
                        fetchOperators();
                    } else {
                        $('#login-error').text(res.message);
                    }
                }, 'json');
            });

            // 4. Logout
            $(document).on('click', '.btn-logout-op', function () {
                const opId = $(this).data('id');
                const opName = $(this).data('name');

                if (confirm('Log out ' + opName + '?')) {
                    $.post('api/totem-ajax.php', {
                        action: 'logout',
                        machine_id: MACHINE_ID,
                        operator_id: opId
                    }, function (res) {
                        if (res.status === 'success') {
                            fetchOperators();
                        } else {
                            alert(res.message);
                        }
                    }, 'json');
                }
            });
        });

        // Helpers
        function fetchOperators() {
            $.post('api/totem-ajax.php', {
                action: 'fetch_operators',
                machine_id: MACHINE_ID
            }, function (res) {
                if (res.status === 'success') {
                    renderOperators(res.operators);
                }
            }, 'json');
        }

        function renderOperators(operators) {
            $('.operator-slot').removeClass('active').addClass('empty').html('--');
            operators.forEach((op, index) => {
                if (index < 6) {
                    const slot = $(`#slot-${index}`);
                    slot.removeClass('empty').addClass('active');
                    slot.html(`
                        <div>
                            <div class="op-name">${op.OperatorUsername}</div>
                            <div class="op-time">${formatTime(op.LoginTime)}</div>
                        </div>
                        <button class="btn-logout-op" data-id="${op.OperatorID}" data-name="${op.OperatorUsername}">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    `);
                }
            });
        }

        function formatTime(sqlDate) {
            const d = new Date(sqlDate);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    </script>
</body>

</html>