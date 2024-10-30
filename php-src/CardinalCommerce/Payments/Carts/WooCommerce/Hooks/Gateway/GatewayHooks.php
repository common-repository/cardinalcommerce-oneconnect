<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Hooks\Gateway;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;

if ( !defined( 'ABSPATH' )) {
    exit;
}

class GatewayHooks {
    private $_logger = null;
    private $_settings = null;
    private $_gateway_class = null;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $settings,
        $gateway_class
    ) {
        $this->_logger = $logger;
        $this->_settings = $settings;
        $this->_gateway_class = $gateway_class;
    }

    /**
    * Display an admin notice, if not on the integration screen and if the account isn't yet connected.
    * @access public
    * @since  1.0.0
    * @return void
    */
    public function maybe_display_admin_notices () {
        if ( isset( $_GET['page'] ) && 'wc-settings' == $_GET['page'] && isset( $_GET['section'] ) && 'mailchimp' == $_GET['section'] ) {
            return; // Don't show these notices on our admin screen.
        }
    }

    public function add_payment_gateway_class( $method ) {
        $methods[] = $this->_gateway_class;
        return $methods;
    }

    public function setup() {
        \add_action( 'admin_notices', array( $this, 'maybe_display_admin_notices' ) );

        \add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateway_class' ) );
    }

}