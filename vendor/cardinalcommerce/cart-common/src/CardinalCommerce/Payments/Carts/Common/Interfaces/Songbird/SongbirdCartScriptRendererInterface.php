<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;

/**
 * Render Songbird script block to interact with cart implementation.
 */
interface SongbirdCartScriptRendererInterface {

    /**
     * Render script block with values to initialize Songbird (cart or framework implementation).
     * @return string
     */
    public function renderSongbirdScriptBlock(
        SongbirdInterfaces\SongbirdContextInterface $ctx,
        BasePaymentMethod $paymentMethod,
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        BaseCartOrder $cartOrder = null,
        PaymentObjects\Consumer $consumerObject = null
    );
}
