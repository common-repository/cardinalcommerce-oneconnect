<?php
// No namespace, this is a class instanced by WooCommerce

if ( !defined( 'ABSPATH' )) {
    exit;
}

// Logging
use \CardinalCommerce\Payments\Carts\WooCommerce\Logging\LoggingAdapter;

// Common
use \CardinalCommerce\Payments\Carts\Common\Songbird\ResponseJWTParser;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\PaymentAuthTypes;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

// Cart
use \CardinalCommerce\Payments\Carts\WooCommerce\Settings\CartSettings;
use \CardinalCommerce\Payments\Carts\WooCommerce\Settings\AdminSettings;
use \CardinalCommerce\Payments\Carts\WooCommerce\Pages as CartPages;
use \CardinalCommerce\Payments\Carts\WooCommerce\Order as CartOrders;
use \CardinalCommerce\Payments\Carts\WooCommerce\Forms as CartForms;
use \CardinalCommerce\Payments\Carts\WooCommerce\Hooks as CartHooks;
use \CardinalCommerce\Payments\Carts\WooCommerce\Factories as CartFactories;
use \CardinalCommerce\Payments\Carts\WooCommerce\Processors as CartProcessors;

/**
* CardinalCommerce Processor Module
*
* Base WooCommerce payment gateway for CardinalCommerce payment methods.
*
* @class       WC_Gateway_CardinalPM
* @extends     WC_Payment_Gateway
* @version     1.0.0
* @package     CardinalCommerce/Carts/WooCommerce/Payment
* @author      CardinalCommerce
*/

/**
* WC_Gateway_CardinalPM Class.
*/
abstract class WC_Gateway_CardinalPM extends \WC_Payment_Gateway_CC {

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var CartInterfaces\CartSettingsInterface
     */
    private $_settings;

