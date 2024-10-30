<?php
namespace CardinalCommerce\Payments\Carts\Common;

use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;

/**
 * Implements a payment method factory.
 *
 * WIP
 */
abstract class BasePaymentMethodFactory {

    /**
     * Create an instance of this payment method
     *
     * @param $key string The Cardinal payment method key (defined in CardinalCommerce\Payments\CardinalPaymentMethodKeys)
     *
     * @return BasePaymentMethod The created PaymentMethod instance
     */
    public abstract function create($key);

}