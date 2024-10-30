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
use \CardinalCommerce\Payments\Carts\WooCommerce\Settings\AdminSettings;
use \CardinalCommerce\Payments\Carts\WooCommerce\Factories\CartObjectsFactory;
use \CardinalCommerce\Payments\Carts\WooCommerce\Integration\CartIntegration;

use \CardinalCommerce\Payments\Carts\Common\CommonObjects;

class WC_Gateway_CardinalPM_Objects {

    public function logger() {
        static $_instance = null;

        if ( $_instance == null ) {
            // NEXTREV: WooCommerce 2.7 Logger implements the Psr\Log\LoggerInterface, LoggingAdapter can then be removed.
            // and \wc_get_logger() is used to access the global instance of the WooCommerce logger.
            $_instance = new LoggingAdapter(new \WC_Logger(), 'CardinalProcessorModule');
        }

        return $_instance;
    }

    public function environments() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = \WC_Gateway_CardinalPM_Environments::loadEnvironments();
        }

        return $_instance;
    }

    public function plugin_settings() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new WC_Gateway_CardinalPM_Settings( \wc_gateway_cardinalpm()->payment_method_id() );
        }

        return $_instance;
    }

    public function settings() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new CartSettings();
        }

        return $_instance;
    }

    public function admin_settings() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new AdminSettings();
        }

        return $_instance;
    }

    public function cart_integration() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new CartIntegration();
        }

        return $_instance;
    }

    public function objects_factory() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new CartObjectsFactory(
                $this->logger(),
                $this->settings()
            );
        }

        return $_instance;
    }

    public function common_objects() {
        static $_initialized = false;

        if ( ! $_initialized ) {
            CommonObjects::initialize(
                $this->logger(),
                $this->cart_integration()
            );

            $_initialized = true;
        }

        return CommonObjects::instance();
    }

    public function order_management() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new WC_Gateway_CardinalPM_Order_Management();
        }

        return $_instance;
    }

    public function createAdminHandler() {
        return new WC_Gateway_CardinalPM_Admin_Handler(
            $this->logger(),
            $this->settings()
        );
    }

}