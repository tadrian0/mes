$(document).ready(function() {
    function fetchTotemState() {
        $.getJSON('api/totem-state.php', { machine_id: MACHINE_ID }, function(res) {
            if (res.error) {
                console.error(res.error);
                return;
            }
            renderUI(res);
        }).fail(function() {
            console.error("Failed to fetch totem state.");
        });
    }

    function renderUI(data) {
        // Machine panel & Header
        $('#ui-machine-name').text(data.machine.Name);
        $('#ui-panel-machine-name').text(data.machine.Name);

        let statusEl = $('#ui-panel-machine-status');
        statusEl.removeClass('status-working status-stopped status-maintenance');
        statusEl.addClass(data.statusClass);
        statusEl.text(data.machine.Status);

        $('#ui-panel-machine-model').text(data.machine.Model);

        // Production area
        let prodHtml = '';
        if (data.activeOrder) {
            let percent = 0;
            if (data.activeOrder.TargetQuantity > 0) {
                percent = (data.activeOrder.ProducedQuantity / data.activeOrder.TargetQuantity) * 100;
                percent = Math.min(100, Math.round(percent * 10) / 10);
            }
            let targetQty = parseInt(data.activeOrder.TargetQuantity).toLocaleString();
            let producedQty = parseInt(data.activeOrder.ProducedQuantity).toLocaleString();

            prodHtml = `
            <div class="panel h-100 d-flex flex-column">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3><i class="fa-solid fa-gear fa-spin me-2"></i> In Production</h3>
                    <span class="badge bg-success" style="padding:5px 10px; border-radius:4px; color:white; background:green;">RUNNING</span>
                </div>

                <div style="font-size:1.2rem; margin-bottom:10px;">
                    <strong>Order #${data.activeOrder.OrderID}</strong> - ${escapeHtml(data.activeOrder.ArticleName)}
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:20px;">
                    <div class="stat-box" style="background:#f8f9fa; padding:10px; border-radius:5px;">
                        <div style="color:#666; font-size:0.9rem;">Target</div>
                        <div style="font-size:1.5rem; font-weight:bold;">${targetQty}</div>
                    </div>
                    <div class="stat-box" style="background:#e0f2fe; padding:10px; border-radius:5px; border:1px solid #bae6fd;">
                        <div style="color:#0369a1; font-size:0.9rem;">Produced</div>
                        <div style="font-size:1.5rem; font-weight:bold; color:#0284c7;">${producedQty}</div>
                    </div>
                </div>

                <div class="progress-container">
                    <div class="progress-bar" style="width: ${percent}%;">${percent}%</div>
                </div>

                <div style="margin-top:auto; display:flex; gap:10px;">
                    <button class="large-btn secondary" style="background:#dc2626;">Stop / Finish Order</button>
                    <button class="large-btn secondary" style="background:#f59e0b;">Suspend Run</button>
                </div>
            </div>`;
        } else {
            let tableHtml = '';
            if (!data.plannedOrders || data.plannedOrders.length === 0) {
                tableHtml = `
                <div style="text-align:center; padding:40px; color:#999;">
                    <i class="fa-solid fa-clipboard-list fa-3x mb-3"></i>
                    <p>No planned orders available for this machine.</p>
                </div>`;
            } else {
                let rows = data.plannedOrders.map(po => {
                    let d = new Date(po.PlannedStartDate);
                    let dateStr = ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2);
                    let qty = parseInt(po.TargetQuantity).toLocaleString();
                    return `
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:10px; font-weight:bold;">#${po.OrderID}</td>
                            <td style="padding:10px;">${escapeHtml(po.ArticleName)}</td>
                            <td style="padding:10px;">${qty}</td>
                            <td style="padding:10px;">${dateStr}</td>
                            <td style="padding:10px;">
                                <button class="btn-start-order" data-id="${po.OrderID}" style="padding:8px 15px; font-size:0.9rem;">
                                    <i class="fa-solid fa-play"></i> Start
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');

                tableHtml = `
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
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
            }

            prodHtml = `
            <div class="panel h-100">
                <h3>Select Order to Start</h3>
                ${tableHtml}
            </div>`;
        }
        $('#ui-production-panel').replaceWith($(prodHtml).attr('id', 'ui-production-panel'));

        // Operators
        $('.operator-slot').removeClass('active').addClass('empty')
            .html('<span class="text-muted" style="opacity:0.3; font-size:2rem;">+</span>');

        data.operators.forEach((op, index) => {
            if(index < 6) {
                const date = new Date(op.LoginTime);
                const timeStr = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                $(`#slot-${index}`).removeClass('empty').addClass('active').html(`
                    <div>
                        <div class="op-name">${escapeHtml(op.OperatorUsername)}</div>
                        <div class="op-time">${timeStr}</div>
                    </div>
                    <button class="btn-logout-op" data-id="${op.OperatorID}" data-name="${escapeHtml(op.OperatorUsername)}">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                `);
            }
        });

        // Recent rejects
        let rejectsHtml = '';
        if (!data.recentRejects || data.recentRejects.length === 0) {
            rejectsHtml = '<div class="text-muted text-center pt-2 fst-italic">No recent rejects</div>';
        } else {
            rejectsHtml = data.recentRejects.map(reject => `
                <div class="d-flex justify-content-between border-bottom pb-1 mb-1 px-1">
                    <span class="text-truncate" style="max-width: 75%;" title="${escapeHtml(reject.CategoryName + ' - ' + reject.ReasonName)}">
                        ${escapeHtml(reject.ReasonName)}
                    </span>
                    <span class="fw-bold text-danger">-${reject.Quantity}</span>
                </div>
            `).join('');
        }
        $('#ui-recent-rejects').html(rejectsHtml);
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
             .toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Initial fetch and interval
    fetchTotemState();
    setInterval(fetchTotemState, 5000);

    // Login logic
    $('#btn-login-modal').click(function() {
        $('#login-username').val('');
        $('#login-password').val('');
        $('#login-alert').addClass('d-none');
        new bootstrap.Modal(document.getElementById('loginModal')).show();
        setTimeout(() => $('#login-username').focus(), 500);
    });

    $('#btn-perform-login').click(performLogin);
    $('#login-username, #login-password').keypress(e => { if(e.which == 13) performLogin(); });

    function performLogin() {
        const user = $('#login-username').val();
        const pass = $('#login-password').val();

        if(!user || !pass) return showError("Enter credentials.");

        $.post('api/totem-ajax.php', {
            action: 'login',
            machine_id: MACHINE_ID,
            username: user,
            password: pass
        }, function(res) {
            if(res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
                fetchTotemState();
            } else {
                showError(res.message);
            }
        }, 'json').fail(() => showError("Connection error."));
    }

    function showError(msg) {
        $('#login-error-msg').text(msg);
        $('#login-alert').removeClass('d-none');
    }

    $(document).on('click', '.btn-logout-op', function() {
        if(confirm('Log out ' + $(this).data('name') + '?')) {
            $.post('api/totem-ajax.php', {
                action: 'logout',
                machine_id: MACHINE_ID,
                operator_id: $(this).data('id')
            }, res => {
                if(res.status === 'success') fetchTotemState();
                else alert(res.message);
            }, 'json');
        }
    });

    $(document).on('click', '.btn-start-order', function() {
        let orderId = $(this).data('id');
        if(confirm("Start Order #" + orderId + "?")) {
            $.post('api/totem-ajax.php', {
                action: 'start_production',
                machine_id: MACHINE_ID,
                order_id: orderId
            }, res => {
                if(res.status === 'success') fetchTotemState();
                else alert(res.message);
            }, 'json');
        }
    });

    // --- Discard Logic ---
    let rejectData = null;
    let selectedCategoryId = null;
    let selectedReasonId = null;

    $('#btn-discard').click(function() {
        new bootstrap.Modal(document.getElementById('modal-discard')).show();
        // Reset state
        selectedCategoryId = null;
        selectedReasonId = null;
        $('#reject-qty').val(1);
        $('#reject-notes').val('');
        $('#reject-reasons').html('<div class="text-center p-3 text-muted">Select a category</div>');
        $('#reject-categories .active').removeClass('active');
        updateSubmitButton();

        if (!rejectData) {
            fetchRejectData();
        }
    });

    function fetchRejectData() {
        $.post('api/totem-ajax.php', { action: 'fetch_reject_data', machine_id: MACHINE_ID }, function(res) {
            if (res.status === 'success') {
                rejectData = res;
                renderCategories();
            } else {
                alert('Failed to load discard options.');
            }
        }, 'json');
    }

    function renderCategories() {
        const $container = $('#reject-categories');
        $container.empty();

        rejectData.categories.forEach(cat => {
            let $el = $(`<a href="#" class="list-group-item list-group-item-action py-3">${escapeHtml(cat.CategoryName)}</a>`);
            $el.click(function(e) {
                e.preventDefault();
                $('#reject-categories .active').removeClass('active');
                $(this).addClass('active');
                selectedCategoryId = cat.CategoryID;
                renderReasons(cat.CategoryID);
            });
            $container.append($el);
        });
    }

    function renderReasons(catId) {
        const $container = $('#reject-reasons');
        $container.empty();
        selectedReasonId = null;
        updateSubmitButton();

        const reasons = rejectData.reasons.filter(r => r.CategoryID == catId);

        if (reasons.length === 0) {
            $container.html('<div class="text-center p-3 text-muted">No reasons found for this category.</div>');
            return;
        }

        reasons.forEach(reason => {
            let $el = $(`<a href="#" class="list-group-item list-group-item-action py-3">${escapeHtml(reason.ReasonName)}</a>`);
            $el.click(function(e) {
                e.preventDefault();
                $('#reject-reasons .active').removeClass('active');
                $(this).addClass('active');
                selectedReasonId = reason.ReasonID;
                updateSubmitButton();
            });
            $container.append($el);
        });
    }

    function updateSubmitButton() {
        $('#btn-submit-reject').prop('disabled', !selectedCategoryId || !selectedReasonId);
    }

    $('#btn-submit-reject').click(function() {
        const qty = $('#reject-qty').val();
        const notes = $('#reject-notes').val();

        if (!selectedCategoryId || !selectedReasonId || qty <= 0) return;

        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

        $.post('api/totem-ajax.php', {
            action: 'submit_reject',
            machine_id: MACHINE_ID,
            category_id: selectedCategoryId,
            reason_id: selectedReasonId,
            quantity: qty,
            notes: notes
        }, function(res) {
            if(res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('modal-discard')).hide();
                fetchTotemState();
                $('#btn-submit-reject').prop('disabled', false).html('<i class="fa-solid fa-check"></i> Confirm Discard');
            } else {
                alert(res.message);
                $('#btn-submit-reject').prop('disabled', false).html('<i class="fa-solid fa-check"></i> Confirm Discard');
            }
        }, 'json').fail(function() {
            alert('Connection error.');
            $('#btn-submit-reject').prop('disabled', false).html('<i class="fa-solid fa-check"></i> Confirm Discard');
        });
    });
});
