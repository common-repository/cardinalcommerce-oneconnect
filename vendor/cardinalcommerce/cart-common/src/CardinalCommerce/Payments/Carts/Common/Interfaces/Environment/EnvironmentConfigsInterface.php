<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Environment;

interface EnvironmentConfigsInterface {

    public function getDefaultKey();
    public function getKeys();

    /*
     * @return bool
     */
    public function isValidKey( $key );

    /**
     * @return EnvironmentConfigInterface
     */
    public function getEnvironmentConfig( $key );
}