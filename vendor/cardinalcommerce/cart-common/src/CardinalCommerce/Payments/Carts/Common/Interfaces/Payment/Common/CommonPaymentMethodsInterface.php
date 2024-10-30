<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common;

interface CommonPaymentMethodsInterface {

    /**
     */
    public function getAvailableKeys();

    /**
     */
    public function getCurrentKey();

    /**
     * @return PaymentMethod
     */
    public function getPaymentMethodInstance($key);

    /**
     * @return PaymentMethod The currently selected Cardinal PaymentMethod.
     */
    public function getCurrent();

    /**
     * Return information about specific payment methods
     *
     * @param string key The payment method key
     * @return object Payment method name and description
     */
    public function getPaymentMethodInfo( $key );
}