
(function($) {

    // Constant values
    var CC_WOOCOMMERCE_OBJ = 'CardinalCruiseWooCommerce';

    var defaultCardinalOpts = {
        bindFormSubmit: false,
        bindSubmitButton: true,
        disableSubmitUntilStarted: true,
    };

    function WooCommerceSongbirdJQuery(form, opts, configureOpts) {
        var cardinalOpts = Object.assign({}, opts.Options, defaultCardinalOpts);
        var elements = Object.assign({}, opts.Elements);

        var responseJWT_hidden = $(elements.responseJWT, form);

        var params = {
            bindFormSubmit: cardinalOpts.bindFormSubmit,
            bindSubmitButton: cardinalOpts.bindSubmitButton,
            disableSubmitUntilStarted: cardinalOpts.disableSubmitUntilStarted,

            serverJWT: elements.serverJWT,
            responseJWT: elements.responseJWT,

            cardNum: elements.cardNum,
            cardExp: elements.cardExp,
            cardExpDelim: elements.cardExpDelim,
            cardExpMonth: elements.cardExpMonth,
            cardExpYear: elements.cardExpYear,
            cardCode: elements.cardCode,

            submitButton: elements.submitButton
        };

        var cc = form.CardinalCruise(params, configureOpts);

        form.on($.CardinalCruise.PAYMENTS_VALIDATED_SUCCESS, function(event, obj) {
            setTimeout(function() {
                // Populate the hidden value
                $(elements.responseJWT, form).val(obj.jwt);

                /*
                if (responseJWT_hidden != null && responseJWT_hidden.length) {
                    responseJWT_hidden[0].setAttribute('value', obj.jwt);
                }
                */

                // Resubmit the form
                form.submit();

            });
        });

        // Handle WooCommerce submit event (after local form validation)
        form.on('checkout_place_order', function() {
            return cc.beginSubmission();
        });

        return {
            CardinalCruise: function() {
                return cc;
            }
        }
    }

    window.CardinalCruiseWooCommerce = function(form, opts, configureOpts) {
        var form = $(form);

        var obj = form.data(CC_WOOCOMMERCE_OBJ);

        if (!obj) {
            opts = $.extend({}, opts);
            configureOpts = $.extend({}, configureOpts);

            obj = new WooCommerceSongbirdJQuery(form, opts, configureOpts);
            form.data(CC_WOOCOMMERCE_OBJ, obj);
        }

        return obj;
    };

}(window.jQuery));
