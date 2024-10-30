<?php
namespace CardinalCommerce\Payments\Carts\Common\Songbird;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

class SongbirdRenderer implements SongbirdInterfaces\SongbirdRendererInterface {
    private $_logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->_logger = $logger;
    }

    /**
     * Render hidden inputs whose values are provided by the server (cart or framework implementation).
     */
    public function renderServerProvidedHiddenInputs(
        SongbirdInterfaces\SongbirdContextInterface $ctx,
        CartInterfaces\CartOrderDetailsInterface $cardOrderDetails,
        $serverJWT
    ) {
        $jwtName = $ctx->getServerJWTHiddenInputName();

        return sprintf('<input type="hidden" id="%s" name="%s" value="%s" />', $jwtName, $jwtName, $serverJWT) . PHP_EOL;
    }

    /**
     * Render hidden inputs whose values will be provided by the client during the transaction (cart or framework implementation).
     */
    public function renderClientProvidedHiddenInputs(
        SongbirdInterfaces\SongbirdContextInterface $ctx
    ) {
        $jwtName = $ctx->getResponseJWTHiddenInputName();

        return sprintf('<input type="hidden" id="%s" name="%s" />', $jwtName, $jwtName) . PHP_EOL;
    }
}
