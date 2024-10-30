<?php
namespace CardinalCommerce\Payments\Carts\Common;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common as PaymentsCommon;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Songbird as PaymentsSongbird;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Cardinal as PaymentsCardinal;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Payment\Implementations as PaymentMethods;

class CommonPaymentMethods implements PaymentsCommon\CommonPaymentMethodsInterface {
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var array
     */
    private $_factories;

    /**
     * @var array
     */
    private $_instances;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->_logger = $logger;

        $this->_factories = array();
        $this->_instances = array();
    }

    /**
     * @return array
     */
    public function getAvailableKeys() {
        // NEXTREV: Implement multiple Cardinal payment methods.
        return array (
            CommonPaymentMethodKeys::CARDINAL_PROCESSOR_MODULE_V2
        );
    }

    /**
     * @return string
     */
    public function getCurrentKey() {
        $this->_logger->info('[CardinalPaymentMethods::getSelectedPaymentMethodKey] getting selected payment method');

        // NEXTREV: Implement multiple Cardinal payment methods.
        $key = CommonPaymentMethodKeys::CARDINAL_PROCESSOR_MODULE_V2;
        $this->_logger->info('[CardinalPaymentMethods::getSelectedPaymentMethodKey] getting selected payment method: key: {key}', array( 'key' => $key ));
        return $key;
    }

    private function createPaymentMethodFactory($key) {
        $logger = CommonObjects::instance()->getLogger();

        switch ($key) {
            case CommonPaymentMethodKeys::CARDINAL_PROCESSOR_MODULE_V2:
                return new PaymentMethods\ProcessorModuleV2\PaymentMethodFactory( $logger );
            default:
                throw new Exceptions\CommonPaymentMethodNotSupportedException($key);
        }
    }

    private function getPaymentMethodFactory( $key ) {
        $logger = CommonObjects::instance()->getLogger();

        $logger->info('[CommonPaymentMethods::getPaymentMethodFactory] key: ' . json_encode($key));

        if ( ! array_key_exists( $key, $this->_factories ) ) {
            $factory = $this->createPaymentMethodFactory( $key );

            $this->_factories[$key] = $factory;
        }

        return $this->_factories[$key];
    }

    public function getPaymentMethodInstance($key) {
        $logger = CommonObjects::instance()->getLogger();

        $logger->info('[CommonPaymentMethods::getPaymentMethodInstance] key: ' . json_encode($key));

        if ( ! array_key_exists( $key, $this->_factories ) ) {
            $factory = $this->getPaymentMethodFactory( $key );
            $instance = $factory->create( $key );

            $this->_instances[$key] = $instance;
        }

        return $this->_instances[$key];
    }

    /**
     * @return PaymentMethod The currently selected Cardinal PaymentMethod.
     */
    public function getCurrent() {
        $logger = CommonObjects::instance()->getLogger();

        $key = $this->getCurrentKey();
        $logger->info('[CardinalPaymentMethods::getCurrent] selected payment method: {key}', array( 'key' => $key ));

        $instance = $this->getPaymentMethodInstance($key);
        $logger->info('[CardinalPaymentMethods::getCurrent] payment method instance: {instance}', array( 'instance' => $instance ));
        return $instance;
    }

    /**
     * Return information about specific payment methods
     *
     * @param string key The payment method key
     * @return object Payment method title and description
     */
    public function getPaymentMethodInfo( $key ) {
        switch ($key) {
            case CardinalPaymentMethodKeys::CARDINAL_PROCESSOR_MODULE_V2_TOKENIZED:
                return (object) array(
                    'title' => PaymentMethods\ProcessorModuleV2\PaymentMethod::PAYMENT_METHOD_TITLE,
                    'description' => PaymentMethods\ProcessorModuleV2\PaymentMethod::PAYMENT_METHOD_DESC
                );
            default:
                return null;
        }
    }

}
