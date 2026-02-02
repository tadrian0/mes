$(document).ready(function() {
    fetchOperators();
    setInterval(fetchOperators, 5000);

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
                fetchOperators(); 
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
                if(res.status === 'success') fetchOperators();
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
                if(res.status === 'success') location.reload();
                else alert(res.message);
            }, 'json');
        }
    });

    function fetchOperators() {
        $.post('api/totem-ajax.php', { action: 'fetch_operators', machine_id: MACHINE_ID }, res => {
            if(res.status === 'success') renderOperators(res.operators);
        }, 'json');
    }

    function renderOperators(operators) {
        $('.operator-slot').removeClass('active').addClass('empty')
            .html('<span class="text-muted" style="opacity:0.3; font-size:2rem;">+</span>');
            
        operators.forEach((op, index) => {
            if(index < 6) {
                const date = new Date(op.LoginTime);
                const timeStr = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                $(`#slot-${index}`).removeClass('empty').addClass('active').html(`
                    <div>
                        <div class="op-name">${op.OperatorUsername}</div>
                        <div class="op-time">${timeStr}</div>
                    </div>
                    <button class="btn-logout-op" data-id="${op.OperatorID}" data-name="${op.OperatorUsername}">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                `);
            }
        });
    }

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
            let $el = $(`<a href="#" class="list-group-item list-group-item-action py-3">${cat.CategoryName}</a>`);
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
            let $el = $(`<a href="#" class="list-group-item list-group-item-action py-3">${reason.ReasonName}</a>`);
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
                location.reload(); // Refresh to show new log
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