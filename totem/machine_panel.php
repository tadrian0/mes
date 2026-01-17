<aside id="machine-panel">
    <div class="panel h-100">
        <h3>Machine Info</h3>
        <div class="machine-detail">
            <span class="label">Name:</span>
            <span class="value">
                <?= htmlspecialchars($machine['Name']) ?>
            </span>
        </div>
        <div class="machine-detail">
            <span class="label">Status:</span>
            <span class="value <?= $statusClass ?>">
                <?= htmlspecialchars($machine['Status']) ?>
            </span>
        </div>
        <div class="machine-detail">
            <span class="label">Model:</span>
            <span class="value">
                <?= htmlspecialchars($machine['Model']) ?>
            </span>
        </div>
        <div class="mt-auto">
            <button class="secondary w-100">Maintenance</button>
        </div>
    </div>
</aside>