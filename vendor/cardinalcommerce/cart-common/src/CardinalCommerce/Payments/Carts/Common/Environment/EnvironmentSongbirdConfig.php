<?php
namespace CardinalCommerce\Payments\Carts\Common\Environment;

use CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;

class EnvironmentSongbirdConfig implements EnvironmentInterfaces\EnvironmentSongbirdConfigInterface {

    private $_songbirdJsUrl;

    public function __construct( $data ) {
        if ( empty( $data ) ) {
            throw new \Exception(sprintf("Invalid environment songbirdConfig object in class [%s] ctor: %s", __CLASS__, var_export( $data, true ) ));
        }

        if ( !property_exists( $data, "songbirdJsUrl" ) || empty( $data->songbirdJsUrl ) ) {
            throw new \Exception(sprintf("Invalid environment songbirdConfig object in class [%s] ctor: missing songbirdJsUrl: %s", __CLASS__, var_export( $data, true ) ));
        }

        $this->_songbirdJsUrl = $data->songbirdJsUrl;
    }

    /**
     * @return string
     */
    public function getSongbirdJsUrl() {
        return $this->_songbirdJsUrl;
    }
}