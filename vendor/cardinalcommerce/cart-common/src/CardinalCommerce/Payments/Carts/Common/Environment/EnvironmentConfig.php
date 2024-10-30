<?php
namespace CardinalCommerce\Payments\Carts\Common\Environment;

use CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;

class EnvironmentConfig {

    private $_key;
    private $_title;
    private $_description;

    /**
     * @var EnvironmentInterfaces\EnvironmentCentinelConfigInterface
     */
    private $_centinelConfig;

    /**
     * @var EnvironmentInterfaces\EnvironmentSongbirdConfigInterface
     */
    private $_songbirdConfig;

    public function __construct( $key, $data ) {
        if ( empty( $data ) ) {
            throw new \Exception(sprintf("Invalid environment config object in class [%s] ctor: %s", __CLASS__, var_export( $data, true ) ));
        }

        if ( !property_exists( $data, "centinelConfig" ) || empty( $data->centinelConfig ) ) {
            throw new \Exception(sprintf("Invalid environment config object in class [%s] ctor: missing centinelConfig", __CLASS__, var_export( $data, true ) ));
        }

        if ( !property_exists( $data, "songbirdConfig" ) || empty( $data->songbirdConfig ) ) {
            throw new \Exception(sprintf("Invalid environment config object in class [%s] ctor: missing songbirdConfig", __CLASS__, var_export( $data, true ) ));
        }

        $this->_centinelConfig = new EnvironmentCentinelConfig( $data->centinelConfig );
        $this->_songbirdConfig = new EnvironmentSongbirdConfig( $data->songbirdConfig );

        $title = ( property_exists( $data, "title" ) && !empty( $data->title )) ? $data->title : ucfirst(strtolower( $key ));
        $description = ( property_exists( $data, "description" ) && !empty( $data->description )) ? $data->description : "The ${title} environment";

        $this->_key = $key;
        $this->_title = $title;
        $this->_description = $description;
    }

    public function getEnvironmentKey() {
        return $this->_key;
    }

    public function getTitle() {
        return $this->_title;
    }

    public function getDescription() {
        return $this->_description;
    }

    /**
     * @return EnvironmentInterfaces\EnvironmentCentinelConfigInterface
     */
    public function getCentinelConfig() {
        return $this->_centinelConfig;
    }

    /**
     * @return EnvironmentInterfaces\EnvironmentSongbirdConfigInterface
     */
    public function getSongbirdConfig() {
        return $this->_songbirdConfig;
    }
}