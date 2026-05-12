<div id="header">
    <div class="auth-btn-wrapper">
        <button id="btn-login-modal" class="primary"><i class="fa-solid fa-id-card"></i> Operator Login</button>
    </div>

    <div id="operators">
        <div class="operator-slot empty" id="slot-0">--</div>
        <div class="operator-slot empty" id="slot-1">--</div>
        <div class="operator-slot empty" id="slot-2">--</div>
        <div class="operator-slot empty" id="slot-3">--</div>
        <div class="operator-slot empty" id="slot-4">--</div>
        <div class="operator-slot empty" id="slot-5">--</div>
    </div>

    <div id="header-right">
        <div id="logo">MES TOTEM</div>
        <small id="ui-machine-name">
            Loading...
        </small>
    </div>
</div>

<div id="main">
    <div id="production-area">
        <div class="panel h-100" id="ui-production-panel">
            <h3>Loading...</h3>
        </div>
    </div>

    <aside id="machine-panel">
        <div class="panel h-100">
            <h3>Machine Info</h3>
            <div class="machine-detail">
                <span class="label">Name:</span>
                <span class="value" id="ui-panel-machine-name">
                    Loading...
                </span>
            </div>
            <div class="machine-detail">
                <span class="label">Status:</span>
                <span class="value" id="ui-panel-machine-status">
                    Loading...
                </span>
            </div>
            <div class="machine-detail">
                <span class="label">Model:</span>
                <span class="value" id="ui-panel-machine-model">
                    Loading...
                </span>
            </div>
            <div class="mt-auto">
                <button class="secondary w-100">Maintenance</button>
            </div>
        </div>
    </aside>
</div>

<div id="footer">
    <div id="qc" class="footer-section d-flex flex-column h-100">
        <div class="d-flex justify-content-between align-items-center mb-2 w-100">
            <h3 class="mb-0">Quality</h3>
            <div class="d-flex gap-1">
                <button class="btn btn-danger btn-sm shadow-sm" id="btn-discard" title="Discard / Reject" style="width: 40px; height: 32px;">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <button class="btn btn-success btn-sm shadow-sm" id="btn-recovery" title="Recovery / Rework" style="width: 40px; height: 32px;">
                    <i class="fa-solid fa-recycle"></i>
                </button>
            </div>
        </div>

        <div class="qc-recent-list flex-grow-1 overflow-auto border rounded bg-white p-1" style="font-size: 0.8rem;" id="ui-recent-rejects">
            <div class="text-muted text-center pt-2 fst-italic">Loading...</div>
        </div>
    </div>

    <div class="modal fade" id="modal-discard" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-trash me-2"></i> Discard Parts</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="d-flex" style="height: 450px;">
                        <div class="col-4 bg-light border-end p-3 d-flex flex-column">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" id="reject-qty" class="form-control form-control-lg text-center fw-bold" value="1" min="1">
                            </div>
                            <div class="flex-grow-1 overflow-auto">
                                <label class="form-label fw-bold text-muted small">CATEGORIES</label>
                                <div class="list-group" id="reject-categories">
                                    <div class="text-center p-3"><i class="fa-solid fa-spinner fa-spin"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-8 p-3 d-flex flex-column">
                            <div class="flex-grow-1 overflow-auto mb-3 border rounded p-2 bg-white" id="reject-reasons-container">
                                <label class="form-label fw-bold text-muted small sticky-top bg-white w-100">REASONS</label>
                                <div class="list-group list-group-flush" id="reject-reasons">
                                    <div class="text-center p-3 text-muted">Select a category</div>
                                </div>
                            </div>

                            <div class="mb-0">
                                <textarea id="reject-notes" class="form-control" rows="2" placeholder="Extra information (optional)..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btn-submit-reject" class="btn btn-danger px-4" disabled>
                        <i class="fa-solid fa-check"></i> Confirm Discard
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="raw-material" class="footer-section">
        <h3>Material</h3>
        <button><i class="fa-solid fa-barcode"></i> Scan Batch</button>
    </div>
    <div id="labels" class="footer-section">
        <h3>Logistics</h3>
        <button><i class="fa-solid fa-print"></i> Print Label</button>
    </div>
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
