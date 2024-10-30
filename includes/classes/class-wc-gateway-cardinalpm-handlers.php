<?php
// No namespace

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use \Psr\Log\LoggerInterface;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;

use \CardinalCommerce\Payments\Carts\WooCommerce\Logging\LoggingAdapter;
use \CardinalCommerce\Payments\Carts\WooCommerce\Settings\CartSettings;
use \CardinalCommerce\Payments\Carts\WooCommerce\Factories\CartObjectsFactory;
use \CardinalCommerce\Payments\Carts\WooCommerce\Integration\CartIntegration;

use \CardinalCommerce\Payments\Carts\Common\CommonObjects;

class WC_Gateway_CardinalPM_Handlers {
    public static function createAdminHandler() {
        return new WC_Gateway_CardinalPM_Admin_Handler(
            \wc_gateway_cardinalpm()->objects()->logger(),
            \wc_gateway_cardinalpm()->objects()->settings()
        );
    }
}