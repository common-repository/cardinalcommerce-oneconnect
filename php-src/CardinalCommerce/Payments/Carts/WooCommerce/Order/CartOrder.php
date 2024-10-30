<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Order;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodResult;

use \CardinalCommerce\Payments\Carts\WooCommerce\Data\OrderMetaKeys;

use \CardinalCommerce\Payments\Objects as PaymentObjects;

class CartOrder extends BaseCartOrder {
    private $_orderId;
    private $_orderDetails;

    // WooCommerce order
    private $_order;

    private function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        $orderId,
        $order,
        $orderNumber
    ) {
        $this->_logger = $logger;
        $this->_orderNumber = $orderNumber;
        $this->_orderDetails = $cartOrderDetails;

        // Save the cart provided order_id
        $this->_orderId = $orderId;

        // Save the cart provided order object
        $this->_order = $order;
    }

    protected static function createOrderNumber( $orderId = null ) {
        // NOTE: sha1 is used only to ensure the result is a hex string
        // we do not rely on collision behavior

        $orderNumberSuffix = substr(sha1(uniqid(mt_rand(), true)), 0, 5);
        if ( $orderId != null ) {
            return sprintf("cardinalpm-%d-%s", $orderId, $orderNumberSuffix);
        }
        return sprintf("cardinalpm-%s", $orderNumberSuffix);
    }

    public static function forCartOrderId( $order_id ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();

        $logger->info('[CartOrder::forCartOrderId] order_id: ' . $order_id);

        $order = \wc_get_order( $order_id );
        $cartOrderDetails = CartOrderDetails::fromWCOrder( $order );

        $logger->info('[CartOrder::forCartOrderId] cartOrderDetails: {cartOrderDetails}',
            array(
                'cartOrderDetails' => var_export($cartOrderDetails, true)
            ));
        
        // Construct a unique order id from the cart provided order_id and current time in microseconds
        $orderNumber = static::createOrderNumber( $order_id );

        return new self(
            $logger,
            $cartOrderDetails,
            $order_id,
            $order,
            $orderNumber
        );
    }

    // Interface with cart persistent storage

    protected function storeMetaKeyValue( $metaKey, $value ) {
        \update_post_meta( $this->_orderId, $metaKey, $value );
    }

    protected function getMetaKeyValue( $metaKey ) {
        return \get_post_meta( $this->_orderId, $metaKey, true );
    }

    public function getOrderNumber() {
        return $this->_orderNumber;
    }

    public function getCartOrderDetails() {
        $cartOrderDetails = $this->_orderDetails;

        $this->_logger->info('[CartOrder::getCartOrderDetails] cartOrderDetails: {cartOrderDetails}',
            array(
                'cartOrderDetails' => var_export($cartOrderDetails, true)
            ));
        
        return $this->_orderDetails;
    }

    public function storePaymentMethodResultData( BasePaymentMethodResult $result ) {
        $order_id = $this->_orderId;
        $resultMetaKeyValues = $result->getMetaKeyValues();

        foreach( $resultMetaKeyValues as $key => $value ) {
            \update_post_meta( $order_id, $key, $value );
        }
    }

    protected function storeCardinalPaymentMethodKey( $order_id, $key ) {
        $this->storeMetaKeyValue( OrderMetaKeys::CARDINAL_SELECTED_PAYMENT_METHOD, $key );
    }

    public function storeCardinalProcessorTransactionId( $processorTransactionId ) {
        $this->storeMetaKeyValue( OrderMetaKeys::CARDINAL_COMMON_PROCESSOR_TRANSACTION_ID, $processorTransactionId );
    }

    public function storeCardinalProcessorOrderId( $processorOrderId ) {
        $this->storeMetaKeyValue( OrderMetaKeys::CARDINAL_COMMON_PROCESSOR_ORDER_ID, $processorOrderId );
    }

    public function getCardinalPaymentMethodKey() {
        return $this->getMetaKeyValue( OrderMetaKeys::CARDINAL_SELECTED_PAYMENT_METHOD );
    }

    public function getCardinalProcessorTransactionId() {
        return $this->getMetaKeyValue( OrderMetaKeys::CARDINAL_COMMON_PROCESSOR_TRANSACTION_ID );
    }

    public function getCardinalProcessorOrderId() {
        return $this->getMetaKeyValue( OrderMetaKeys::CARDINAL_COMMON_PROCESSOR_ORDER_ID );
    }

    public function markInProcessing(
        BasePaymentMethodResult $result
    ) {
        $logger = $this->_logger;
        $order = $this->_order;

        $logger->info('[CartOrder::markInProcessing] setting order status to processing.');
        $order->update_status ( 'processing' );

        $order->add_order_note( sprintf( __('CardinalCommerce ProcessorModule Payment authorized (Order Number: %s, Auth Code: %s)', 'wc-cardinalprocessormodule'),
            $result->getOrderNumber(),
            $result->getAuthorizationCode() ));

        $order->save();
    }

    public function markComplete(
        BasePaymentMethodResult $result
    ) {
        $logger = $this->_logger;
        $order = $this->_order;

        $logger->info('[CartOrder::markComplete] marking payment complete.');
        $order->payment_complete();

        $order->add_order_note( sprintf( __('CardinalCommerce ProcessorModule Payment authorized and captured (ID: %s, Auth Code: %s)', 'wc-cardinalprocessormodule'),
            $result->payment->id, $result->payment->authCode ));

        $order->save();
    }

    public function markVoided(
        BasePaymentMethodResult $result
    ) {
        $logger = $this->_logger;
        $order = $this->_order;

        $logger->info('[CartOrder::markVoided] setting order status to voided.');
        $order->update_status ( 'voided' );

        $order->add_order_note( sprintf( __('CardinalCommerce ProcessorModule Payment voided (Order Number: %s)', 'wc-cardinalprocessormodule'),
            $this->getOrderNumber()
        ));

        $order->save();
    }

    public function reduceStock() {
        $logger = $this->_logger;
        $order = $this->_order;

        // Reduce stock levels
        $logger->info('[CartOrder::reduceStock] reducing stock.');
        $order->reduce_order_stock();
    }

    public function clearCart() {
        $logger = $this->_logger;
        $order = $this->_order;

        // Empty the cart
        $logger->info('[WC_Gateway_CardinalPM::process_payment] emptying cart.');
        \WC()->cart->empty_cart();   
    }
}