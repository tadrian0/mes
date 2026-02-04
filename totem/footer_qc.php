<div id="qc" class="footer-section">
    <h3>Quality</h3>

    <div class="qc-recent-list mb-2" style="font-size: 0.8rem; min-height: 60px;">
        <?php if (!empty($data['recentRejects'])): ?>
            <?php foreach ($data['recentRejects'] as $reject): ?>
                <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                    <span class="text-truncate" style="max-width: 70%;" title="<?= htmlspecialchars($reject['CategoryName'] . ' - ' . $reject['ReasonName']) ?>">
                        <?= htmlspecialchars($reject['ReasonName']) ?>
                    </span>
                    <span class="fw-bold text-danger">-<?= $reject['Quantity'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-muted text-center pt-2">No recent rejects</div>
        <?php endif; ?>
    </div>

    <div class="d-flex gap-2">
        <button class="btn-qc w-50 btn btn-danger" id="btn-discard" style="height: auto; padding: 10px;">
            <i class="fa-solid fa-trash"></i> Discard
        </button>
        <button class="btn-qc w-50 btn btn-success" id="btn-recovery" style="height: auto; padding: 10px;">
            <i class="fa-solid fa-recycle"></i> Recovery
        </button>
    </div>
</div>

<!-- Discard Modal -->
<div class="modal fade" id="modal-discard" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-trash me-2"></i> Discard Parts</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="d-flex" style="height: 450px;">
                    <!-- Left Side -->
                    <div class="col-4 bg-light border-end p-3 d-flex flex-column">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity</label>
                            <input type="number" id="reject-qty" class="form-control form-control-lg text-center fw-bold" value="1" min="1">
                        </div>
                        <div class="flex-grow-1 overflow-auto">
                            <label class="form-label fw-bold text-muted small">CATEGORIES</label>
                            <div class="list-group" id="reject-categories">
                                <!-- Categories injected here -->
                                <div class="text-center p-3"><i class="fa-solid fa-spinner fa-spin"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="col-8 p-3 d-flex flex-column">
                        <div class="flex-grow-1 overflow-auto mb-3 border rounded p-2 bg-white" id="reject-reasons-container">
                            <label class="form-label fw-bold text-muted small sticky-top bg-white w-100">REASONS</label>
                            <div class="list-group list-group-flush" id="reject-reasons">
                                <!-- Reasons injected here -->
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
