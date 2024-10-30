(function(Cardinal, $) {
    try {
        function set_cruise_result(data, jwt) {
            $('#CardinalOneConnectResult').val(JSON.stringify({
                data: data,
                jwt: jwt
            }));
        }

        function permanent_error(message) {
            var msg = 'CardinalCommerce OneConnect: ' + message;
            console.error(msg);
            set_cruise_result({
                ActionCode: 'ERROR',
                ErrorDescription: message,
                PermanentFatal: true
            });
        }

        if (!$) {
            permanent_error('jQuery could not be loaded');
            return;
        }

        if (!Cardinal) {
            permanent_error('Cruise Songbird library could not be loaded');
            return;
        }

        Cardinal.OneConnect = {};

        Cardinal.configure({
            logging: {
                level: $('#CardinalOneConnectLoggingLevel').val()
            },
            CCA: {
                CustomContentID: 'merchant-content-wrapper'
            }
        });

        Cardinal.on('payments.setupComplete', function (setupCompleteData) {
            Cardinal.OneConnect.setupComplete = true;
        });

        Cardinal.on('payments.validated', function (data, jwt) {
            set_cruise_result(data, jwt);
            if (!Cardinal.OneConnect.setupComplete) {
                return;
            }
            $('form.checkout').submit();
        });

        function digits(name) {
            var el = $('#cardinalpm-' + name);
            return el.val().replace(/\D/g, '');
        }

        Cardinal.OneConnect.start = function(jwt) {
            var expiry = digits('card-expiry');
            var month = expiry.substring(0, 2);
            var year = expiry.substring(2);
            if (year.length == 2) {
                year = '20' + year;
            }
            var data = {
                Consumer: {
                    Account: {
                        AccountNumber: digits('card-number'),
                        ExpirationMonth: month,
                        ExpirationYear: year,
                        CardCode: digits('card-cvc')
                    }
                }
            };
            Cardinal.start('cca', data, jwt);
        };

        Cardinal.OneConnect.clear_results = function () {
            $('#CardinalOneConnectResult').val('');
        };

        Cardinal.setup('init', { jwt: $('#CardinalOneConnectJWT').val() });
    } catch (ex) {
        try {
            permanent_error(ex.toString());
        } catch (e) {}
        throw ex;
    }
})(window.Cardinal, window.jQuery);
