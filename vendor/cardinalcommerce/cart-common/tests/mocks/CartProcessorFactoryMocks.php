<?php

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Processors as Processors;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;
use \CardinalCommerce\Payments\Carts\Common\BaseCartProcessorFactory;
use \CardinalCommerce\Payments\Carts\Common\BaseCartObjectsFactory;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class CartProcessorFactoryMocks {

    protected static $CARTPROCESSORFACTORY_ABSTRACT_METHODS = array(
        'createCartProcessor'
    );

    public static function createCartProcessorFactoryMock(
        \PHPUnit_Framework_TestCase $testCase,
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        // Inject
        CommonPaymentMethodsInterface $paymentMethods,
        Processors\SongbirdProcessor $songbirdProcessor,
        Processors\PaymentProcessor $paymentProcessor
    ) {
        $cartProcessorFactory = $testCase->getMockBuilder( BaseCartProcessorFactory::class )
            ->setMethods( static::$CARTPROCESSORFACTORY_ABSTRACT_METHODS)
            ->setConstructorArgs(array(
                $logger,
                $cartSettings,
                // Inject
                $paymentMethods,
                $songbirdProcessor,
                $paymentProcessor
            ))
            ->getMock();

        return $cartProcessorFactory;
    }
}