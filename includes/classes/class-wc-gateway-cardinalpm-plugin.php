<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Gateway_CardinalPM_Plugin {
    /**
     * @var string
     */
    private $_file;

    /**
     * @var string
     */
    private $_version;

    /**
     * @var string
     */
    private $_paymentMethodId;

    /**
     * @var string
     */
    private $_gatewayClassName;

    /**
     * @var bool
     */
    private $_bootstrapped;
    
    /**
     * @var CartProcessors\CheckoutProcessor
     */
    private $_checkoutProcessor;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var EnvironmentInterfaces\EnvironmentConfigsInterface
     */
    private $_environments;

    /**
     * @var CartInterfaces\CartSettingsInterface
     */
    private $_settings;

    /**
     * @var CommonObjects
     */
    private $_objects;

    private $_objectsFactory;
    private $_admin;
    private $_orderManagement;

    private $_pluginUrl;
    private $_assetsDir;
    private $_assetsUrl;
    private $_configDir;

    public function __construct( $file, $version, $paymentMethodId, $gatewayClassName ) {
        $this->_file = $file;
        $this->_version = $version;
        $this->_paymentMethodId = $paymentMethodId;
        $this->_gatewayClassName = $gatewayClassName;
        $this->_bootstrapped = false;

        // Helpers
        $this->_pluginUrl = trailingslashit( plugin_dir_url( $file ) );
        $this->_assetsUrl = trailingslashit( plugin_dir_url( $file ) ) . 'assets/';
        $this->_configDir = trailingslashit( plugin_dir_path( $file ) ) . 'includes/config/';
    }

    public function maybe_run() {
        // NEXTREV: Activation hook
        // register_activation_hook( $this->_file, array( $this, 'activate' ) );

        add_action( 'plugins_loaded', array( $this, 'plugin_loaded' ) );
    }

    public function plugin_loaded() {
        if ( $this->_bootstrapped ) {
            throw new Exception( __('CardinalCommerce Processor Module Plugin already loaded.' ) );
        }

        //error_log('[WC_Gateway_CardinalPM_Plugin::plugin_loaded] before _bootstrap()');
        $this->_bootstrap();

        //error_log('[WC_Gateway_CardinalPM_Plugin::plugin_loaded] before _load_handlers()');
        $this->_load_handlers();
    }

    public function payment_gateways( $methods ) {
        $methods[] = sprintf("%s", $this->_gatewayClassName);
        return $methods;
    }

    protected function _bootstrap() {
        require_once( __DIR__ . '/../../vendor/autoload.php' );
        require_once( __DIR__ . '/class-wc-gateway-cardinalpm-settings.php' );
        require_once( __DIR__ . '/class-wc-gateway-cardinalpm-common.php' );
        require_once( __DIR__ . '/class-wc-gateway-cardinalpm-environments.php' );
        require_once( __DIR__ . '/class-wc-gateway-cardinalpm-objects.php' );
        require_once( __DIR__ . '/class-wc-gateway-cardinalpm-order-management.php' );

        $this->_objects = new WC_Gateway_CardinalPM_Objects();
        $this->_boostrapped = true;
    }

    protected function _load_handlers() {
        require_once( __DIR__ . '/class-wc-gateway-cardinalpm-admin-handler.php' );
        require_once( __DIR__ . '/class-wc-gateway-cardinalpm-handlers.php' );

        // Handlers
        $this->_admin = WC_Gateway_CardinalPM_Handlers::createAdminHandler();

        add_filter( 'woocommerce_payment_gateways', array( $this, 'payment_gateways' ) );

        /*
        \add_action('all', function( $data ) {
            error_log( sprintf("[WC_Gateway_CardinalPM_Plugin::_load_handlers] current filter [%s]", var_export( \current_filter(), true) ) );
            return $data;
        } );
        */
    }

    public function objects() {
        if ( ! $this->_boostrapped ) {
            throw new \Exception( sprintf("Cannot call wc_gateway_cardinalpm()->%s() until the plugin is loaded", __METHOD__ ));
        }
        return $this->_objects;
    }

    /*
    public function resolver() {
        if ( ! $this->_boostrapped ) {
            throw new \Exception( sprintf("Cannot call wc_gateway_cardinalpm()->%s() until the plugin is loaded", __METHOD__ ));
        }
        return $this->_objects->getPaymentMethodResolver();
    }

    public function payment_methods() {
        if ( ! $this->_boostrapped ) {
            throw new \Exception( sprintf("Cannot call wc_gateway_cardinalpm()->%s() until the plugin is loaded", __METHOD__ ));
        }
        return $this->_objects->getPaymentMethods();
    }

    public function objects_factory() {
        if ( ! $this->_boostrapped ) {
            throw new \Exception( sprintf("Cannot call wc_gateway_cardinalpm()->%s() until the plugin is loaded", __METHOD__ ));
        }
        return $this->_objectsFactory;
    }

    public function order_management() {
        if ( ! $this->_boostrapped ) {
            throw new \Exception( sprintf("Cannot call wc_gateway_cardinalpm()->%s() until the plugin is loaded", __METHOD__ ));
        }
        return $this->_orderManagement;
    }
    */

    public function payment_method_id() {
        return $this->_paymentMethodId;
    }

    public function plugin_url() {
        return $this->_pluginUrl;
    }
    
    public function assets_url() {
        return $this->_assetsUrl;
    }
   
    public function config_dir() {
        return $this->_configDir;
    }

}