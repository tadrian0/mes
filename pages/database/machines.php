<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'MachineManager.php';
require_once INCLUDE_PATH . 'CountryManager.php';
require_once INCLUDE_PATH . 'PlantManager.php';
require_once INCLUDE_PATH . 'SectionManager.php';
require_once INCLUDE_PATH . 'CityManager.php';

$isAdmin = isAdmin();
$machineManager = new MachineManager($pdo);
$countryManager = new CountryManager($pdo);

$countries = $countryManager->listAll();

$plantManager = new PlantManager($pdo);
$sectionManager = new SectionManager($pdo);
$allPlants = $plantManager->listAll();
$allSections = $sectionManager->listAll();

$message = '';
$error = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');

    if (isset($_POST['create'])) {
        $plantId = !empty($_POST['plant_id']) ? (int)$_POST['plant_id'] : null;
        $sectionId = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;
        
        if ($machineManager->createMachine($_POST['name'], $_POST['status'], $_POST['capacity'], $_POST['last_maintenance_date'], $_POST['location'], $_POST['model'], $plantId, $sectionId)) {
            header("Location: $redirectUrl?msg=created"); exit;
        } else { $error = 'Error creating machine.'; }
    }

    if (isset($_POST['edit'])) {
        $plantId = !empty($_POST['edit_plant_id']) ? (int)$_POST['edit_plant_id'] : null;
        $sectionId = !empty($_POST['edit_section_id']) ? (int)$_POST['edit_section_id'] : null;

        if ($machineManager->updateMachine($_POST['machine_id'], $_POST['edit_name'], $_POST['edit_status'], $_POST['edit_capacity'], $_POST['edit_last_maintenance_date'], $_POST['edit_location'], $_POST['edit_model'], $plantId, $sectionId)) {
            header("Location: $redirectUrl?msg=updated"); exit;
        } else { $error = 'Error updating machine.'; }
    }

    if (isset($_POST['delete'])) {
        if ($machineManager->deleteMachine($_POST['machine_id'])) {
            header("Location: $redirectUrl?msg=deleted"); exit;
        } else { $error = 'Error deleting machine.'; }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = "Machine created successfully.";
    if ($_GET['msg'] === 'updated') $message = "Machine updated successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Machine deleted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - Machines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    
    <style>
        .dataTables_filter { display: none; } 
        .table td { vertical-align: middle; }
    </style>
</head>

<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Machines Registry</h1>

        <div id="apiErrorAlert" class="alert alert-danger d-none align-items-center" role="alert">
            <i class="fa-solid fa-lock me-2"></i>
            <div>
                <strong>Access Denied:</strong> Unable to fetch data. Your API Key may be missing or expired. 
                <a href="/mes/logout.php" class="alert-link">Please log in again.</a>
            </div>
        </div>
        
        <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body bg-light rounded">
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" class="form-control" id="globalSearch" placeholder="Search machine, model, location...">
                        </div>
                    </div>
                    <div class="col-md-8 text-end">
                        <button class="btn btn-outline-secondary me-1" id="refreshBtn"><i class="fa-solid fa-rotate"></i> Refresh Data</button>
                        <?php if ($isAdmin): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMachineModal"><i class="fa-solid fa-plus"></i> Add Machine</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Country</label>
                        <select class="form-select form-select-sm filter-input" id="filter_country" data-col-index="3">
                            <option value="">All Countries</option>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?= htmlspecialchars($c['Name']) ?>"><?= htmlspecialchars($c['Name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">City</label>
                        <select class="form-select form-select-sm filter-input" id="filter_city" data-col-index="4">
                            <option value="">All Cities</option>
                            </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Plant</label>
                        <select class="form-select form-select-sm filter-input" id="filter_plant" data-col-index="5">
                            <option value="">All Plants</option>
                            </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Section</label>
                        <select class="form-select form-select-sm filter-input" id="filter_section" data-col-index="6">
                            <option value="">All Sections</option>
                            </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="machinesTable" class="table table-hover table-striped w-100 mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Model</th>
                                <th>Country</th>
                                <th>City</th>
                                <th>Plant</th>
                                <th>Section</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <div class="modal fade" id="addMachineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add New Machine</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="post">
                    <div class="modal-body">
                        <div class="row mb-2">
                            <div class="col"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                            <div class="col"><label>Model</label><input type="text" name="model" class="form-control" required></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label>Plant</label>
                                <select name="plant_id" class="form-select">
                                    <option value="">Select...</option>
                                    <?php foreach ($allPlants as $p): ?><option value="<?= $p['PlantID'] ?>"><?= htmlspecialchars($p['Name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <label>Section</label>
                                <select name="section_id" class="form-select">
                                    <option value="">Select...</option>
                                    <?php foreach ($allSections as $s): ?><option value="<?= $s['SectionID'] ?>"><?= htmlspecialchars($s['Name']) . ' - ' . htmlspecialchars($s['PlantName']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col"><label>Status</label><select name="status" class="form-select"><option>Active</option><option>Inactive</option><option>Maintenance</option></select></div>
                            <div class="col"><label>Capacity (tons)</label><input type="number" step="0.01" name="capacity" class="form-control" required></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col"><label>Location (Grid)</label><input type="text" name="location" class="form-control" required></div>
                            <div class="col"><label>Last Maint.</label><input type="date" name="last_maintenance_date" class="form-control"></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" name="create" class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editMachineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Machine</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="machine_id" id="edit_machine_id">
                        <div class="row mb-2">
                            <div class="col"><label>Name</label><input type="text" name="edit_name" id="edit_name" class="form-control" required></div>
                            <div class="col"><label>Model</label><input type="text" name="edit_model" id="edit_model" class="form-control" required></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label>Plant</label>
                                <select name="edit_plant_id" id="edit_plant_id" class="form-select">
                                    <option value="">Select...</option>
                                    <?php foreach ($allPlants as $p): ?><option value="<?= $p['PlantID'] ?>"><?= htmlspecialchars($p['Name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <label>Section</label>
                                <select name="edit_section_id" id="edit_section_id" class="form-select">
                                    <option value="">Select...</option>
                                    <?php foreach ($allSections as $s): ?><option value="<?= $s['SectionID'] ?>"><?= htmlspecialchars($s['Name']) . ' - ' . htmlspecialchars($s['PlantName']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col"><label>Status</label><select name="edit_status" id="edit_status" class="form-select"><option>Active</option><option>Inactive</option><option>Maintenance</option></select></div>
                            <div class="col"><label>Capacity</label><input type="number" step="0.01" name="edit_capacity" id="edit_capacity" class="form-control" required></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col"><label>Location</label><input type="text" name="edit_location" id="edit_location" class="form-control" required></div>
                            <div class="col"><label>Last Maint.</label><input type="date" name="edit_last_maintenance_date" id="edit_last_maintenance_date" class="form-control"></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" name="edit" class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';

            var table = $('#machinesTable').DataTable({
                ajax: '<?= $siteBaseUrl ?>api/machines-fetch.php',
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
                        data: 'Status',
                        render: function(data) {
                            let badge = 'warning';
                            if(data === 'Active') badge = 'success';
                            if(data === 'Inactive') badge = 'secondary';
                            return `<span class="badge text-bg-${badge}">${data}</span>`;
                        }
                    },
                    { 
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            <?php if ($isAdmin): ?>
                            let rowData = encodeURIComponent(JSON.stringify(row));
                            return `
                                <button class="btn btn-sm btn-warning btn-edit" data-row="${rowData}"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete?');">
                                    <input type="hidden" name="machine_id" value="${row.MachineID}">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            `;
                            <?php else: ?>
                            return '';
                            <?php endif; ?>
                        }
                    }
                ],
                order: [[ 1, 'asc' ]],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: { search: "_INPUT_", searchPlaceholder: "Search records..." }
            });

            $('#machinesTable').on('error.dt', function(e, settings, techNote, message) {
                console.error('An error has been reported by DataTables: ', message);
                
                if (settings.jqXHR && (settings.jqXHR.status === 401 || settings.jqXHR.status === 403)) {
                    $('#apiErrorAlert').removeClass('d-none'); 
                    $('.card').addClass('opacity-50'); 
                } else {
                    alert('A data error occurred. Check console for details.');
                }
            });

            $('#globalSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            updateCascadingOptions();

            $('#filter_country, #filter_city, #filter_plant').on('change', function() {
                let id = $(this).attr('id');
                if (id === 'filter_country') {
                    $('#filter_city').val('');
                    $('#filter_plant').val('');
                    $('#filter_section').val('');
                } else if (id === 'filter_city') {
                    $('#filter_plant').val('');
                    $('#filter_section').val('');
                } else if (id === 'filter_plant') {
                    $('#filter_section').val('');
                }

                applyTableFilters();
                
                updateCascadingOptions();
            });

            $('#filter_section').on('change', function() {
                applyTableFilters();
            });

            function applyTableFilters() {
                $('.filter-input').each(function() {
                    let colIndex = $(this).data('col-index');
                    let val = $.fn.dataTable.util.escapeRegex($(this).val());
                    table.column(colIndex).search(val ? '^'+val+'$' : '', true, false);
                });
                table.draw();
            }

            function updateCascadingOptions() {
                let country = $('#filter_country').val();
                let city    = $('#filter_city').val();
                let plant   = $('#filter_plant').val();

                $.ajax({
                    url: '<?= $siteBaseUrl ?>api/get-filter-options.php',
                    data: { country: country, city: city, plant: plant },
                    dataType: 'json',
                    success: function(response) {
                        function populate(selector, data, currentValue) {
                            let $el = $(selector);
                            $el.empty();
                            $el.append('<option value="">All</option>');
                            data.forEach(function(item) {
                                let selected = (item === currentValue) ? 'selected' : '';
                                $el.append(`<option value="${item}" ${selected}>${item}</option>`);
                            });
                        }

                        if (!$('#filter_city').val()) {
                            populate('#filter_city', response.cities, '');
                        } else {
                            let current = $('#filter_city').val();
                            populate('#filter_city', response.cities, current);
                            if (response.cities.indexOf(current) === -1) $('#filter_city').val(''); 
                        }

                        let currentPlant = $('#filter_plant').val();
                        populate('#filter_plant', response.plants, currentPlant);
                        
                        let currentSection = $('#filter_section').val();
                        populate('#filter_section', response.sections, currentSection);
                    }
                });
            }

            $('#refreshBtn').on('click', function() {
                table.ajax.reload(null, false);
            });

            $('#machinesTable').on('click', '.btn-edit', function() {
                let rowData = JSON.parse(decodeURIComponent($(this).data('row')));
                $('#edit_machine_id').val(rowData.MachineID);
                $('#edit_name').val(rowData.Name);
                $('#edit_model').val(rowData.Model);
                $('#edit_plant_id').val(rowData.PlantID);
                $('#edit_section_id').val(rowData.SectionID);
                $('#edit_status').val(rowData.Status);
                $('#edit_capacity').val(rowData.Capacity);
                $('#edit_location').val(rowData.Location);
                $('#edit_last_maintenance_date').val(rowData.LastMaintenanceDate);
                new bootstrap.Modal(document.getElementById('editMachineModal')).show();
            });
        });
    </script>
</body>
</html>