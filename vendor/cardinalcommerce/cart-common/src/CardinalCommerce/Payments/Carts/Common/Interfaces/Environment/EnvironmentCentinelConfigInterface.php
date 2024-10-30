<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Environment;

interface EnvironmentCentinelConfigInterface {

    /**
      * @return string
      */
    public function getTransactionUrl();

    /**
      * @return string
      */
    public function getMerchantConfigUrl();

    /**
      * @return string
      */
    public function getMerchantReportsUrl();
}