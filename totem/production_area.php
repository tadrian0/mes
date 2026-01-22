<div id="production-area">
    
    <?php if ($activeOrder): ?>
        <?php 
            $percent = 0;
            if ($activeOrder['TargetQuantity'] > 0) {
                $percent = ($activeOrder['ProducedQuantity'] / $activeOrder['TargetQuantity']) * 100;
                $percent = min(100, round($percent, 1));
            }
        ?>
        <div class="panel h-100 d-flex flex-column">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3><i class="fa-solid fa-gear fa-spin me-2"></i> In Production</h3>
                <span class="badge bg-success" style="padding:5px 10px; border-radius:4px; color:white; background:green;">RUNNING</span>
            </div>
            
            <div style="font-size:1.2rem; margin-bottom:10px;">
                <strong>Order #<?= $activeOrder['OrderID'] ?></strong> - <?= htmlspecialchars($activeOrder['ArticleName']) ?>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:20px;">
                <div class="stat-box" style="background:#f8f9fa; padding:10px; border-radius:5px;">
                    <div style="color:#666; font-size:0.9rem;">Target</div>
                    <div style="font-size:1.5rem; font-weight:bold;"><?= number_format($activeOrder['TargetQuantity'], 0) ?></div>
                </div>
                <div class="stat-box" style="background:#e0f2fe; padding:10px; border-radius:5px; border:1px solid #bae6fd;">
                    <div style="color:#0369a1; font-size:0.9rem;">Produced</div>
                    <div style="font-size:1.5rem; font-weight:bold; color:#0284c7;"><?= number_format($activeOrder['ProducedQuantity'], 0) ?></div>
                </div>
            </div>

            <div class="progress-container">
                <div class="progress-bar" style="width: <?= $percent ?>%;"><?= $percent ?>%</div>
            </div>
            
            <div style="margin-top:auto; display:flex; gap:10px;">
                <button class="large-btn secondary" style="background:#dc2626;">Stop / Finish Order</button>
                <button class="large-btn secondary" style="background:#f59e0b;">Suspend Run</button>
            </div>
        </div>

    <?php else: ?>
        <div class="panel h-100">
            <h3>Select Order to Start</h3>
            
            <?php if (empty($plannedOrders)): ?>
                <div style="text-align:center; padding:40px; color:#999;">
                    <i class="fa-solid fa-clipboard-list fa-3x mb-3"></i>
                    <p>No planned orders available for this machine.</p>
                </div>
            <?php else: ?>
                <div style="overflow-y:auto; height:85%;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f3f4f6; text-align:left;">
                                <th style="padding:10px;">ID</th>
                                <th style="padding:10px;">Article</th>
                                <th style="padding:10px;">Qty</th>
                                <th style="padding:10px;">Date</th>
                                <th style="padding:10px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plannedOrders as $po): ?>
                                <tr style="border-bottom:1px solid #eee;">
                                    <td style="padding:10px; font-weight:bold;">#<?= $po['OrderID'] ?></td>
                                    <td style="padding:10px;"><?= htmlspecialchars($po['ArticleName']) ?></td>
                                    <td style="padding:10px;"><?= number_format($po['TargetQuantity'], 0) ?></td>
                                    <td style="padding:10px;"><?= date('d/m', strtotime($po['PlannedStartDate'])) ?></td>
                                    <td style="padding:10px;">
                                        <button class="btn-start-order" data-id="<?= $po['OrderID'] ?>" style="padding:8px 15px; font-size:0.9rem;">
                                            <i class="fa-solid fa-play"></i> Start
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>