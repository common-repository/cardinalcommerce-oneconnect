<?php
namespace CardinalCommerce\Payments\Carts\Common\Environment;

use CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;

class EnvironmentCentinelConfig implements EnvironmentInterfaces\EnvironmentCentinelConfigInterface {

    private $_transactionUrl;
    private $_merchantConfigUrl;
    private $_merchantReportsUrl;

    public function __construct( $data ) {
        if ( empty( $data ) ) {
            throw new \Exception(sprintf("Invalid environment centinelConfig object in class [%s] ctor: %s", __CLASS__, var_export( $data, true ) ));
        }

        if ( !property_exists( $data, "transactionUrl" ) || empty( $data->transactionUrl ) ) {
            throw new \Exception(sprintf("Invalid environment centinelConfig object in class [%s] ctor: missing transactionUrl: %s", __CLASS__, var_export( $data, true ) ));
        }

        $this->_transactionUrl = $data->transactionUrl;
        $this->_merchantConfigUrl = null;
        $this->_merchantReportsUrl = null;

        if ( property_exists( $data, "merchantConfigUrl" ) && (!empty( $data->merchantConfigUrl )) ) {
            $this->_merchantConfigUrl = $data->merchantConfigUrl;
        }

        if ( property_exists( $data, "merchantReportsUrl" ) && (!empty( $data->merchantReportsUrl )) ) {
            $this->_merchantReportsUrl = $data->merchantReportsUrl;
        }
    }

    /**
      * @return string
      */
    public function getTransactionUrl(){
        return $this->_transactionUrl;
    }

    /**
      * @return string
      */
    public function getMerchantConfigUrl(){
        return $this->_merchantConfigUrl;
    }

    /**
      * @return string
      */
    public function getMerchantReportsUrl(){
        return $this->_merchantReportsUrl;
    }
}