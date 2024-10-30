<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use CardinalCommerce\Payments\Carts\Common\CommonPaymentMethodKeys;

class AdminSettings {

    private function getEnvironmentOptions() {
        $environmentConfigs = \wc_gateway_cardinalpm()->objects()->environments();
        $keys = $environmentConfigs->getKeys();

        if ( count( $keys) < 2 ) {
            return (object) array();
        }

        $defaultKey = $environmentConfigs->getDefaultKey();
        $options = (object) array();

        foreach( $keys as $key ) {
            $environmentConfig = $environmentConfigs->getEnvironmentConfig( $key );
            $title = $environmentConfig->getTitle();

            $options->$key = __( $title, 'wc-cardinalprocessormodule' );
        }

        return array(
            'environment' => array(
                'title' => __('Chosen Environment', 'wc-cardinalprocessormodule'),
                'type' => 'select',
                'description' => __('Choose environment to process your transaction.', 'wc-cardinalprocessormodule'),
                'options' => $options,
                'default' => $defaultKey
            )
        );
    }

    private function getPaymentMethodSettings() {
        $paymentMethods = \wc_gateway_cardinalpm()->objects()->common_objects()->getPaymentMethods();
        $keys = $paymentMethods->getAvailableKeys();
        $defaultKey = $keys[0];

        // Payment methods
        $select = array();
        if ( count( $keys ) > 1 ) {
            $options = (object) array ();
            foreach( $keys as $key ) {
                $paymentMethodInfo = $paymentMethods->getPaymentMethodInfo( $key );
                $title = $paymentMethodInfo->title;

                $options->$key = __( $title, 'wc-cardinalprocessormodule' );
            }

            $select['paymentMethodSelect'] = array(
                'title' => __('Cardinal Payment Method', 'wc-cardinalprocessormodule'),
                'type' => 'select',
                'description' => __('Select Cardinal payment method or gateway', 'wc-cardinalprocessormodule'),
                'options' => $options,
                'default' => $defaultKey
            );
        }

        return $select;
    }

    /**
    * WooCommerce Settings for Cardinal Processor Module Gateway
    */
    public function form_fields() {
        $mainOptions = array(

            'enabled' => array(
                'title'   => esc_html__( 'Enable / Disable', 'woocommerce-plugin-framework' ),
                'label'   => esc_html__( 'Enable this gateway', 'woocommerce-plugin-framework' ),
                'type'    => 'checkbox',
                'default' => 'yes',
            ),

            'ccaEnabled' => array(
                'title' => __('Enable / Disable CCA', 'woocommerce'),
                'label' => __('Enable Cardinal Consumer Authentication', 'wc-cardinalprocessormodule'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'yes'
            ),

            'songbirdLoggingEnabled' => array(
                'title' => __('Browser Logging', 'woocommerce'),
                'label' => __('Enable logging in CardinalCruise', 'wc-cardinalprocessormodule'),
                'type' => 'checkbox',
                'description' => __('Whether to enable logging on the checkout page for CardinalCruise. You will need to open your browser console during checkout to see this output.', 'wc-cardinalprocessormodule'),
                'desc_tip' => true,
                'default' => 'yes'
            ),

            'paymentAuthType' => array(
                'title' => __('Payment Action', 'wc-cardinalprocessormodule'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => __('Choose whether you wish to capture funds immediately or authorize payment only. (This will apply to all products.)', 'wc-cardinalprocessormodule' ),
                'default' => 'AUTH_ONLY',
                'desc_tip' => true,
                'options' => array(
                    'AUTH_ONLY' => __('Authorize', 'wc-cardinalprocessormodule'),
                    'AUTH_CAPTURE' => __('Sale', 'wc-cardinalprocessormodule'),
                )
            )
        );

        $environmentOptions = $this->getEnvironmentOptions();
        $paymentMethodSettings = $this->getPaymentMethodSettings();

        $centinelOptions = array(
            'processorModuleTimeout' => array(
                'title' => __('Centinel Timeout (ms)', 'wc-cardinalprocessormodule'),
                'type' => 'text',
                'description' => __('Maximum time to wait for a response from Centinel API in milliseconds.', 'wc-cardinalprocessormodule'),
                'desc_tip' => true,
            )
        );

        $apiCreds = array(
            'apiIdentifier' => array(
                'title' => __('API Identifier', 'wc-cardinalprocessormodule'),
                'type' => 'text',
                'description' => __('Enter the API Identifier given to you when you created your API key.', 'wc-cardinalprocessormodule'),
                'desc_tip' => true,
            ),

            'orgUnitId' => array(
                'title' => __('API OrgUnitId', 'wc-cardinalprocessormodule'),
                'type' => 'text',
                'description' => __('Enter the OrgUnitId given to you when you created your API key.', 'wc-cardinalprocessormodule'),
                'desc_tip' => true,
            ),

            'apiKey' => array(
                'title' => __('API Key', 'wc-cardinalprocessormodule'),
                'type' => 'text',
                'description' => __('Enter the API key you created.', 'wc-cardinalprocessormodule'),
                'desc_tip' => true,
            )
        );

        $centinelCreds = array(
            'processorModuleProcessorId' => array(
                'title' => __('Cardinal Processor ID', 'wc-cardinalprocessormodule'),
                'type' => 'text',
                'description' => __('Enter your Centinel Processor ID.', 'wc-cardinalprocessormodule'),
                'desc_tip' => true,
            ),

            'processorModuleMerchantId' => array(
                'title' => __('Cardinal Merchant ID', 'wc-cardinalprocessormodule'),
                'type' => 'text',
                'description' => __('Enter your Centinel Merchant ID.', 'wc-cardinalprocessormodule'),
                'desc_tip' => true,
            ),

            'processorModuleTransactionPwd' => array(
                'title' => __('Transaction Password', 'wc-cardinalprocessormodule'),
                'type' => 'text',
                'description' => __('Enter your Centinel Transaction Password.', 'wc-cardinalprocessormodule'),
                'desc_tip' => true,
            )
        );

        return array_merge(
            $mainOptions,
            $paymentMethodSettings,
            $environmentOptions,
            $centinelOptions,
            $apiCreds,
            $centinelCreds
        );
    }
}
