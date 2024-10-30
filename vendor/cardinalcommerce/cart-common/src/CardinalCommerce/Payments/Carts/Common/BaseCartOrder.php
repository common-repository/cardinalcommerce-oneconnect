<?php
namespace CardinalCommerce\Payments\Carts\Common;

use \CardinalCommerce\Payments\Objects as PaymentObjects;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodResult;

abstract class BaseCartOrder {

    public abstract function getCartOrderDetails();
    public abstract function getOrderNumber();

    public abstract function storePaymentMethodResultData( BasePaymentMethodResult $result );

    // NEXTREV: Move call to store payment method key out of CheckoutOrderHooks?
    //public abstract function storeCardinalPaymentMethodKey( $key );

    public abstract function storeCardinalProcessorTransactionId( $transactionId );
    public abstract function storeCardinalProcessorOrderId( $transactionId );

    public abstract function getCardinalPaymentMethodKey();
    public abstract function getCardinalProcessorTransactionId();
    public abstract function getCardinalProcessorOrderId();

    public abstract function markInProcessing(
        BasePaymentMethodResult $result
    );

    public abstract function markComplete(
        BasePaymentMethodResult $result
    );

    public abstract function reduceStock();

    public abstract function clearCart();
}