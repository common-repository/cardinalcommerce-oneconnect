<?php
// No namespace

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use \Psr\Log\LoggerInterface;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\PaymentAuthTypes;

use \CardinalCommerce\Payments\Carts\WooCommerce\Settings\CartSettings;

class WC_Gateway_CardinalPM_Settings extends WC_Integration {

    /**
     * @var object
     */
    private $_options;

    /**
     * @var string
     */
    private $_environmentKey;

    /**
     * @var EnvironmentInterfaces\EnvironmentConfigInterface
     */
    private $_environmentConfig;

    public function __construct(
        $paymentMethodId
    ) {
        $this->id = $paymentMethodId;

        parent::init_settings();

        $logger = \wc_gateway_cardinalpm()->objects()->logger();
        $environments = \wc_gateway_cardinalpm()->objects()->environments();
        $defaultKey = $environments->getDefaultKey();

        $options = (object) [
            'environmentKey' => $this->get_option('environment', $defaultKey),

            'apiIdentifier' => $this->get_option( 'apiIdentifier', '' ),
            'apiKey' => $this->get_option('apiKey', ''),
            'orgUnitIdentifier' => $this->get_option('orgUnitId', ''),

            'centinelTransactionUrl' => $this->get_option('processorModuleTransactionUrl', ''),
            'centinelTimeout' => $this->get_option('processorModuleTimeout', ''),
            'centinelProcessorId' => $this->get_option('processorModuleProcessorId', ''),
            'centinelMerchantId' => $this->get_option('processorModuleMerchantId', ''),
            'centinelTransactionPwd' => $this->get_option('processorModuleTransactionPwd', ''),

            'ccaEnabled' => 'yes' === $this->get_option('ccaEnabled', 'no'),
            'songbirdLoggingEnabled' => 'yes' === $this->get_option('songbirdLoggingEnabled', 'no'),
            'paymentAuthType' => PaymentAuthTypes::AUTH_CAPTURE === $this->get_option('paymentAuthType', PaymentAuthTypes::AUTH_ONLY) ?
                PaymentAuthTypes::AUTH_CAPTURE : PaymentAuthTypes::AUTH_ONLY
        ];
        $logger->debug('[WC_Gateway_CardinalPM_Settings::ctor] options: ' . json_encode($options));

        $this->_options = $options;

        $environmentKey = $options->environmentKey;
        $logger->debug('[WC_Gateway_CardinalPM_Settings::ctor] environmentKey: ' . json_encode($environmentKey));

        $environmentConfig = \wc_gateway_cardinalpm()->objects()->environments()
            ->getEnvironmentConfig( $environmentKey );

        $this->_environmentKey = $environmentKey;
        $this->_environmentConfig = $environmentConfig;
        $this->_options = $options;
    }

    public function options() {
        return $this->_options;
    }

    public function environment_key() {
        return $this->_environmentKey;
    }

    public function environment_config() {
        return $this->_environmentConfig;
    }
}