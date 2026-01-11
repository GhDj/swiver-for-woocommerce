(function($) {
    'use strict';

    $(document).ready(function() {
        // Sync button click
        $('#swiver-sync-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var token = $('#swiver_token').val();

            if (!token) {
                showMessage('error', swiverAjax.strings.errorEmptyToken);
                return;
            }

            $btn.prop('disabled', true);
            $btn.data('original-text', $btn.text());
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>' + swiverAjax.strings.syncing);

            $.ajax({
                url: swiverAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'swiver_sync',
                    nonce: swiverAjax.nonce,
                    token: token
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showMessage('error', response.data.message);
                        $btn.prop('disabled', false);
                        $btn.text($btn.data('original-text'));
                    }
                },
                error: function() {
                    showMessage('error', swiverAjax.strings.errorGeneric);
                    $btn.prop('disabled', false);
                    $btn.text($btn.data('original-text'));
                }
            });
        });

        // Resync button click
        $('#swiver-resync-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);

            $btn.prop('disabled', true);
            $btn.data('original-text', $btn.html());
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>' + swiverAjax.strings.resyncing);

            $.ajax({
                url: swiverAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'swiver_resync',
                    nonce: swiverAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showMessage('error', response.data.message);
                        $btn.prop('disabled', false);
                        $btn.html($btn.data('original-text'));
                    }
                },
                error: function() {
                    showMessage('error', swiverAjax.strings.errorGeneric);
                    $btn.prop('disabled', false);
                    $btn.html($btn.data('original-text'));
                }
            });
        });

        // Disconnect button click
        $('#swiver-disconnect-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);

            if (!confirm(swiverAjax.strings.confirmDisconnect)) {
                return;
            }

            $btn.prop('disabled', true);
            $btn.data('original-text', $btn.text());
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>' + swiverAjax.strings.disconnecting);

            $.ajax({
                url: swiverAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'swiver_disconnect',
                    nonce: swiverAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showMessage('error', response.data.message);
                        $btn.prop('disabled', false);
                        $btn.text($btn.data('original-text'));
                    }
                },
                error: function() {
                    showMessage('error', swiverAjax.strings.errorGeneric);
                    $btn.prop('disabled', false);
                    $btn.text($btn.data('original-text'));
                }
            });
        });

        // Add all taxes to WooCommerce button click
        $('#swiver-add-all-taxes-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);

            if (!confirm(swiverAjax.strings.confirmAddAllTaxes)) {
                return;
            }

            $btn.prop('disabled', true);
            $btn.data('original-text', $btn.text());
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>' + swiverAjax.strings.addingTaxes);

            $.ajax({
                url: swiverAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'swiver_add_all_taxes',
                    nonce: swiverAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showMessage('error', response.data.message);
                        $btn.prop('disabled', false);
                        $btn.text($btn.data('original-text'));
                    }
                },
                error: function() {
                    showMessage('error', swiverAjax.strings.errorGeneric);
                    $btn.prop('disabled', false);
                    $btn.text($btn.data('original-text'));
                }
            });
        });

        // Add tax to WooCommerce button click
        $(document).on('click', '.swiver-add-tax-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var taxId = $btn.data('tax-id');
            var taxRate = $btn.data('tax-rate');
            var taxName = $btn.data('tax-name');

            $btn.prop('disabled', true);
            $btn.data('original-text', $btn.text());
            $btn.html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: swiverAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'swiver_add_tax',
                    nonce: swiverAjax.nonce,
                    tax_id: taxId,
                    tax_rate: taxRate,
                    tax_name: taxName
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        // Replace button with success badge and WC tax name
                        var html = '<span class="badge bg-success">' + swiverAjax.strings.matched + '</span>';
                        if (response.data.wc_name) {
                            html += ' <small class="text-muted"><span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span> ' + response.data.wc_name + '</small>';
                        }
                        $btn.closest('.d-flex.align-items-center').html(html);
                    } else {
                        showMessage('error', response.data.message);
                        $btn.prop('disabled', false);
                        $btn.text($btn.data('original-text'));
                    }
                },
                error: function() {
                    showMessage('error', swiverAjax.strings.errorGeneric);
                    $btn.prop('disabled', false);
                    $btn.text($btn.data('original-text'));
                }
            });
        });

        // Show message function
        function showMessage(type, message) {
            var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            var $alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade show mt-3" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');

            $('#swiver-messages').html($alert);

            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $alert.alert('close');
            }, 5000);
        }
    });
})(jQuery);
