<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Cart;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Interfaces\APICredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelCredentialsInterface;

/**
 * Common connection point for cart integration
 */
interface CartIntegrationInterface {

    /**
     * @return CartSettingsInterface
     */
    public function getSettings();

    /**
     * @return EnvironmentInterfaces\EnvironmentConfigInterface
     */
    public function getEnvironmentConfig();

    /**
     * @return CartInterfaces\CartObjectsFactoryInterface
     */
    public function getCartObjectsFactory();
}