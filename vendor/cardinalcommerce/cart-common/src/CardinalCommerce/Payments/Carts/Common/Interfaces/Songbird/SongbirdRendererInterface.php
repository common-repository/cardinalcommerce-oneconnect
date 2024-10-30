<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

/**
 * Render common page elements for invoking CardinalCruise.
 */
interface SongbirdRendererInterface {

    /**
     * Render hidden inputs whose values are provided by the server (cart or framework implementation).
     * @return string
     */
    public function renderServerProvidedHiddenInputs(
        SongbirdInterfaces\SongbirdContextInterface $ctx,
        CartInterfaces\CartOrderDetailsInterface $cardOrderDetails,
        $serverJWT
    );

    /**
     * Render hidden inputs whose values will be provided by the client during the transaction (cart or framework implementation).
     * @return string
     */
    public function renderClientProvidedHiddenInputs(
        SongbirdInterfaces\SongbirdContextInterface $ctx
    );
}
