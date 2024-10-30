<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Integration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;

/**
 * Common connection point for cart integration
 */
class CartIntegration implements CartInterfaces\CartIntegrationInterface {

    /**
     * @return CartInterfaces\CartSettingsInterface
     */
    public function getSettings() {
        return \wc_gateway_cardinalpm()->objects()->settings();
    }

    /**
     * @return EnvironmentInterfaces\EnvironmentConfigInterface
     */
    public function getEnvironmentConfig() {
        return \wc_gateway_cardinalpm()->objects()->plugin_settings()->environment_config();
    }

    /**
     * @return CartInterfaces\CartObjectsFactoryInterface
     */
    public function getCartObjectsFactory() {
        return \wc_gateway_cardinalpm()->objects()->objects_factory();
    }
  
}