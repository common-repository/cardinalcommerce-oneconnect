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

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class PaymentMethodMocks {

    protected static $COMMONPAYMENTMETHODS_METHODS = array(
        'getAvailableKeys',
        'getCurrentKey',
        'getCurrent',
        'getPaymentMethodInfo',
        'getPaymentMethodInstance'
    );

    protected static $PAYMENTMETHOD_ABSTRACT_METHODS = array(
        'createServerJWTPayload',
        'createPaymentMethodOrder',
        'createPaymentMethodOrderFromResponse',
        'processOrderAuthorization',
        'processOrderCapture',
        'processRefund'
    );

    public static function createPaymentMethodsMock(
        \PHPUnit_Framework_TestCase $testCase,
        LoggerInterface $logger,
        BasePaymentMethod $paymentMethod = null
    ) {
        $paymentMethods = $testCase->getMockBuilder( CommonPaymentMethodsInterface::class )
            ->setMethods( static::$COMMONPAYMENTMETHODS_METHODS)
            ->getMock();

        if ( $paymentMethod != null ) {
            $paymentMethods->expects( $testCase->atLeastOnce() )
                ->method( 'getCurrent' )
                ->will( $testCase->returnValue( $paymentMethod ));
        }

        return $paymentMethods;
    }

    public static function createPaymentMethodMock(
        \PHPUnit_Framework_TestCase $testCase,
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings
    ) {
        $paymentMethod = $testCase->getMockBuilder( BasePaymentMethod::class )
            ->setMethods( static::$PAYMENTMETHOD_ABSTRACT_METHODS)
            ->setConstructorArgs(array(
                $logger,
                $cartSettings
            ))
            ->getMock();

        return $paymentMethod;
    }
}