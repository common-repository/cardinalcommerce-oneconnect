<?php
namespace CardinalCommerce\Payments\Carts\Common\Exceptions;

class CommonPaymentMethodNotSupportedException extends \Exception {

    private $_key;

    public function __construct($key) {
        $this->_key = $key;

        parent::__construct("The payment method with key {$key} is not supported.");
    }

    public function __toString() {
        return "The payment method with key {$key} is not supported.";
    }
}