<?php
namespace CardinalCommerce\Payments\Carts\Common;

/**
 * Base Payment Method Result
 *
 * WIP
 */
abstract class BasePaymentMethodResult {
    public abstract function getCardinalPaymentMethodKey();
    public abstract function wasSuccessful();

    // NEXTREV: Are these standard enough for the base result object?
    public function getOrderNumber() {
        return null;
    }

    public function getTransactionId() {
        return null;
    }

    public function getMerchantData() {
        return null;
    }

    public function getProcessorOrderNumber() {
        return null;
    }

    public function getAuthorizationCode() {
        return null;
    }

    public function getAVSResult() {
        return null;
    }

    public function getCardCodeResult() {
        return null;
    }

    /**
     * @return object
     */
    public abstract function getMetaKeyValues();
}