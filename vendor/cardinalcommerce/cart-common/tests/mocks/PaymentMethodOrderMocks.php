<?php

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;

class PaymentMethodOrderMocks {

    protected static $PAYMENTMETHODORDER_ABSTRACT_METHODS = array(
        'getOrderNumber',
        'getOrderDetailsObject'
    );

    public static function createPaymentMethodOrderMock(
        \PHPUnit_Framework_TestCase $testCase,
        LoggerInterface $logger
    ) {
        $paymentMethodOrder = $testCase->getMockBuilder( BasePaymentMethodOrder::class )
            ->setMethods( static::$PAYMENTMETHODORDER_ABSTRACT_METHODS)
            ->getMock();

        return $paymentMethodOrder;
    }
}