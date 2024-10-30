<?php
// No namespace

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use \Psr\Log\LoggerInterface;
use CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;

use \CardinalCommerce\Payments\Carts\Common\Environment\EnvironmentConfigs;

class WC_Gateway_CardinalPM_Environments {

    public static function loadEnvironments() {
        return new EnvironmentConfigs(
            \wc_gateway_cardinalpm()->objects()->logger(),
            json_decode( file_get_contents( sprintf( "%senvironments.json", \wc_gateway_cardinalpm()->config_dir() ) ) )
        );
    }
}