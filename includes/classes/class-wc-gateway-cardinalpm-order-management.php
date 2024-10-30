<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\WooCommerce\Order\CartOrder;

class WC_Gateway_CardinalPM_Order_Management {

    protected function _get_existing_cart_order( $order_id ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();

        $logger->info('[WC_Gateway_CardinalPM_Order_Management:_get_existing_cart_order] capturing payment for order [{order_id}]',
            array( 'order_id' => $order_id ) );

        $cartOrder = CartOrder::forCartOrderId( $order_id );

        return $cartOrder;
    }

    protected function _get_payment_method_for_existing_order( $cartOrder ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();
        $resolver = \wc_gateway_cardinalpm()->objects()->common_objects()->getPaymentMethodResolver();

        return $resolver->resolvePaymentMethodForExistingOrder( $cartOrder );
    }

    protected function _create_payment_method_order_for_existing( BasePaymentMethod $paymentMethod, CartOrder $cartOrder ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();
        $resolver = \wc_gateway_cardinalpm()->objects()->common_objects()->getPaymentMethodResolver();

        return $paymentMethod->createPaymentMethodOrderForExistingOrder( $cartOrder );
    }

    public function capture_payment( $order_id ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();

        $cartOrder = $this->_get_existing_cart_order( $order_id );
        $paymentMethod = $this->_get_payment_method_for_existing_order( $cartOrder );
        $order = $this->_create_payment_method_order_for_existing( $paymentMethod, $cartOrder );

        $result = $paymentMethod->processOrderCapture(
            $order,
            $cartOrder
        );
        $logger->info('[WC_Gateway_CardinalPM_Order_Management:capture_payment] PaymentMethod::processOrderCapture result: ' . json_encode($result));

        $success = $result->wasSuccessful();

        if( $success ) {
            $logger->info('[WC_Gateway_CardinalPM_Order_Management:capture_payment] capture success.');
            $logger->info('[WC_Gateway_CardinalPM_Order_Management:capture_payment] marking payment complete.');

            $cartOrder->markComplete( $result );
        }

        return $success;
    }

    public function void_order( $order_id ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();

        $cartOrder = $this->_get_existing_cart_order( $order_id );
        $paymentMethod = $this->_get_payment_method_for_existing_order( $cartOrder );
        $order = $this->_create_payment_method_order_for_existing( $paymentMethod, $cartOrder );

        $result = $paymentMethod->processVoid(
            $order,
            $cartOrder
        );
        $logger->info('[WC_Gateway_CardinalPM_Order_Management:void_order] PaymentMethod::processVoid result: ' . json_encode($result));

        $success = $result->wasSuccessful();

        if( $success ) {
            $logger->info('[WC_Gateway_CardinalPM_Order_Management:void_order] result success.');

            $logger->info('[WC_Gateway_CardinalPM_Order_Management:refund_payment] marking payment voided.');
            $cartOrder->markVoided( $result );
        }

        return $success;
    }

    public function can_refund_order( $order_id, $amount, $reason = '' ) {
        return true;
    }

    public function refund_order( $order_id, $amount, $reason = '' ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();

        $logger->info('[WC_Gateway_CardinalPM_Order_Management:refund_order] PaymentMethod::processRefund amount: ' . json_encode($amount));
        $logger->info('[WC_Gateway_CardinalPM_Order_Management:refund_order] PaymentMethod::processRefund reason: ' . json_encode($reason));

        $rawAmount = ((float) $amount) * 100;
        $logger->info('[WC_Gateway_CardinalPM_Order_Management:refund_order] PaymentMethod::processRefund rawAmount: ' . json_encode($rawAmount));

        $cartOrder = $this->_get_existing_cart_order( $order_id );
        $paymentMethod = $this->_get_payment_method_for_existing_order( $cartOrder );
        $order = $this->_create_payment_method_order_for_existing( $paymentMethod, $cartOrder );

        $result = $paymentMethod->processRefund(
            $order,
            $cartOrder,
            $rawAmount,
            $reason
        );
        $logger->info('[WC_Gateway_CardinalPM_Order_Management:refund_order] PaymentMethod::processRefund result: ' . json_encode($result));

        $success = $result->wasSuccessful();

        if( $success ) {
            $logger->info('[WC_Gateway_CardinalPM_Order_Management:refund_order] result success.');

            // TODO
            //$logger->info('[WC_Gateway_CardinalPM_Order_Management:refund_payment] marking payment refunded.');
            //$cartOrder->markComplete( $result );
        }

        return $success;
    }

}