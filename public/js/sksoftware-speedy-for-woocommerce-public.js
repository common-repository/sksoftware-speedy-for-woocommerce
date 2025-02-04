(function ($) {
    'use strict';

    var API_URL = sksoftware_speedy_for_woocommerce_public.api_key;

    function initOfficePicker() {
        $('#sksoftware_speedy_office').selectWoo({
            width: '100%',
            ajax: {
                url: API_URL + '/search',
                dataType: 'json',
                delay: 100,
                data: function (params) {
                    return {
                        filter: 'type = OFFICE',
                        query: params.term,
                        fqcn: 'App\\Entity\\SpeedyOffice',
                    };
                },
                processResults: function (data) {
                    var offices = [];

                    $.each(data, function (index, office) {
                        offices.push({
                            id: office.id,
                            text: office.name + ' ' + office.address,
                        });
                    });

                    return {
                        results: offices,
                    };
                }
            },
            minimumInputLength: 2,
        });
    }

    function initAptPicker() {
        $('#sksoftware_speedy_apt').selectWoo({
            width: '100%',
            ajax: {
                url: API_URL + '/search',
                dataType: 'json',
                delay: 100,
                data: function (params) {
                    return {
                        filter: 'type = APT',
                        query: params.term,
                        fqcn: 'App\\Entity\\SpeedyOffice',
                    };
                },
                processResults: function (data) {
                    var offices = [];
                    $.each(data, function (index, office) {
                        offices.push({
                            id: office.id,
                            text: office.name + ' ' + office.address,
                        });
                    });

                    return {
                        results: offices,
                    };
                }
            },
            minimumInputLength: 2,
        });
    }

    $(
        function () {
            // Update checkout on payment method change
            $(document).on(
                'change',
                '[name="payment_method"]',
                function () {
                    $('body').trigger('update_checkout', {update_shipping_method: true});
                }
            );

            initOfficePicker();
            initAptPicker();

            // Show loader on checkout update if our fields exist
            $(document.body).on('update_checkout', () => {
                if ($( '.sksoftware-woocommerce-speedy-office-fragment' ).length > 0) {
                    $( '.sksoftware-woocommerce-speedy-office-fragment' ).block({
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    });
                }

                if ($( '.sksoftware-woocommerce-speedy-apt-fragment' ).length > 0) {
                    $( '.sksoftware-woocommerce-speedy-apt-fragment' ).block({
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    });
                }
            });

            // Reload office/apt picker on updated checkout
            $(document.body).on('updated_checkout updated_shipping_method', function () {
                initOfficePicker();
                initAptPicker();
            });

            // Add validation for office
            $(document.body).on('blur change', '#sksoftware_speedy_office', function () {
                var wrapper = $('.speedy-office-field.form-row');

                if (!$(this).val()) {
                    wrapper.addClass('woocommerce-invalid');
                } else {
                    wrapper.addClass('woocommerce-validated');
                }
            });

            // Add validation for apt
            $(document.body).on('blur change', '#sksoftware_speedy_apt', function () {
                var wrapper = $('.speedy-apt-field.form-row');

                if (!$(this).val()) {
                    wrapper.addClass('woocommerce-invalid');
                } else {
                    wrapper.addClass('woocommerce-validated');
                }
            });

            // Update office hidden fields on select2 change
            $(document.body).on('select2:select', '#sksoftware_speedy_office', function (event) {
                $('#billing_sksoftware_speedy_office').val(event.params.data.id);
                $('#shipping_sksoftware_speedy_office').val(event.params.data.id);
                $('#billing_sksoftware_speedy_office_name').val(event.params.data.text);
                $('#shipping_sksoftware_speedy_office_name').val(event.params.data.text);

                $(document.body).trigger('update_checkout', {update_shipping_method: true});
            });

            // Update apt hidden fields on select2 change
            $(document.body).on('select2:select', '#sksoftware_speedy_apt', function (event) {
                $('#billing_sksoftware_speedy_apt').val(event.params.data.id);
                $('#shipping_sksoftware_speedy_apt').val(event.params.data.id);
                $('#billing_sksoftware_speedy_apt_name').val(event.params.data.text);
                $('#shipping_sksoftware_speedy_apt_name').val(event.params.data.text);

                $(document.body).trigger('update_checkout', {update_shipping_method: true});
            });
        }
    );

})(jQuery);
