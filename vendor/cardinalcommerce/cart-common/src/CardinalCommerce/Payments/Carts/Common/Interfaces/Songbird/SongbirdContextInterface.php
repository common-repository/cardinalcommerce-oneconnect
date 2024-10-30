<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;

/**
 * The non-transaction-specific Songbird context.
 */
interface SongbirdContextInterface {

    public function getSettings();

    public function getCartPaymentDetailsPage();

    /**
     * Return Songbird Cardinal.configure options
     * see https://developer.cardinalcommerce.com/cardinal-cruise-activation.shtml#availConfigOptions
     * @returns object
     */
    public function getConfigureOptions();

    /**
     * Return Songbird Cardinal.setup('init') options
     * @returns object
     */
    public function getSetupInitOptions(
        BaseCartOrder $cartOrder,
        PaymentObjects\Consumer $consumerObject,
        $serverJWT
    );

    /**
     * Return the name of the hidden input in which to populate the ServerJWT
     * @returns string The name of the hidden input.
     *
     * NEXTREV: Move to CartPaymentDetailsPageInterface
     */
    public function getServerJWTHiddenInputName();

    /**
     * Return the name of the hidden input in which the client script will store the ResponseJWT
     * once it has been returned in the Response object.
     * @returns string The name of the hidden input.
     *
     * NEXTREV: Move to CartPaymentDetailsPageInterface
     */
    public function getResponseJWTHiddenInputName();
}