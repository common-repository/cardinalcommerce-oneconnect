<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Songbird;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;

class ScriptRenderer implements SongbirdInterfaces\SongbirdCartScriptRendererInterface {
    private $_logger;

    public function __construct(LoggerInterface $logger) {
        $this->_logger = $logger;
    }

    private function getOptions(
        SongbirdInterfaces\SongbirdContextInterface $ctx
    ) {

        $page = $ctx->getCartPaymentDetailsPage();
        $configureOpts = $ctx->getConfigureOptions();

        $serverJWT_sel = sprintf("input[type=hidden][name='%s']", $ctx->getServerJWTHiddenInputName());
        $responseJWT_sel = sprintf("input[type=hidden][name='%s']", $ctx->getResponseJWTHiddenInputName());

        return (object) array(
            'Elements' => (object) array(
                'serverJWT' => $serverJWT_sel,
                'responseJWT' => $responseJWT_sel,

                'cardNum' => $page->getCardNumberSelector(),
                'cardExp' => $page->getCardExpSelector(),
                'cardExpDelim' => $page->getCardExpDelimiter(),
                'cardExpMonth' => $page->getCardExpMonthSelector(),
                'cardExpYear' => $page->getCardExpMonthSelector(),
                'cardCode' => $page->getCardCVVSelector(),

                'submitButton' => $page->getSubmitButtonSelector()
            )
        );
    }

    /**
     * Render script block with values to initialize Songbird (cart or framework implementation).
     * @returns String
     */
    public function renderSongbirdScriptBlock(
        SongbirdInterfaces\SongbirdContextInterface $ctx,
        BasePaymentMethod $paymentMethod,
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        BaseCartOrder $cartOrder = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {

        $scriptBlock = <<<'EOT'

<script>
(function($) {
    $(document).ready(function() {
        var form = $("{{form_sel}}");

        window.CardinalCruiseWooCommerce(form, {{opts}}, {{configureOpts}});
    });
}(window.jQuery));
</script>
EOT;

        $page = $ctx->getCartPaymentDetailsPage();

        $replacementValues = array(
            '{{opts}}' => json_encode($this->getOptions($ctx)),
            '{{configureOpts}}' => json_encode($ctx->getConfigureOptions()),
            '{{form_sel}}' => $page->getFormSelector()
        );

        return strtr($scriptBlock, $replacementValues);
    }
}
