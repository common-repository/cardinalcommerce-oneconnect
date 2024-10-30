<?php
namespace CardinalCommerce\Client\ProcessorModule;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Interfaces\APICredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelCredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelClientInterface;

class ProcessorModuleClientBuilder {

    private $_logger;
    private $_centinelClient = null;
    private $_apiCreds = null;
    private $_centinelCreds = null;

    public function __construct( LoggerInterface $logger ) {
        $this->_logger = $logger;
    }

    public function setCentinelClient( CentinelClientInterface $centinelClient ) {
        if ( $this->_centinelClient != null ) {
            throw new Error('Centinel client already set.');
        }

        $this->_centinelClient = $centinelClient;
        return $this;
        
    }

    public function setAPICredentials( APICredentialsInterface $apiCreds ) {
        if ( $this->_apiCreds != null ) {
            throw new Error('API Credentials already set.');
        }

        $this->_apiCreds = $apiCreds;
        return $this;
    }

    public function setCentinelCredentials( CentinelCredentialsInterface $centinelCreds ) {
        if ( $this->_centinelCreds != null ) {
            throw new Error('Centinel Credentials already set.');
        }

        $this->_centinelCreds = $centinelCreds;
        return $this;
    }

    public function build() {
        if ( $this->_centinelClient == null ) {
            throw new Error('Centinel client required.');
        }

        if ( $this->_apiCreds == null ) {
            throw new Error('API Credentials required.');
        }

        return new ProcessorModuleClient(
            $this->_logger,
            $this->_centinelClient,
            $this->_apiCreds,
            $this->_centinelCreds
        );
    }
}
