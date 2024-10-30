<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Hooks\Checkout;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\WooCommerce\Processors as CartProcessors;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\WooCommerce\Data\OrderMetaKeys;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class CheckoutOrderHooks {
    private $_logger;
    private $_settings;
    private $_paymentMethods;
    private $_checkoutProcessor;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        CommonPaymentMethodsInterface $paymentMethods,
        CartProcessors\CheckoutProcessor $checkoutProcessor
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
        $this->_paymentMethods = $paymentMethods;
        $this->_checkoutProcessor = $checkoutProcessor;
    }

    public function checkout_after_order_notes() {
        $this->_checkoutProcessor->renderHiddenInputs();
    }

    protected function updateSelectedPaymentMethod( $order_id, $key ) {
        \update_post_meta( $order_id, OrderMetaKeys::CARDINAL_SELECTED_PAYMENT_METHOD, $key );
    }

    public function checkout_update_order_meta( $order_id, $posted ) {
        $key = $this->_paymentMethods->getCurrentKey();

        $this->_logger->info('[WCCheckoutOrderHooks::checkout_update_order_meta] setting order_id [{order_id}] meta key [{meta_key}] to [{key}]', array(
            'order_id' => $order_id,
            'meta_key' => OrderMetaKeys::CARDINAL_SELECTED_PAYMENT_METHOD,
            'key' => $key
        ));

        $this->updateSelectedPaymentMethod( $order_id, $key );
    }

    public function setup() {
        \add_action('woocommerce_after_order_notes', array( $this, 'checkout_after_order_notes' ));
        \add_action('woocommerce_checkout_update_order_meta', array( $this, 'checkout_update_order_meta' ), 10, 2);
    }

}