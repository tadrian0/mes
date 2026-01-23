<div class="container mt-5" style="max-width: 1400px;">
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
                        <?php foreach ($data['countries'] as $c): ?>
                            <option value="<?= htmlspecialchars($c['Name']) ?>"><?= htmlspecialchars($c['Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><select class="form-select filter-input" id="filter_city" data-col-index="4"><option value="">All Cities</option></select></div>
                <div class="col-md-2"><select class="form-select filter-input" id="filter_plant" data-col-index="5"><option value="">All Plants</option></select></div>
                <div class="col-md-2"><select class="form-select filter-input" id="filter_section" data-col-index="6"><option value="">All Sections</option></select></div>
            </div>

            <div class="table-responsive">
                <table id="totemSelectTable" class="table table-hover table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Machine</th><th>Model</th><th>Country</th><th>City</th><th>Plant</th><th>Section</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>