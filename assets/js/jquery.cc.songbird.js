
(function($, Cardinal) {
    var JWT_REGEX = /^[A-Za-z0-9-_=]+\.[A-Za-z0-9-_=]+\.?[A-Za-z0-9-_.+/=]*$/;

    $.extend({ CardinalCruise: {} });

    // Constant values
    var CC_OBJ = 'CardinalCruise';

    var CC_FORM_STATE = 'CardinalCruise.form.state';

    var CC_FORM_STATE_NONE = 'Cardinal.form.none';
    var CC_FORM_STATE_AWAITING_START_RESULT = 'Cardinal.form.awaitingStartResult';
    var CC_FORM_STATE_AWAITING_RESUBMIT = 'Cardinal.form.awaitingResubmit';

    // NONE -> form:onsubmit -> AWAITING_START_RESULT -> setupInit -> AWAITING_RESUBMIT -> form:onsubmit ->
    // form:onsubmit AWAITING_START_RESULT? -> warn and fail submission
    // form:onsubmit AWAITING_RESUBMIT -> NONE -> allow submission

    // Events
    var PAYMENTS_VALIDATED_SUCCESS = $.CardinalCruise.PAYMENTS_VALIDATED_SUCCESS = 'Cardinal.payments.validated.success';
    var PAYMENTS_VALIDATED_FAILURE = $.CardinalCruise.PAYMENTS_VALIDATED_FAILURE = 'Cardinal.payments.validated.failure';

    var defaultOpts = {
        bindFormSubmit: true,

        bindSubmitButton: false,
        submitButton: "input[type=submit]",
        disableSubmitUntilStarted: true,

        cardExpDelim: '/',

        orderDetails: {
        }
    }

    function debug(msg) {
        console.debug && console.debug('CardinalCruise JQuery Plugin: debug: ' + msg);
    }

    function warn(msg) {
        console.warn && console.warn('CardinalCruise JQuery Plugin: ' + msg);
    }

    function SongbirdJQuery(form, opts, configureOpts) {

        var submit = $(opts.submitButton, form);
        var responseJWT = $(opts.responseJWT, form);

        function splitCardExp(expCombined) {
                var delim = opts.cardExpDelim || defaultOpts.cardExpDelim;
                var parts = getValue(opts.cardExp).split(delim);
                return (parts.length === 2) ? parts : null;
        }

        function getValue(selector, defaultValue) {
            var els = $(selector, form);
            return els.length ? els.val() : (('undefined' !== typeof defaultValue) ? defaultValue : '');
        }

        function getCardExpDate() {
            var expCombined = $(opts.cardExp);
            var parts = (expCombined.length) ? splitCardExp() :
                [getValue(opts.cardExpMonth), getValue(opts.cardExpYear)];
            
            var month = parseInt(parts[0]);
            var year = parseInt(parts[1]);

            if (isNaN(month) || isNaN(year)) {
                warn('Invalid expiration date month or year');
                return null;
            }

            return { month: month, year: (year < 2000) ? (2000 + year) : year };
        }

        function getAccount() {
            var accountNum = getValue(opts.cardNum).replace(/[^0-9]/g, "");
            var expDate = getCardExpDate();
            return {
                AccountNumber: accountNum,
                ExpirationMonth: (!!expDate) ? expDate.month : '',
                ExpirationYear: (!!expDate) ? expDate.year : '',
                CardCode: $(opts.cardCode).val()
            };
        }

        function makeServerJWTGetter() {
            function ensureFormat(s) {
                return (JWT_REGEX.exec(s || '') !== null) ? s : null;
            }

            var val = opts.serverJWT || '';

            if (!val.length) {
                /* Return a function that returns an empty string, this is will error out in Cardinal.configure(); */
                return function() {
                    return '';
                };
            }

            var jwt = ensureFormat(val);
            if (jwt != null) {
                /* Assume this is the JWT value and return a function that returns it */
                return function() {
                    return jwt;
                };
            } else {
                /* Otherwise, consider it a selector and return a getter for the contained value */
                return function() {
                    var raw = getValue(opts.serverJWT);
                    jwt = ensureFormat(raw);
                    return jwt || '';
                };
            }
        }

        function startTransaction(extraOrderDetails) {
            // -> CC_FORM_STATE_AWAITING_START_RESULT
            form.data(CC_FORM_STATE, CC_FORM_STATE_AWAITING_START_RESULT);

            var orderDetails = $.extend({}, extraOrderDetails, opts.orderDetails);

            function handleNonFatalResult(data, jwt) {
                    // -> CC_FORM_STATE_AWAITING_RESUBMIT
                    form.data(CC_FORM_STATE, CC_FORM_STATE_AWAITING_RESUBMIT);

                    /*
                    jwt = jwt || '';
                    if (jwt.length && responseJWT.length) {
                        responseJWT.get(0).value = jwt;
                    }
                    */

                    setTimeout(function() {
                        form.trigger(PAYMENTS_VALIDATED_SUCCESS, { data: data, jwt: jwt });
                    });
                }

            function handleFatalResult(data, jwt) {
                form.trigger(PAYMENTS_VALIDATED_FAILURE, { data: data, jwt: jwt });
                alert("Error processing payment (" + (data.ActionCode || "unknown") + ") JWT: [" + jwt + "]");
            }

            Cardinal.on("payments.validated", function(data, jwt) {
                if (form.data(CC_FORM_STATE) !== CC_FORM_STATE_AWAITING_START_RESULT) {
                    warn('Ignoring multiple payments.validated events');
                    return;
                }

                switch(data.ActionCode) {
                    case "SUCCESS":
                    case "NOACTION":
                        handleNonFatalResult(data, jwt); break;

                    case "ERROR":
                    default: 
                        handleFatalResult(data, jwt); break;
                }
            });

            Cardinal.start("cca", { 
                Options: {
                    EnableCCA: configureOpts.EnableCCA
                },
                OrderDetails: orderDetails,
                Consumer: {
                    Account: getAccount()
                }
            });
        }

        function beginSubmission(extraOrderDetails) {
            var state = form.data(CC_FORM_STATE) || CC_FORM_STATE_NONE;

            switch (state) {
                case CC_FORM_STATE_NONE:
                    // -> CC_FORM_STATE_AWAITING_START_RESULT
                    startTransaction(extraOrderDetails);
                    // Prevent the default action while the consumer enters their response
                    return false;

                case CC_FORM_STATE_AWAITING_START_RESULT:
                    warn('Warning: the beginSubmission() method should not be called if there is a pending Start call in progress.');
                    return false;

                case CC_FORM_STATE_AWAITING_RESUBMIT:
                    // -> CC_FORM_STATE_NONE
                    form.data(CC_FORM_STATE, CC_FORM_STATE_NONE);

                    // Allow resubmission
                    return true;
            }
        }

        function handleSubmitButtonClick(event) {
            if (!beginSubmission()) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        }

        function handleFormSubmit(event) {
            if (!beginSubmission()) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }

            return true;
        }

        function bound() {
            var serverJWTFunc = makeServerJWTGetter();

            if (opts.bindFormSubmit === true) {
                form.on('submit', function(event) {
                    return handleFormSubmit(event);
                })
            }

            (submit.length > 1) && warn('Warning: More than one button matched the submitButton selector, this may not result in the correct button being used.');

            if (submit.length) {
                if (opts.bindSubmitButton === true) {
                    submit.on('click', function(event) {
                        return handleSubmitButtonClick(event);
                    });
                }

                if (opts.disableSubmitUntilStarted === true) {
                    submit.prop('disabled', true);
                    Cardinal.on('payments.setupComplete', function() {
                        submit.prop('disabled', false);
                    });
                }
            }

            /* Configure */
            Cardinal.configure(configureOpts);

            /* Setup */
            var serverJWT = serverJWTFunc();
            Cardinal.setup("init", { jwt: serverJWT });
        }

        // Enable bindings
        bound();

        // Public Methods
        return {
            beginSubmission: beginSubmission,
            handleFormSubmit: handleFormSubmit,
            handleSubmitButtonClick
        };
    }

    $.fn.CardinalCruise = function(opts, configureOpts) {
        var form = $(this);

        var obj = form.data(CC_OBJ);
        if (obj && 'setupInit' === opts) {
            return obj.beginSubmission();
        }

        if (!obj) {
            opts = $.extend({}, defaultOpts, opts);
            configureOpts = $.extend({}, configureOpts);

            obj = new SongbirdJQuery(form, opts, configureOpts);
            form.data(CC_OBJ, obj);
        }
        
        return obj;
    };

}(window.jQuery, window.Cardinal));