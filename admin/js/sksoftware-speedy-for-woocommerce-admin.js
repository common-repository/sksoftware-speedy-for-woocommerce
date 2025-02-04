(function ($) {
    'use strict';

    var API_HOST = sksoftware_speedy_for_woocommerce_admin.api_host;

    $(function () {
        $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from_office_location_name')
            .closest('tr').hide();

        $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from').on('change', function () {
            if ($(this).val() === 'office') {
                $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from_office_location')
                    .closest('tr').show();
            } else if ($(this).val() === 'address') {
                $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from_office_location')
                    .closest('tr').hide();
            }
        })

        $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from').trigger('change');

        $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from_office_location').on('change', function () {
            var value = $(this).val();

            if (!value) {
                $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from_office_location_name').val('');

                return;
            }

            var label = $(this).find('option[value=' + value + ']').text();

            $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from_office_location_name').val(label);
        });

        if ($('#woocommerce_sksoftware_speedy_for_woocommerce_send_from_office_location').length > 0) {
            $('#woocommerce_sksoftware_speedy_for_woocommerce_send_from_office_location').select2({
                ajax: {
                    url: API_HOST + '/search', dataType: 'json', delay: 100, data: function (params) {
                        return {
                            filter: 'type = OFFICE', query: params.term, fqcn: 'App\\Entity\\SpeedyOffice',
                        };
                    }, processResults: function (data) {
                        var offices = [];

                        $.each(data, function (index, office) {
                            offices.push({
                                id: office.id, text: office.name + ' ' + office.address,
                            });
                        });

                        return {
                            results: offices,
                        };
                    }
                }, minimumInputLength: 2,
            });
        }

        $('#_sksoftware-speedy-for-woocommerce-form-field-delivery-type').on('change', function () {
            var value = $(this).val();

            if ('address' === value) {
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-city').parent().show();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-postal-code').parent().show();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-address-line-1').parent().show();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-address-line-2').parent().show();
                $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-apt').parent().hide();

                $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-office').parent().hide();
            }

            if ('office' === value) {
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-city').parent().hide();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-postal-code').parent().hide();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-address-line-1').parent().hide();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-address-line-2').parent().hide();
                $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-apt').parent().hide();

                $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-office').parent().show();
            }

            if ('apt' === value) {
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-city').parent().hide();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-postal-code').parent().hide();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-address-line-1').parent().hide();
                $('#_sksoftware-speedy-for-woocommerce-form-field-recipient-address-line-2').parent().hide();
                $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-office').parent().hide();

                $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-apt').parent().show();
            }
        });

        if ($('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-office').length > 0) {
            $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-office').select2({
                width: '50%', ajax: {
                    url: API_HOST + '/search', dataType: 'json', delay: 100, data: function (params) {
                        return {
                            filter: 'type = OFFICE', query: params.term, fqcn: 'App\\Entity\\SpeedyOffice',
                        };
                    }, processResults: function (data) {
                        var offices = [];

                        $.each(data, function (index, office) {
                            offices.push({
                                id: office.id, text: office.name + ' ' + office.address,
                            });
                        });

                        return {
                            results: offices,
                        };
                    }
                }, minimumInputLength: 2,
            });
        }

        if ($('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-apt').length > 0) {
            $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-apt').select2({
                width: '50%', ajax: {
                    url: API_HOST + '/search', dataType: 'json', delay: 100, data: function (params) {
                        return {
                            filter: 'type = APT', query: params.term, fqcn: 'App\\Entity\\SpeedyOffice',
                        };
                    }, processResults: function (data) {
                        var offices = [];

                        $.each(data, function (index, office) {
                            offices.push({
                                id: office.id, text: office.name + ' ' + office.address,
                            });
                        });

                        return {
                            results: offices,
                        };
                    }
                }, minimumInputLength: 2,
            });
        }

        $('#_sksoftware-speedy-for-woocommerce-form-field-delivery-type').trigger('change');

        $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-office').on('change', function () {
            $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-office-name').val($(this).find(":selected").text());
        });

        $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-office').trigger('change');

        $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-apt').on('change', function () {
            $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-apt-name').val($(this).find(":selected").text());
        });

        $('#_sksoftware-speedy-for-woocommerce-form-field-billing-sksoftware-speedy-apt').trigger('change');

        if ($('#_sksoftware-speedy-for-woocommerce-form-field-is-declared-amount').val() === 'false') {
            $('#_sksoftware-speedy-for-woocommerce-form-field-declared-amount').closest('p').hide();
        }
        $('#_sksoftware-speedy-for-woocommerce-form-field-is-declared-amount').on('change', function () {
            if ($(this).val() === 'false') {
                $('#_sksoftware-speedy-for-woocommerce-form-field-declared-amount').closest('p').hide();
            } else {
                $('#_sksoftware-speedy-for-woocommerce-form-field-declared-amount').closest('p').show();
            }
        });

        if ($('#_sksoftware-speedy-for-woocommerce-form-field-is-cash-on-delivery').val() === 'false') {
            $('#_sksoftware-speedy-for-woocommerce-form-field-cod-amount').closest('p').hide();
        }
        $('#_sksoftware-speedy-for-woocommerce-form-field-is-cash-on-delivery').on('change', function () {
            if ($(this).val() === 'false') {
                $('#_sksoftware-speedy-for-woocommerce-form-field-cod-amount').closest('p').hide();
            } else {
                $('#_sksoftware-speedy-for-woocommerce-form-field-cod-amount').closest('p').show();
            }
        });

        $('.sksoftware_speedy_for_woocommerce_print_shipment_label').attr('target', '_blank');

        $('[data-toggle="sksoftware-speedy-for-woocommerce-shipment-create"]').on('click', function () {
            $('#woocommerce-order-actions').find('[name="wc_order_action"]').val('sksoftware_speedy_for_woocommerce_shipment_create_order_action');
            $('#woocommerce-order-actions').find('button.save_order').trigger('click');
        });

        $('[data-toggle="sksoftware-speedy-for-woocommerce-shipment-delete"]').on('click', function () {
            $('#woocommerce-order-actions').find('[name="wc_order_action"]').val('sksoftware_speedy_for_woocommerce_shipment_delete_order_action');
            $('#woocommerce-order-actions').find('button.save_order').trigger('click');
        });

        $('[data-toggle="sksoftware-speedy-for-woocommerce-recalculate-shipping"]').on('click', function () {
            $('#woocommerce-order-actions').find('[name="wc_order_action"]').val('sksoftware_speedy_for_woocommerce_recalculate_shipping_order_action');
            $('#woocommerce-order-actions').find('button.save_order').trigger('click');
        });

        $('[id^=_sksoftware-speedy-for-woocommerce-form-field]').on('change, keyup, input', function () {
            $('[data-toggle="sksoftware-speedy-for-woocommerce-shipment-create"]').attr('disabled', true).text('Recalculate first!');
        });

        $(document).on('click', '[data-toggle="sksoftware-speedy-for-woocommerce-add-new-row"]', function () {
            var table = $(this).closest('table');
            var table_body = $(this).closest('table').find('tbody');
            var template = table_body.data('table-row-template');
            var current_index = table_body.data('current-index');

            if (0 !== current_index && !current_index) {
                current_index = 0;
            } else {
                current_index++;
            }

            template = template.replaceAll('%index%', current_index);

            table_body.data('current-index', current_index);

            table_body.append(template);

            $(document.body).trigger('wc-enhanced-select-init');

            table.find('tbody.sksoftware-settings-table-sortable').sortable('refresh');
        });

        $(document).on('click', '[data-toggle="sksoftware-speedy-for-woocommerce-delete-row"]', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('wc_backbone_modal_loaded', function () {
            $('.sksoftware-settings-table').find('tbody.sksoftware-settings-table-sortable').sortable({
                items: 'tr',
                cursor: 'move',
                axis: 'y',
                handle: 'td.sksoftware-settings-table-handle',
                scrollSensitivity: 40
            });
        });

        $(document).on('wc_backbone_modal_loaded', function () {
            $('#sksoftware-speedy-for-woocommerce-get-license').on('click', function () {
                var $button = $(this);

                $button.prop('disabled', true);

                var $errors_container = $('#sksoftware-speedy-for-woocommerce-errors');

                $errors_container.hide();
                $errors_container.html('');

                var data = {
                    action: 'sksoftware_speedy_for_woocommerce_start_free_trial_action',
                    email: $('#admin_email').val(),
                    accepted_terms: $('#accepted_terms').is(':checked'),
                    _ajax_nonce: $button.data('nonce'),
                };

                $.post(ajaxurl, data, function (response) {
                    if (response.success) {
                        window.location.href = window.location.href + '&sksoftware_speedy_for_woocommerce_start_free_trial_success=true';
                        return;
                    }

                    if (!response.data) {
                        window.location.href = window.location.href + '&sksoftware_speedy_for_woocommerce_start_free_trial_success=false';
                        return;
                    }

                    $errors_container.show();

                    response.data.forEach(function (violation) {
                        $errors_container.append('&bull; ' + violation.title + '<br>');
                    });
                }).always(function () {
                    $button.prop('disabled', false);
                });
            });
        });

        $("#sksoftware-speedy-for-woocommerce-start-free-trial").on('click', function (event) {
            event.preventDefault();

            $(this).WCBackboneModal({
                template: 'sksoftware-speedy-for-woocommerce-start-free-trial'
            });

        });
    });
})(jQuery);
