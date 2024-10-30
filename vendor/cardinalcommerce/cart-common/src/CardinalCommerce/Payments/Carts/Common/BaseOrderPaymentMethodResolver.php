<?php
namespace CardinalCommerce\Payments\Carts\Common;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

abstract class BaseOrderPaymentMethodResolver {
    private $_logger;
    private $_paymentMethods;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        CommonPaymentMethodsInterface $paymentMethods
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
        $this->_paymentMethods = $paymentMethods;
    }

    protected function getLogger() {
        return $this->_logger;
    }

    protected function getSettings() {
        return $this->_settings;
    }

    protected function getPaymentMethods() {
        return $this->_paymentMethods;
    }

    public abstract function resolvePaymentMethodForOrder(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,        
        BaseCartOrder $cartOrder = null,
        PaymentObjects\Consumer $consumerObject = null
    );

    public abstract function resolvePaymentMethodForExistingOrder(
        BaseCartOrder $cartOrder
    );
}