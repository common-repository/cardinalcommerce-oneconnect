<?php
namespace CardinalCommerce\Payments\Carts\Common\Payment\Implementations\ProcessorModuleV2;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\CommonPaymentMethodKeys;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodResult;

use \CardinalCommerce\Payments\Interfaces\CentinelClientInterface;

class PaymentMethodResult extends BasePaymentMethodResult {
    private $_response;
    private $_orderNumber;

    public function __construct(
        $response,
        $orderNumber = null
    ) {
        $this->_response = $response;
        $this->_orderNumber = $orderNumber;
    }

    public function getCardinalPaymentMethodKey() {
        return CommonPaymentMethodKeys::CARDINAL_PROCESSOR_MODULE_V2;
    }

    public function wasSuccessful() {
        // Centinel uses ErrorNo
        return $this->_response->ErrorNo === 0 || $this->_response->ErrorNo === '0';
    }

    public function getOrderNumber() {
        return $this->_orderNumber;
    }
    
    public function getTransactionId() {
        return $this->_responseTransactionId;
    }

    public function getMerchantData() {
        return $this->_response->MerchantData;
    }

    public function getProcessorOrderNumber() {
        return $this->_response->ProcessorOrderNumber;
    }

    public function getProcessorTransactionId() {
        return $this->_response->TransactionId;
    }

    public function getProcessorOrderId() {
        return $this->_response->OrderId;
    }

    public function getAuthorizationCode() {
        return $this->_response->AuthorizationCode;
    }

    public function getAVSResult() {
        return $this->_response->AVSResult;
    }

    public function getCardCodeResult() {
        return $this->_response->CardCodeResult;
    }

    public function getMetaKeyValues() {
        return (object) array(
            PaymentMethodOrderMetaKeys::CARDINAL_PROCESSOR_MODULE_RESULT_ORDER_NUMBER => $this->getOrderNumber(),
            PaymentMethodOrderMetaKeys::CARDINAL_PROCESSOR_MODULE_RESULT_TRANSACTION_ID => $this->getTransactionId(),
            PaymentMethodOrderMetaKeys::CARDINAL_PROCESSOR_MODULE_RESULT_MERCHANT_DATA => $this->getMerchantData(),
            PaymentMethodOrderMetaKeys::CARDINAL_PROCESSOR_MODULE_RESULT_PROCESSOR_ORDER_NUMBER => $this->getProcessorOrderNumber(),
            PaymentMethodOrderMetaKeys::CARDINAL_PROCESSOR_MODULE_RESULT_AUTHORIZATION_CODE => $this->getAuthorizationCode(),
            PaymentMethodOrderMetaKeys::CARDINAL_PROCESSOR_MODULE_RESULT_AVSRESULT => $this->getAVSResult(),
            PaymentMethodOrderMetaKeys::CARDINAL_PROCESSOR_MODULE_RESULT_CARD_CODE_RESULT => $this->getCardCodeResult()
        );
    }
}