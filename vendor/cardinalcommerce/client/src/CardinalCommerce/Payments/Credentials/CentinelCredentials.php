<?php
namespace CardinalCommerce\Payments\Credentials;

use \CardinalCommerce\Payments\Interfaces\CentinelCredentialsInterface;

class CentinelCredentials implements CentinelCredentialsInterface {
    private $_transactionUrl;
    private $_timeout;
    private $_processorId;
    private $_merchantId;
    private $_transactionPwd;

    public function __construct(
        $transactionUrl,
        $timeout,
        $processorId,
        $merchantId,
        $transactionPwd
    ) {
        $this->_transactionUrl = $transactionUrl;
        $this->_timeout = $timeout;
        $this->_processorId = $processorId;
        $this->_merchantId = $merchantId;
        $this->_transactionPwd = $transactionPwd;
    }

    public function getTransactionUrl() {
        return $this->_transactionUrl;
    }

    public function getTimeout() {
        return (int) $this->_timeout;
    }

    public function getProcessorId() {
        return $this->_processorId;

    }
    public function getMerchantId() {
        return $this->_merchantId;

    }
    public function getTransactionPwd() {
        return $this->_transactionPwd;
    }

    public function __toString() {
        return json_encode((object) array(
            'processorId' => $this->_processorId,
            'merchantId' => $this->_merchantId,
            'transactionPwd' => $this->_transactionPwd
        ));
    }
}