<?php
namespace CardinalCommerce\Payments\Carts\Common\Environment;

use \Psr\Log\LoggerInterface;
use CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;

class EnvironmentConfigs implements EnvironmentInterfaces\EnvironmentConfigsInterface {
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    private $_environmentConfigs;
    private $_defaultKey;
    private $_envKeys;

    public function __construct(
        LoggerInterface $logger,
        $data
    ) {
        $this->_environmentConfigs = array();

        if ( empty( $data ) || empty( $data->environments ) ) {
            throw new \Exception(sprintf("Invalid environment configs object in class [%s] ctor: %s", __CLASS__, var_export( $data, true ) ));
        }

        $environments = $data->environments;
        $envKeys = array_keys( get_object_vars($environments) );

        if ( count( $envKeys ) == 0 ) {
            throw new \Exception(sprintf("Invalid environment configs object in class [%s] ctor: empty environments dictionary", __CLASS__ ));
        }

        $defaultKey = ( property_exists( $data, "defaultEnvironmentKey" ) && !empty( $data->defaultEnvironmentKey ) ) ?
            $data->defaultEnvironmentKey : $envKeys[0];

        foreach( $environments as $key => $envData ) {
            $logger->info('[EnvironmentConfigs::ctor] add environment config for key [{{key}}]', array('key' => $key));
            $this->_environmentConfigs[ $key ] = new EnvironmentConfig( $key, $envData );
        }

        $this->_logger =  $logger;
        $this->_defaultKey = $defaultKey;
        $this->_envKeys = $envKeys;
    }

    public function getDefaultKey() {
        return $this->_defaultKey;
    }

    public function getKeys() {
        return $this->_envKeys;
    }

    /*
     * @return bool
     */
    public function isValidKey( $key ) {
        return in_array( $key, $this->_envKeys );
    }

    public function getEnvironmentConfig( $key ) {
        $this->_logger->info('[EnvironmentConfigs::getEnvironmentConfig] getting environment config for key [{key}]', array( 'key' => $key ));

        $this->_logger->info('[EnvironmentConfigs::getEnvironmentConfig] _environmentConfigs: ' . var_export( $this->_environmentConfigs, true ));

        if ( ! array_key_exists( $key, $this->_environmentConfigs ) || empty ( $this->_environmentConfigs[$key] ) ) {
            throw new \Exception(sprintf("Invalid environment key [%s] in [%s::%s]. (_environmentConfigs: %s)", $key, __CLASS__, __METHOD__, var_export( $this->_environmentConfigs, true ) ));
        }

        return $this->_environmentConfigs[ $key ];
    }
}