<?php

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;


use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;
use \CardinalCommerce\Payments\Carts\Common\BaseCartProcessorFactory;
use \CardinalCommerce\Payments\Carts\Common\BaseCartProcessor;

use \CardinalCommerce\Payments\Carts\Common\Processors as Processors;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class CartProcessorMocks {

    protected static $CARTPROCESSOR_ABSTRACT_METHODS = array(
        'getPaymentMethod',
    );

    public static function createCartProcessorMock(
        \PHPUnit_Framework_TestCase $testCase,
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        CommonPaymentMethodsInterface $paymentMethods,
        Processors\SongbirdProcessor $songbirdProcessor,
        Processors\PaymentProcessor $paymentProcessor        
    ) {
        $cartProcessor = $testCase->getMockBuilder( BaseCartProcessor::class )
            ->setMethods( static::$CARTPROCESSOR_ABSTRACT_METHODS)
            ->setConstructorArgs(array(
                $logger,
                $cartSettings,
                $paymentMethods,
                $songbirdProcessor,
                $paymentProcessor
            ))
            ->getMock();

        return $cartProcessor;
    }
}