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
use \CardinalCommerce\Payments\Carts\Common\BaseCartObjectsFactory;
use \CardinalCommerce\Payments\Carts\Common\BaseCartProcessorFactory;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class CartObjectsFactoryMocks {

    protected static $CARTOBJECTSFACTORY_ABSTRACT_METHODS = array(
        'createPaymentDetailsPage',
        'createScriptRenderer'
    );

    public static function createCartObjectsFactoryMock(
        \PHPUnit_Framework_TestCase $testCase,
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings
    ) {
        $cartObjectsFactory = $testCase->getMockBuilder( BaseCartObjectsFactory::class )
            ->setMethods( static::$CARTOBJECTSFACTORY_ABSTRACT_METHODS)
            ->setConstructorArgs(array(
                $logger,
                $cartSettings
            ))
            ->getMock();

        return $cartObjectsFactory;
    }
}