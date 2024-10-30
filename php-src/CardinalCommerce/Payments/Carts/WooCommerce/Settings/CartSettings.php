<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\PaymentAuthTypes;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;

use \CardinalCommerce\Payments\Credentials\CentinelCredentials;
use \CardinalCommerce\Payments\Credentials\APICredentials;

/**
 * Implementation of CardinalCommerce\Payments\Carts\Common\Interfaces\Cart\CartSettingsInterface for WooCommerce
 */
class CartSettings implements CartInterfaces\CartSettingsInterface {
    private $_centinelCredentials;
    private $_apiCredentials;
    private $_songbirdLoggingConfigured;
    private $_paymentAuthType;

    public function __construct() {
        $pluginSettings = \wc_gateway_cardinalpm()->objects()->plugin_settings();

        $options = $pluginSettings->options();
        $environmentKey = $pluginSettings->environment_key();
        $environmentConfig = $pluginSettings->environment_config();

        $centinelEnvironment = $environmentConfig->getCentinelConfig();
        $songbirdEnvironment = $environmentConfig->getSongbirdConfig();

        $centinelCredentials = new CentinelCredentials(
            $centinelEnvironment->getTransactionUrl(),
            intval( $options->centinelTimeout ),
            $options->centinelProcessorId,
            $options->centinelMerchantId,
            $options->centinelTransactionPwd
        );

        $apiCredentials = new APICredentials(
            $options->apiIdentifier,
            $options->orgUnitIdentifier,
            $options->apiKey
        );

        $this->_centinelCredentials = $centinelCredentials;
        $this->_apiCredentials = $apiCredentials;

        $this->_ccaEnabled = $options->ccaEnabled;
        $this->_songbirdLoggingEnabled = $options->songbirdLoggingEnabled;
        $this->_paymentAuthType = $options->paymentAuthType;
    }

    public function __toString() {
        return sprintf('Settings [ %s ]', json_encode($this) );
    }

    public function getEnvironmentKey() {
        return \wc_gateway_cardinalpm()->objects()->plugin_settings()->environment_key();
    }

    public function getAPICredentials() {
        return $this->_apiCredentials;
    }

    public function getCentinelCredentials() {
        return $this->_centinelCredentials;
    }

    public function getSongbirdLoggingEnabled() {
        return $this->_songbirdLoggingEnabled;
    }

    public function getCCAEnabled() {
        return $this->_ccaEnabled;
    }

    public function getPaymentAuthType() {
        return $this->_paymentAuthType;
    }
}

