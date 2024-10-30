<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\Songbird\Objects as SongbirdObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;

/**
 * Generate a ServerJWT for Songbird with the given CartOrderDetails
 */
interface ServerJWTCreatorInterface {

    /**
     * Generate a ServerJWT for CardinalCruise
     *
     * @param CartInterfaces\CartOrderDetailsInterface $cartOrderDetails
     * @param SongbirdObjects\ServerJWTPayload $serverJWTPayload
     *
     * @return string the ServerJWT
     */
    public function create(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        SongbirdObjects\ServerJWTPayload $serverJWTPayload
    );
}
