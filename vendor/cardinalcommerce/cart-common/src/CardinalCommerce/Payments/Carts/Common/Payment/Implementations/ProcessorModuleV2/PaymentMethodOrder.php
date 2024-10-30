<?php
namespace CardinalCommerce\Payments\Carts\Common\Payment\Implementations\ProcessorModuleV2;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;

class PaymentMethodOrder extends BasePaymentMethodOrder {

    private $_cartOrder;
    private $_consumerObject;
    private $_orderDetailsObject;
    private $_responseObject;

    private function __construct(
        BaseCartOrder $cartOrder,
        PaymentObjects\OrderDetails $orderDetailsObject = null,
        PaymentObjects\Response $responseObject = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $cartOrderDetails = $cartOrder->getCartOrderDetails();

        $amount = $cartOrderDetails->getOrderAmountForMidas();
        $currencyCode = $cartOrderDetails->getOrderNumericCurrencyForMidas();
        $orderDescription = $cartOrderDetails->getOrderDescription();

        $this->_cartOrder = $cartOrder;
        $this->_consumerObject = $consumerObject;
        $this->_orderDetailsObject = $orderDetailsObject;
        $this->_responseObject = $responseObject;
    }

    public static function forPayment(
        BaseCartOrder $cartOrder,
        PaymentObjects\OrderDetails $orderDetailsObject,
        PaymentObjects\Response $responseObject,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        return new self(
            $cartOrder,
            $orderDetailsObject,
            $responseObject,
            $consumerObject
        );
    }

    public static function forExistingOrder(
        BaseCartOrder $cartOrder
    ) {
        return new self(
            $cartOrder
        );
    }

    public function getOrderNumber() {
        return $this->_cartOrder->getOrderNumber();
    }

    public function getOrderDetailsObject() {
        return $this->_cartOrder->getCartOrderDetails();
    }


    public function getPaymentExtensions() {
      if ($this->_responseObject == null) {
          return null;
      }
      return $this->_responseObject->Payment->ExtendedData;
    }


    public function getAuthorizationProcessorObject() {
        if ($this->_responseObject == null) {
            return null;
        }
        return $this->_responseObject->AuthorizationProcessor;
    }
}
