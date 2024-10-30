<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Hooks\OrderStatus;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;

use \CardinalCommerce\Payments\Carts\WooCommerce\Processors as CartProcessors;

class OrderStatusHooks {
    private $_logger;
    private $_settings;
    private $_checkoutProcessor;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        CartProcessors\CheckoutProcessor $checkoutProcessor
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
        $this->_checkoutProcessor = $checkoutProcessor;
    }

    public function order_status_completed( $order_id ) {
        $logger = $this->_logger;
        $checkoutProcessor = $this->_checkoutProcessor;

        $logger->info('[WCOrderStatusHooks:order_status_completed] updating order [{order_id}] to status completed, and triggering capture if enabled in the Cardinal PaymentMethod.',
            array( 'order_id' => $order_id ));

        // Process capture
        $success = $checkoutProcessor->processCapture( $order_id );

        $logger->info('[WCOrderStatusHooks:order_status_completed] CheckoutProcessor::processCapture success: ' . json_encode($success) );
    }

    public function capture_payment( $order_id ) {
        $logger = $this->_logger;
        $checkoutProcessor = $this->_checkoutProcessor;

        $logger->info('[WCOrderStatusHooks:capture_payment] CAPTURE PAYMENT FOR ORDER MARKED COMPLETED (order_id: {order_id}).',
            array( 'order_id' => $order_id ));

        // Process capture
        $success = $checkoutProcessor->processCapture( $order_id );

        $logger->info('[WCOrderStatusHooks:capture_payment] CAPTURE PAYMENT RESULT success: ' . json_encode($success) );
    }

    public function order_status_changed( $order_id, $from_status, $to_status ) {
        $logger = $this->_logger;

        $logger->info('[WCOrderStatusHooks:order_status_changed] order_id: ' .json_encode( $order_id ) );
        $logger->info('[WCOrderStatusHooks:order_status_changed] from_status: ' .json_encode( $from_status ) );
        $logger->info('[WCOrderStatusHooks:order_status_changed] to_status: ' .json_encode( $to_status ) );

        $checkoutProcessor = $this->_checkoutProcessor;
        $logger->info('[WCOrderStatusHooks:order_status_changed] status changed for order [{order_id}] from [{from_status}] to [{to_status}]',
            array( 'order_id' => $order_id, 'from_status' => $from_status, 'to_status' => $to_status ) );

        if ( 'completed' === $to_status ) {
            $logger->info('[WCOrderStatusHooks:order_status_changed] status changed to completed, firing capture.' );

            $this->capture_payment( $order_id );
        }
    }

    public function order_edit_status( $order_id, $status ) {
        $logger = $this->_logger;

        $logger->info('[WCOrderStatusHooks:order_edit_status] order_id: ' .json_encode( $order_id ) );
        $logger->info('[WCOrderStatusHooks:order_edit_status] status: ' .json_encode( $status ) );

        $checkoutProcessor = $this->_checkoutProcessor;
        $logger->info('[WCOrderStatusHooks:order_edit_status] status changed for order [{order_id}] to [{to_status}]',
            array( 'order_id' => $order_id, 'status' => $status ) );

        if ( 'completed' === $status ) {
            $logger->info('[WCOrderStatusHooks:order_edit_status] status changed to completed, firing capture.' );

            $this->capture_payment( $order_id );
        }
    }

    public function setup() {
        $logger = $this->_logger;

        $logger->info('[WCOrderStatusHooks:setup] registering `order_status_changed` hook' );
        \add_action('woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 10, 3);

        $logger->info('[WCOrderStatusHooks:setup] registering `order_edit_status` hook' );
        \add_action('woocommerce_order_edit_status', array( $this, 'order_edit_status '), 10, 2 );
    }
}