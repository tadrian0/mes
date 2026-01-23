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
});