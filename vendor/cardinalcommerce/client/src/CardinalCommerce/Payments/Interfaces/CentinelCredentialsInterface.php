<?php
namespace CardinalCommerce\Payments\Interfaces;

interface CentinelCredentialsInterface {
    public function getTransactionUrl();
    public function getTimeout();
    public function getProcessorId();
    public function getMerchantId();
    public function getTransactionPwd();
}