    public function __construct() {
        $plugin = \wc_gateway_cardinalpm();

        $logger = $plugin->objects()->logger();
        $cartSettings = $plugin->objects()->settings();
        $environments = $plugin->objects()->environments();
        $cartIntegration = $plugin->objects()->cart_integration();

        $commonObjects = $plugin->objects()->common_objects();

        $paymentMethods = $commonObjects->getPaymentMethods();

        // WooCommerce required fields

        $this->id                 = static::PAYMENT_METHOD_ID;

        $this->has_fields         = true;
        $this->order_button_text  = __( 'Complete Payment', 'wc-cardinalprocessormodule' );

        $this->method_title       = $this->get_option( 'method_title', __( static::DEFAULT_METHOD_TITLE, 'wc-cardinalprocessormodule' ) );
        $this->method_description = $this->get_option( 'method_description', __( static::DEFAULT_METHOD_DESCR, 'wc-cardinalprocessormodule' ) );

        $this->supports           = array(
            'products',
            'default_credit_card_form',
            //'tokenization',
            'refunds'
        );

        $this->access_key = $this->get_option( 'access_key' );
        $this->title = $this->get_option( 'title', static::DEFAULT_TITLE );
        $this->description = $this->get_option( 'description', static::DEFAULT_DESCR );

        $this->test_mode = 'yes' === $this->get_option( 'testmode', 'no' );
        $this->debug = 'yes' === $this->get_option( 'debug', 'no' );

        $logger->info( '[WC_Gateway_CardinalPM::__construct] ============================ Creating gateway ============================' );

        $checkoutProcessor = new CartProcessors\CheckoutProcessor( static::PAYMENT_METHOD_ID );

        $this->_logger = $logger;
        $this->_settings = $cartSettings;
        $this->_checkoutProcessor = $checkoutProcessor;

        // Prepare Settings page

        $this->form_fields = \wc_gateway_cardinalpm()->objects()->admin_settings()->form_fields();
        $logger->info('[WC_Gateway_CardinalPM::__construct] form_fields: ' . json_encode($this->form_fields));

        // Setup hooks

        $scriptsDir = sprintf( "%s/js/", $plugin->assets_url() );

        $hooks = new CartHooks\Hooks(
            $logger,
            $cartSettings,
            $paymentMethods,
            $checkoutProcessor,
            GATEWAY_CLASS,
            $scriptsDir
        );

        $hooks->setup();

        if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
        }
        else {
            add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
        }
    }

    // Payment form

    /**
    * Get gateway icon.
    *  @return string
    */
    public function get_icon() {
        $icon_html = '<img src="../wp-content/plugins/cardinalcommerce-oneconnect/assets/images/card-visa.svg" width="40" height="25" style="width: 40px; height: 25px;" />
        <img src="../wp-content/plugins/cardinalcommerce-oneconnect/assets/images/card-mastercard.svg" width="40" height="25" style="width: 40px; height: 25px;" />
        <img src="../wp-content/plugins/cardinalcommerce-oneconnect/assets/images/card-discover.svg" width="40" height="25" style="width: 40px; height: 25px;" />
        <img src="../wp-content/plugins/cardinalcommerce-oneconnect/assets/images/card-amex.svg" width="40" height="25" style="width: 40px; height: 25px;" />
        ';

        return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
    }

    /**
     * Define payment fields
     */
  // public function payment_fields() {
			// $this->get_payment_form()->render();
	// }

  /**
   * Get the payment form class
   */
  // public function get_payment_form() {
    // return new WC_Gateway_CardinalPM_Payment_Form( $this );
  // }

    /**
    * Check if gateway is available in this country.
    */
    public function is_valid_for_use() {
        // TODO: Do we need to limit this?
        /// SEE: https://docs.woocommerce.com/wc-apidocs/source-class-WC_Gateway_Paypal.html#193
        return true;
    }

    /**
    * Initialize Gateway Settings Form Fields.
    */
    public function admin_options() {
        if ( $this->is_valid_for_use() ) {
            //parent::admin_options();
            $this->cardinal_admin_options();

        } else {
            echo sprintf('<div class="inline error"><p><strong>%s</strong>: %s</p></div>',
                _e( 'Gateway Disabled', 'woocommerce' ),
                _e( 'Cardinal Processor Module does not support the selected currency.', 'wc-cardinalprocessormodule' )
            );
        }
    }

    protected function cardinal_admin_options() {
        ?><h3><?php _e( 'Cardinal Processor Module', 'wc-cardinalprocessormodule'); ?></h3><?php

        $apiCreds = $this->_settings->getAPICredentials();

        if ( $apiCreds == null ) {
            ?>
                <div class="cardinal-setup-banner updated">
                <img src="https://www.cardinalcommerce.com/images/logo.png" />
                <p class="main"><strong><?php _e( 'Connect with CardinalCommerce', 'wc-cardinalprocessormodule'); ?></strong></p>
                <p><a href="https://developer.cardinalcommerce.com/register.shtml" target="_new">Register for a Cardinal account and start transacting with Processor Module now.</a></p>
                </div>
            <?php
        }

        /*
        $this->checks();
        */

        ?>
        <table class="form-table">
        <?php $this->generate_settings_html(); ?>
        </table>
        <?php
    }

    protected function checks() {
        if ('no' == $this->enabled) {
            return;
        }

        echo '';
    }

    /**
    * Get the transaction URL.
    * @param WC_Order $order
    * @return string
    */
    public function get_transaction_url( $order ) {
        if ( $this->testmode ) {
            $this->view_transaction_url = '';
        }
        return parent::get_transaction_url( $order );
    }

    // Credit Card Processing

    /**
    * Process the payment and return the result.
    * @param int $order_id
    * @return array
    */
    public function process_payment( $order_id ) {
        $logger = $this->_logger;
        $cartSettings = $this->_settings;
        $checkoutProcessor = $this->_checkoutProcessor;

        $postData = $_POST;

        $logger->info('[WC_Gateway_CardinalPM::process_payment] order_id: ' . json_encode($order_id));
        $logger->info('[WC_Gateway_CardinalPM::process_payment] POST: ' . json_encode($postData));

        $authType = $cartSettings->getPaymentAuthType();
        $logger->info('[WC_Gateway_CardinalPM::process_payment] authType: ' . json_encode($authType));

        $success = $checkoutProcessor->processPayment(
            $order_id,
            $authType,
            $postData
        );
        $redirect = null;

        $this->_logger->info('[WC_Gateway_CardinalPM::process_payment] success: ' . json_encode($success));
        $this->_logger->info('[WC_Gateway_CardinalPM::process_payment] redirect: ' . json_encode($redirect));

        if( $success ) {
            // Prepare redirect
            $redirect = $this->get_return_url( $order );
            $this->_logger->info('[WC_Gateway_CardinalPM::process_payment] redirect to [{redirect}].', array( 'redirect' => $redirect ));
        }

        return array(
            'result' => $success ? 'success' : 'failure',
            'redirect' => $redirect
        );
    }

    /**
    * Can the order be refunded using Processor Module
    * @param WC_Order $order
    * @return bool
    */
    protected function can_refund_order( $order_id, $amount, $reason = '' ) {

        $logger = $this->_logger;
        $cartSettings = $this->_settings;
        $checkoutProcessor = $this->_checkoutProcessor;

        return wc_gateway_cardinalpm()->order_management()
            ->can_refund_order( $order_id, $amount, $reason );

        //return $order && $order->get_transaction_id();
    }

   /**
     * Process refund.
     *
     * If the gateway declares 'refunds' support, this will allow it to refund.
     * a passed in amount.
     *
     * @param  int $order_id
     * @param  float $amount
     * @param  string $reason
     * @return boolean True or false based on success, or a WP_Error object.
     */
     public function process_refund( $order_id, $amount = NULL, $reason = '' ) {
            $logger = $this->_logger;
            $cartSettings = $this->_settings;
            $checkoutProcessor = $this->_checkoutProcessor;

            if ( ! $this->can_refund_order( $order_id, $amount, $reason )) {
                return $this->_failure( __('Refund failed, cannot refund order.', 'wc-cardinalprocessormodule'), __METHOD__ );
            }

            $success = wc_gateway_cardinalpm()->order_management()
                ->refund_order( $order_id, $amount, $reason );

            // NEXTREV: Retrieve error/reason details and decide whether to return a \WP_Error object.

            if ( ! $success ) {
                return $this->_failure( __('Refund failed, cannot refund order.', 'wc-cardinalprocessormodule'), __METHOD__ );
            }

            return true;
        }

        protected function _failure( $msg, $method ) {
            $logger = $this->_logger;

            $logger->error( sprintf( '[%s::%s] %s', __CLASS__, $method, $msg ) );
            return new \WP_Error( 'error', $msg );
        }
    }
