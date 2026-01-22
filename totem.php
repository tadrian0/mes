<?php
require_once 'includes/Config.php';
require_once 'includes/Database.php';
require_once 'includes/MachineManager.php';
// NEW: Include Production Order Manager
require_once 'includes/ProductionOrderManager.php';

// Includes for Selection Screen Filters
require_once 'includes/CountryManager.php';
require_once 'includes/PlantManager.php';
require_once 'includes/SectionManager.php';
require_once 'includes/CityManager.php';

session_start();

$machineId = isset($_GET['machine_id']) ? (int)$_GET['machine_id'] : 0;
$machineManager = new MachineManager($pdo);
// Initialize Production Manager
$poManager = new ProductionOrderManager($pdo);

$machine = null;

if ($machineId > 0) {
    $machine = $machineManager->getMachineById($machineId);
}

// =================================================================================
// CASE 1: MACHINE NOT SELECTED -> SHOW SELECTION SCREEN (DATATABLES)
// =================================================================================
if (!$machine) {
    $countryManager = new CountryManager($pdo);
    $countries = $countryManager->listAll();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Select Totem</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
        <style>
            body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
            .selection-container { margin-top: 50px; max-width: 1400px; }
            .dataTables_filter { display: none; }
            .table td { vertical-align: middle; }
        </style>
    </head>
    <body>
        <div class="container selection-container">
            <div class="card shadow-lg">
                <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fa-solid fa-tablet-screen-button me-2"></i> Select Machine Totem</h3>
                    <a href="login.php" class="btn btn-outline-light btn-sm">Back to Login</a>
                </div>
                <div class="card-body">
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span>
                                <input type="text" class="form-control" id="globalSearch" placeholder="Search machine name, model...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select filter-input" id="filter_country" data-col-index="3">
                                <option value="">All Countries</option>
                                <?php foreach ($countries as $c): ?>
                                    <option value="<?= htmlspecialchars($c['Name']) ?>"><?= htmlspecialchars($c['Name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select filter-input" id="filter_city" data-col-index="4"><option value="">All Cities</option></select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select filter-input" id="filter_plant" data-col-index="5"><option value="">All Plants</option></select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select filter-input" id="filter_section" data-col-index="6"><option value="">All Sections</option></select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="totemSelectTable" class="table table-hover table-striped w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Machine</th>
                                    <th>Model</th>
                                    <th>Country</th>
                                    <th>City</th>
                                    <th>Plant</th>
                                    <th>Section</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                var table = $('#totemSelectTable').DataTable({
                    ajax: 'api/machines-fetch.php',
                    columns: [
                        { data: 'MachineID' },
                        { 
                            data: 'Name',
                            render: function(data, type, row) {
                                return `<div class="fw-bold">${data}</div><small class="text-muted">${row.Location}</small>`;
                            }
                        },
                        { data: 'Model' },
                        { data: 'CountryName', defaultContent: '-' },
                        { data: 'CityName', defaultContent: '-' },
                        { data: 'PlantName', defaultContent: '-' },
                        { data: 'SectionName', defaultContent: '-' },
                        { 
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `<a href="?machine_id=${row.MachineID}" class="btn btn-primary btn-sm w-100">
                                            <i class="fa-solid fa-arrow-right"></i> Open Totem
                                        </a>`;
                            }
                        }
                    ],
                    order: [[ 1, 'asc' ]],
                    pageLength: 10,
                    lengthMenu: [10, 25, 50],
                    language: { search: "", searchPlaceholder: "Search..." }
                });

                $('#globalSearch').on('keyup', function() { table.search(this.value).draw(); });

                function updateCascadingOptions() {
                    let country = $('#filter_country').val();
                    let city    = $('#filter_city').val();
                    let plant   = $('#filter_plant').val();

                    $.ajax({
                        url: 'api/get-filter-options.php',
                        data: { country: country, city: city, plant: plant },
                        dataType: 'json',
                        success: function(res) {
                            function populate(sel, data, curr) {
                                let $el = $(sel);
                                $el.empty().append('<option value="">All</option>');
                                data.forEach(item => $el.append(new Option(item, item, false, item === curr)));
                            }

                            if (!$('#filter_city').val()) populate('#filter_city', res.cities, '');
                            else {
                                let curr = $('#filter_city').val();
                                populate('#filter_city', res.cities, curr);
                                if (res.cities.indexOf(curr) === -1) $('#filter_city').val('');
                            }

                            populate('#filter_plant', res.plants, $('#filter_plant').val());
                            populate('#filter_section', res.sections, $('#filter_section').val());
                        }
                    });
                }

                updateCascadingOptions();

                $('#filter_country, #filter_city, #filter_plant').on('change', function() {
                    if(this.id == 'filter_country') { $('#filter_city').val(''); $('#filter_plant').val(''); $('#filter_section').val(''); }
                    else if(this.id == 'filter_city') { $('#filter_plant').val(''); $('#filter_section').val(''); }
                    else if(this.id == 'filter_plant') { $('#filter_section').val(''); }
                    
                    applyFilters();
                    updateCascadingOptions();
                });

                $('#filter_section').on('change', function() { applyFilters(); });

                function applyFilters() {
                    $('.filter-input').each(function() {
                        let colIndex = $(this).data('col-index');
                        let val = $.fn.dataTable.util.escapeRegex($(this).val());
                        table.column(colIndex).search(val ? '^'+val+'$' : '', true, false);
                    });
                    table.draw();
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// =================================================================================
// CASE 2: MACHINE SELECTED -> SHOW TOTEM INTERFACE
// =================================================================================

// --- FETCH PRODUCTION DATA (NEW LOGIC) ---
$activeOrder = $poManager->getActiveOrderForMachine($machineId);
$plannedOrders = [];

// If no active order is running, fetch the planned ones to let operator choose
if (!$activeOrder) {
    $plannedOrders = $poManager->getPlannedOrders($machineId);
}

// --- MACHINE STATUS COLOR ---
$statusClass = match($machine['Status']) {
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
    <link rel="stylesheet" href="totem.css"/>
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

        $(document).ready(function() {
            // 1. Initial Load & Polling for Operators
            fetchOperators();
            setInterval(fetchOperators, 5000);

            // 2. Modal Logic
            $('#btn-login-modal').click(function() {
                $('#login-username').val('');
                $('#login-password').val('');
                $('#login-error').text('');
                $('#login-modal').css('display', 'flex');
            });

            $('#btn-cancel-login').click(function() {
                $('#login-modal').hide();
            });

            // 3. Perform Login
            $('#btn-perform-login').click(function() {
                const user = $('#login-username').val();
                const pass = $('#login-password').val();

                if(!user || !pass) {
                    $('#login-error').text("Please enter credentials.");
                    return;
                }

                $.post('api/totem-ajax.php', {
                    action: 'login',
                    machine_id: MACHINE_ID,
                    username: user,
                    password: pass
                }, function(res) {
                    if(res.status === 'success') {
                        $('#login-modal').hide();
                        fetchOperators(); 
                    } else {
                        $('#login-error').text(res.message);
                    }
                }, 'json');
            });

            // 4. Logout
            $(document).on('click', '.btn-logout-op', function() {
                const opId = $(this).data('id');
                const opName = $(this).data('name');
                
                if(confirm('Log out ' + opName + '?')) {
                    $.post('api/totem-ajax.php', {
                        action: 'logout',
                        machine_id: MACHINE_ID,
                        operator_id: opId
                    }, function(res) {
                        if(res.status === 'success') {
                            fetchOperators();
                        } else {
                            alert(res.message);
                        }
                    }, 'json');
                }
            });

            // 6. STOP PRODUCTION
            $(document).on('click', '.btn-stop-order', function() {
                let orderId = $(this).data('id');

                if(confirm("Are you sure you want to STOP/FINISH this order?")) {
                    $.post('api/totem-ajax.php', {
                        action: 'stop_order',
                        machine_id: MACHINE_ID,
                        order_id: orderId
                    }, function(res) {
                        if(res.status === 'success') {
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    }, 'json');
                }
            });

            // 7. SUSPEND PRODUCTION
            $(document).on('click', '.btn-suspend-order', function() {
                let orderId = $(this).data('id');

                if(confirm("Are you sure you want to SUSPEND this order?")) {
                    $.post('api/totem-ajax.php', {
                        action: 'suspend_order',
                        machine_id: MACHINE_ID,
                        order_id: orderId
                    }, function(res) {
                        if(res.status === 'success') {
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    }, 'json');
                }
            });

            // 5. START PRODUCTION (New Logic)
            $(document).on('click', '.btn-start-order', function() {
                let orderId = $(this).data('id');
                // Optional: Check if operators are logged in before starting (handled by backend too)
                
                if(confirm("Start production for Order #" + orderId + "?")) {
                    $.post('api/totem-ajax.php', {
                        action: 'start_production',
                        machine_id: MACHINE_ID,
                        order_id: orderId
                    }, function(res) {
                        if(res.status === 'success') {
                            // Reload page to refresh PHP logic and show the Active Order View
                            location.reload(); 
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
            }, function(res) {
                if(res.status === 'success') {
                    renderOperators(res.operators);
                }
            }, 'json');
        }

        function renderOperators(operators) {
            $('.operator-slot').removeClass('active').addClass('empty').html('--');
            operators.forEach((op, index) => {
                if(index < 6) {
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
            return d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
    </script>
</body>
</html>