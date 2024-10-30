<?php
namespace CardinalCommerce\Payments\Carts\Common\Payment\Implementations\ProcessorModuleV2;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodFactory;

use \CardinalCommerce\Payments\Interfaces\CentinelClientInterface;

use \CardinalCommerce\Client\Centinel\CentinelClient;

/**
 * Factory for creating ProcessorModule V2 payment method
 */
class PaymentMethodFactory extends BasePaymentMethodFactory {

    /**
     * Create an instance of this payment method
     *
     * @param $key string The Cardinal payment method key (defined in CardinalCommerce\Payments\CardinalPaymentMethodKeys)
     *
     * @return BasePaymentMethod The created PaymentMethod instance
     */
    public function create($key) {
        return new PaymentMethod();
    }
}