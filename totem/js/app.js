/**
 * Application State and Constants
 */
const RejectState = {
    data: null,
    selectedCategoryId: null,
    selectedReasonId: null
};

$(document).ready(function() {
    // Initial Data Fetch
    OperatorManager.fetch();
    setInterval(OperatorManager.fetch, 5000);

    // Initialize Event Listeners
    initEventListeners();
});

/**
 * Global Event Initializer
 * Moves event binding out of the main loop to reduce complexity.
 */
function initEventListeners() {
    // Login Events
    $('#btn-login-modal').click(AuthManager.openModal);
    $('#btn-perform-login').click(AuthManager.performLogin);
    $('#login-username, #login-password').keypress(e => { 
        if(e.which === 13) AuthManager.performLogin(); 
    });

    // Global Operator/Order Events (Delegated)
    $(document).on('click', '.btn-logout-op', OperatorManager.handleLogout);
    $(document).on('click', '.btn-start-order', ProductionManager.startOrder);

    // Reject Modal Events
    $('#btn-discard').click(RejectManager.openModal);
    $('#btn-submit-reject').click(RejectManager.submit);
}

/**
 * Authentication Module
 */
const AuthManager = {
    openModal() {
        $('#login-username').val('');
        $('#login-password').val('');
        $('#login-alert').addClass('d-none');
        new bootstrap.Modal(document.getElementById('loginModal')).show();
        setTimeout(() => $('#login-username').focus(), 500);
    },

    performLogin() {
        const user = $('#login-username').val();
        const pass = $('#login-password').val();

        if(!user || !pass) return AuthManager.showError("Enter credentials.");

        $.post('api/totem-ajax.php', {
            action: 'login',
            machine_id: MACHINE_ID,
            username: user,
            password: pass
        }, function(res) {
            if(res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
                OperatorManager.fetch(); 
            } else {
                AuthManager.showError(res.message);
            }
        }, 'json').fail(() => AuthManager.showError("Connection error."));
    },

    showError(msg) {
        $('#login-error-msg').text(msg);
        $('#login-alert').removeClass('d-none');
    }
};

/**
 * Operator Management Module
 */
const OperatorManager = {
    fetch() {
        $.post('api/totem-ajax.php', { 
            action: 'fetch_operators', 
            machine_id: MACHINE_ID 
        }, res => {
            if(res.status === 'success') OperatorManager.render(res.operators);
        }, 'json');
    },

    render(operators) {
        const $slots = $('.operator-slot');
        $slots.removeClass('active').addClass('empty')
              .html('<span class="text-muted" style="opacity:0.3; font-size:2rem;">+</span>');
            
        operators.forEach((op, index) => {
            if(index >= 6) return;
            
            const timeStr = new Date(op.LoginTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            $(`#slot-${index}`).removeClass('empty').addClass('active').html(`
                <div>
                    <div class="op-name">${op.OperatorUsername}</div>
                    <div class="op-time">${timeStr}</div>
                </div>
                <button class="btn-logout-op" data-id="${op.OperatorID}" data-name="${op.OperatorUsername}">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            `);
        });
    },

    handleLogout() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        if(confirm(`Log out ${name}?`)) {
            $.post('api/totem-ajax.php', {
                action: 'logout',
                machine_id: MACHINE_ID,
                operator_id: id
            }, res => {
                if(res.status === 'success') OperatorManager.fetch();
                else alert(res.message);
            }, 'json');
        }
    }
};

/**
 * Production & Order Logic
 */
const ProductionManager = {
    startOrder() {
        const orderId = $(this).data('id');
        if(confirm(`Start Order #${orderId}?`)) {
            $.post('api/totem-ajax.php', {
                action: 'start_production',
                machine_id: MACHINE_ID,
                order_id: orderId
            }, res => {
                if(res.status === 'success') location.reload();
                else alert(res.message);
            }, 'json');
        }
    }
};

/**
 * Discard / Reject Logic Module
 */
const RejectManager = {
    openModal() {
        new bootstrap.Modal(document.getElementById('modal-discard')).show();
        
        // Reset state
        RejectState.selectedCategoryId = null;
        RejectState.selectedReasonId = null;
        
        $('#reject-qty').val(1);
        $('#reject-notes').val('');
        $('#reject-reasons').html('<div class="text-center p-3 text-muted">Select a category</div>');
        $('#reject-categories .active').removeClass('active');
        
        RejectManager.updateSubmitButton();

        if (!RejectState.data) {
            RejectManager.fetchData();
        }
    },

    fetchData() {
        $.post('api/totem-ajax.php', { 
            action: 'fetch_reject_data', 
            machine_id: MACHINE_ID 
        }, function(res) {
            if (res.status === 'success') {
                RejectState.data = res;
                RejectManager.renderCategories();
            } else {
                alert('Failed to load discard options.');
            }
        }, 'json');
    },

    renderCategories() {
        const $container = $('#reject-categories').empty();

        RejectState.data.categories.forEach(cat => {
            let $el = $(`<a href="#" class="list-group-item list-group-item-action py-3">${cat.CategoryName}</a>`);
            $el.click(e => {
                e.preventDefault();
                $('#reject-categories .active').removeClass('active');
                $el.addClass('active');
                RejectState.selectedCategoryId = cat.CategoryID;
                RejectManager.renderReasons(cat.CategoryID);
            });
            $container.append($el);
        });
    },

    renderReasons(catId) {
        const $container = $('#reject-reasons').empty();
        RejectState.selectedReasonId = null;
        RejectManager.updateSubmitButton();

        const reasons = RejectState.data.reasons.filter(r => r.CategoryID == catId);
        if (reasons.length === 0) {
            return $container.html('<div class="text-center p-3 text-muted">No reasons found.</div>');
        }

        reasons.forEach(reason => {
            let $el = $(`<a href="#" class="list-group-item list-group-item-action py-3">${reason.ReasonName}</a>`);
            $el.click(e => {
                e.preventDefault();
                $('#reject-reasons .active').removeClass('active');
                $el.addClass('active');
                RejectState.selectedReasonId = reason.ReasonID;
                RejectManager.updateSubmitButton();
            });
            $container.append($el);
        });
    },

    updateSubmitButton() {
        const isDisabled = !RejectState.selectedCategoryId || !RejectState.selectedReasonId;
        $('#btn-submit-reject').prop('disabled', isDisabled);
    },

    submit() {
        const qty = $('#reject-qty').val();
        const notes = $('#reject-notes').val();

        if (!RejectState.selectedCategoryId || !RejectState.selectedReasonId || qty <= 0) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

        $.post('api/totem-ajax.php', {
            action: 'submit_reject',
            machine_id: MACHINE_ID,
            category_id: RejectState.selectedCategoryId,
            reason_id: RejectState.selectedReasonId,
            quantity: qty,
            notes: notes
        }, function(res) {
            if(res.status === 'success') {
                location.reload();
            } else {
                alert(res.message);
                $btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Confirm Discard');
            }
        }, 'json').fail(() => {
            alert('Connection error.');
            $btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Confirm Discard');
        });
    }
};