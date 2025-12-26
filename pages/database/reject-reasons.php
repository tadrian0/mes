<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'IsAdmin.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'RejectReasonManager.php';
require_once INCLUDE_PATH . 'CountryManager.php';
require_once INCLUDE_PATH . 'PlantManager.php';
require_once INCLUDE_PATH . 'SectionManager.php';

$isAdmin = isAdmin();
$rrManager = new RejectReasonManager($pdo);
$countryManager = new CountryManager($pdo);
$plantManager = new PlantManager($pdo);
$sectionManager = new SectionManager($pdo);

$countries = $countryManager->listAll();
$categories = $rrManager->getCategories();
$allPlants = $plantManager->listAll();
$allSections = $sectionManager->listAll();

$filterCountry = $_GET['filter_country'] ?? null;
$filterCity = $_GET['filter_city'] ?? null;
$filterPlant = isset($_GET['filter_plant']) && $_GET['filter_plant'] !== '' ? (int)$_GET['filter_plant'] : null;
$filterSection = isset($_GET['filter_section']) && $_GET['filter_section'] !== '' ? (int)$_GET['filter_section'] : null;
$filterCategory = isset($_GET['filter_category']) && $_GET['filter_category'] !== '' ? (int)$_GET['filter_category'] : null;

$reasons = $rrManager->listReasons($filterPlant, $filterSection, $filterCategory);

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET);

    if (isset($_POST['create'])) {
        $plantId = !empty($_POST['plant_id']) ? $_POST['plant_id'] : null;
        $sectionId = !empty($_POST['section_id']) ? $_POST['section_id'] : null;
        if ($rrManager->create($_POST['name'], (int)$_POST['category_id'], $plantId, $sectionId)) {
            header("Location: $redirect&msg=created"); exit;
        }
    }

    if (isset($_POST['edit'])) {
        $plantId = !empty($_POST['edit_plant_id']) ? $_POST['edit_plant_id'] : null;
        $sectionId = !empty($_POST['edit_section_id']) ? $_POST['edit_section_id'] : null;
        if ($rrManager->update((int)$_POST['reason_id'], $_POST['edit_name'], (int)$_POST['edit_category_id'], $plantId, $sectionId)) {
            header("Location: $redirect&msg=updated"); exit;
        }
    }

    if (isset($_POST['copy'])) {
        $targets = [];
        if (!empty($_POST['target_loc'])) {
            foreach ($_POST['target_loc'] as $val) {
                $parts = explode('|', $val); 
                $targets[] = [
                    'plant_id' => $parts[0] !== '0' ? $parts[0] : null,
                    'section_id' => $parts[1] !== '0' ? $parts[1] : null
                ];
            }
            if ($rrManager->replicateReason((int)$_POST['source_reason_id'], $targets)) {
                header("Location: $redirect&msg=copied"); exit;
            }
        }
    }

    if (isset($_POST['delete'])) {
        if ($rrManager->delete((int)$_POST['reason_id'])) {
            header("Location: $redirect&msg=deleted"); exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MES - Reject Reasons</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $siteBaseUrl ?>styles/backoffice.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
    <?php include INCLUDE_PATH . 'Sidebar.php'; ?>

    <div class="content">
        <h1>Reject Reasons</h1>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Operation successful.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light"><i class="fa-solid fa-filter me-1"></i> Filter</div>
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small">Country</label>
                        <select class="form-select form-select-sm" name="filter_country" id="f_country">
                            <option value="">All</option>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?= htmlspecialchars($c['Name']) ?>" <?= $filterCountry == $c['Name'] ? 'selected' : '' ?>><?= htmlspecialchars($c['Name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">City</label>
                        <select class="form-select form-select-sm" name="filter_city" id="f_city">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Plant</label>
                        <select class="form-select form-select-sm" name="filter_plant" id="f_plant">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Section</label>
                        <select class="form-select form-select-sm" name="filter_section" id="f_section">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Category</label>
                        <select class="form-select form-select-sm" name="filter_category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['CategoryID'] ?>" <?= $filterCategory == $cat['CategoryID'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['CategoryName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isAdmin): ?>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fa-solid fa-plus"></i> New Reason
            </button>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Reason Name</th>
                        <th>Category</th>
                        <th>Assigned Plant</th>
                        <th>Assigned Section</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reasons as $row): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($row['ReasonName']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['CategoryName']) ?></span></td>
                            <td>
                                <?php if ($row['PlantName']): ?>
                                    <?= htmlspecialchars($row['PlantName']) ?>
                                    <div class="small text-muted"><?= htmlspecialchars($row['CityName'] ?? '') ?></div>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">Global</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['SectionName'] ?? 'All Sections') ?></td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <button class="btn btn-sm btn-warning btn-edit" 
                                            data-id="<?= $row['ReasonID'] ?>"
                                            data-name="<?= htmlspecialchars($row['ReasonName']) ?>"
                                            data-cat="<?= $row['CategoryID'] ?>"
                                            data-plant="<?= $row['PlantID'] ?>"
                                            data-section="<?= $row['SectionID'] ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    
                                    <button class="btn btn-sm btn-info btn-copy text-white" 
                                            data-id="<?= $row['ReasonID'] ?>"
                                            data-name="<?= htmlspecialchars($row['ReasonName']) ?>">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>

                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this reason?');">
                                        <input type="hidden" name="reason_id" value="<?= $row['ReasonID'] ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">New Reason</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Reason Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select...</option>
                                <?php foreach ($categories as $cat): ?><option value="<?= $cat['CategoryID'] ?>"><?= htmlspecialchars($cat['CategoryName']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Plant (Optional)</label>
                            <select name="plant_id" class="form-select m_plant">
                                <option value="">Global / All Plants</option>
                                <?php foreach ($allPlants as $p): ?><option value="<?= $p['PlantID'] ?>"><?= htmlspecialchars($p['Name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Section (Optional)</label>
                            <select name="section_id" class="form-select m_section">
                                <option value="">All Sections</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" name="create" class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Reason</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="reason_id" id="e_id">
                        <div class="mb-3">
                            <label>Reason Name</label>
                            <input type="text" name="edit_name" id="e_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <select name="edit_category_id" id="e_cat" class="form-select" required>
                                <?php foreach ($categories as $cat): ?><option value="<?= $cat['CategoryID'] ?>"><?= htmlspecialchars($cat['CategoryName']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Plant</label>
                            <select name="edit_plant_id" id="e_plant" class="form-select m_plant">
                                <option value="">Global</option>
                                <?php foreach ($allPlants as $p): ?><option value="<?= $p['PlantID'] ?>"><?= htmlspecialchars($p['Name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Section</label>
                            <select name="edit_section_id" id="e_section" class="form-select m_section">
                                <option value="">All Sections</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" name="edit" class="btn btn-warning">Update</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="copyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Copy Reason</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="source_reason_id" id="c_id">
                        <p>Copying reason: <strong><span id="c_name_display"></span></strong></p>
                        
                        <label class="form-label">Select Target Locations:</label>
                        <div class="border p-2 bg-light rounded" style="max-height: 300px; overflow-y: auto;">
                            <?php 
                            $structure = [];
                            foreach ($allPlants as $p) $structure[$p['PlantID']] = ['name' => $p['Name'], 'sections' => []];
                            foreach ($allSections as $s) if(isset($structure[$s['PlantID']])) $structure[$s['PlantID']]['sections'][] = $s;
                            ?>
                            
                            <?php foreach ($structure as $pid => $data): ?>
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="target_loc[]" value="<?= $pid ?>|0" id="cp_<?= $pid ?>">
                                        <label class="form-check-label fw-bold" for="cp_<?= $pid ?>">
                                            <?= htmlspecialchars($data['name']) ?> (Plant-wide)
                                        </label>
                                    </div>
                                    <div class="ms-4 border-start ps-2">
                                        <?php foreach ($data['sections'] as $sec): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="target_loc[]" value="<?= $pid ?>|<?= $sec['SectionID'] ?>" id="cs_<?= $sec['SectionID'] ?>">
                                                <label class="form-check-label small" for="cs_<?= $sec['SectionID'] ?>">
                                                    <?= htmlspecialchars($sec['Name']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" name="copy" class="btn btn-info text-white">Replicate</button></div>
                </form>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let selCountry = "<?= $filterCountry ?>";
            let selCity = "<?= $filterCity ?>";
            let selPlant = "<?= $filterPlant ?>";
            let selSection = "<?= $filterSection ?>";

            function updateFilters() {
                let country = $('#f_country').val() || selCountry;
                let city = $('#f_city').val() || selCity;
                let plant = $('#f_plant').val() || selPlant;

                $.ajax({
                    url: '<?= $siteBaseUrl ?>api/get-filter-options.php',
                    data: { country: country, city: city, plant: plant },
                    dataType: 'json',
                    success: function(res) {
                        let $city = $('#f_city').empty().append('<option value="">All</option>');
                        res.cities.forEach(c => $city.append(new Option(c, c, false, c == selCity)));
                        
                        let $plant = $('#f_plant').empty().append('<option value="">All</option>');
                        res.plants.forEach(p => $plant.append(new Option(p, p, false, p == selPlant)));

                        let $sec = $('#f_section').empty().append('<option value="">All</option>');
                        res.sections.forEach(s => $sec.append(new Option(s, s, false, s == selSection)));
                    }
                });
            }

            updateFilters();
            $('#f_country, #f_city, #f_plant').change(function() {
                if(this.id == 'f_country') { selCity=''; selPlant=''; selSection=''; }
                if(this.id == 'f_city') { selPlant=''; selSection=''; }
                if(this.id == 'f_plant') { selSection=''; }
                selCountry = $('#f_country').val();
                selCity = $('#f_city').val();
                selPlant = $('#f_plant').val();
                updateFilters(); 
            });

            const sectionMap = {};
            <?php foreach ($allSections as $s): ?>
                if (!sectionMap[<?= $s['PlantID'] ?>]) sectionMap[<?= $s['PlantID'] ?>] = [];
                sectionMap[<?= $s['PlantID'] ?>].push({id: <?= $s['SectionID'] ?>, name: "<?= addslashes($s['Name']) ?>"});
            <?php endforeach; ?>

            function updateModalSections(plantSelect, sectionSelect) {
                let pid = $(plantSelect).val();
                let $sec = $(sectionSelect).empty().append('<option value="">All Sections</option>');
                if (pid && sectionMap[pid]) {
                    sectionMap[pid].forEach(s => $sec.append(new Option(s.name, s.id)));
                }
            }

            $('.m_plant').change(function() {
                updateModalSections(this, $(this).closest('form').find('.m_section'));
            });

            $('.btn-edit').click(function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let cat = $(this).data('cat');
                let plant = $(this).data('plant');
                let section = $(this).data('section');

                $('#e_id').val(id);
                $('#e_name').val(name);
                $('#e_cat').val(cat);
                $('#e_plant').val(plant || "").trigger('change');
                setTimeout(() => $('#e_section').val(section || ""), 100);

                new bootstrap.Modal(document.getElementById('editModal')).show();
            });

            $('.btn-copy').click(function() {
                $('#c_id').val($(this).data('id'));
                $('#c_name_display').text($(this).data('name'));
                new bootstrap.Modal(document.getElementById('copyModal')).show();
            });
        });
    </script>
</body>
</html>