<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Processors;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BaseOrderPaymentMethodResolver;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class OrderPaymentMethodResolver extends BaseOrderPaymentMethodResolver {

    public function resolvePaymentMethodForOrder(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        BaseCartOrder $cartOrder = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $paymentMethods = $this->getPaymentMethods();

        return $paymentMethods->getCurrent();
    }

    public function resolvePaymentMethodForExistingOrder(
        BaseCartOrder $cartOrder
    ) {
        $logger = $this->getLogger();
        $paymentMethods = $this->getPaymentMethods();

        $key = $cartOrder->getCardinalPaymentMethodKey();
        $logger->info('[CartProcessor::resolvePaymentMethodForExistingOrder] key: ' . json_encode($key));

        $paymentMethod = $paymentMethods->getPaymentMethodInstance($key);
        $logger->info('[CartProcessor::resolvePaymentMethodForExistingOrder] paymentMethod: ' . json_encode( $paymentMethod ));

        return $paymentMethod;
    }

}