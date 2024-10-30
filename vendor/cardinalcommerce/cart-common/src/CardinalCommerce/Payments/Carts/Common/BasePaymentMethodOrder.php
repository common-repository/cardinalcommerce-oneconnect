<?php
namespace CardinalCommerce\Payments\Carts\Common;

/**
 * Base Order object, representing an Order being processed
 * by a payment method implementation.
 *
 * WIP
 */
abstract class BasePaymentMethodOrder {
    public abstract function getOrderNumber();
    public abstract function getOrderDetailsObject();
}