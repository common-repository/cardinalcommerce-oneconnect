<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

require_once( __DIR__ . "/../vendor/autoload.php" );

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdContext;
use \CardinalCommerce\Payments\Carts\Common\Processors as Processors;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodResult;

require_once( __DIR__ . "/SongbirdBaseTestCase.php" );
require_once( __DIR__ . "/mocks/PaymentMethodMocks.php" );
require_once( __DIR__ . "/mocks/PaymentMethodOrderMocks.php" );
require_once( __DIR__ . "/mocks/CartProcessorMocks.php" );
require_once( __DIR__ . "/mocks/CartProcessorFactoryMocks.php" );
require_once( __DIR__ . "/mocks/CartObjectsFactoryMocks.php" );

class GatewayCartProcessorTest extends SongbirdBaseTestCase {
    public function testRenderScriptBlock() {
        $logger = $this->getLoggerMock();
        $mocks = $this->getSongbirdMocks();

        $paymentMethod = PaymentMethodMocks::createPaymentMethodMock(
            $this,
            $logger,
            $mocks->cartSettings
        );

        $cartObjectsFactory = CartObjectsFactoryMocks::createCartObjectsFactoryMock(
            $this,
            $logger,
            $mocks->cartSettings
        );

        $cartObjectsFactory->expects( $this->atLeastOnce() )
            ->method( 'createPaymentDetailsPage' )
            ->will( $this->returnValue( $mocks->cartPaymentDetailsPage ));

        $cartObjectsFactory->expects( $this->atLeastOnce() )
            ->method( 'createScriptRenderer' )
            ->will( $this->returnValue( $mocks->scriptRenderer ));

        $paymentMethods = $cartObjectsFactory->createPaymentMethods();
        $songbirdProcessor = $cartObjectsFactory->createSongbirdProcessor();
        $paymentProcessor = $cartObjectsFactory->createPaymentProcessor();

        $songbirdContext = $songbirdProcessor->getSongbirdContext();

        $cartProcessor = CartProcessorMocks::createCartProcessorMock(
            $this,
            $logger,
            $mocks->cartSettings,
            $paymentMethods,
            $songbirdProcessor,
            $paymentProcessor
        );

        $cartProcessorFactory = CartProcessorFactoryMocks::createCartProcessorFactoryMock(
            $this,
            $logger,
            $mocks->cartSettings,
            $paymentMethods,
            $songbirdProcessor,
            $paymentProcessor
        );

        //$songbirdContext = $cartObjectsFactory->createSongbirdContext();

        // Prepare values

        $consumerObject = $this->createConsumerObject();

        $orderId = 222;
        $orderNumber = 'oi222-222-222';

        $paymentMethodOrder = PaymentMethodOrderMocks::createPaymentMethodOrderMock(
            $this,
            $logger,
            $mocks->cartSettings,
            $orderId,
            $orderNumber
        );

        // Expectations

        $cartSettings = $mocks->cartSettings;
        $testCase = $this;

        $cartProcessorFactory->expects( $this->once() )
            ->method( 'createCartProcessor' )
            ->will( $this->returnValue( $cartProcessor ) );

        $cartProcessor->expects( $this->atLeastOnce() )
            ->method( 'getPaymentMethod' )
            ->will( $this->returnValue( $paymentMethod ) );

        $cartOrder = $this->createCartOrderMock();

        /*
        $cartOrder->expects( $this->atLeastOnce() )
            ->method( 'getCartOrderDetails' )
            ->will( $this->returnValue( $mocks->cartOrderDetails ));
        */

        /*
        $paymentMethod->expects( $this->once() )
            ->method( 'createServerJWTPayload' )
            ->with(
                $cartOrder,
                $this->callback(function($arg_orderDetailsObject) {
                    return true;
                }),
                $consumerObject
            );
        */

        $mocks->scriptRenderer->expects( $this->once() )
            ->method( 'renderSongbirdScriptBlock' )
            ->with(
                $songbirdContext,
                $paymentMethod,
                $cartOrder
            );

        // Test

        $songbirdContext = new SongbirdContext(
            $logger,
            $mocks->cartSettings,
            $mocks->cartPaymentDetailsPage
        );

        $songbirdProcessor = new Processors\SongbirdProcessor(
            $logger,
            $mocks->cartSettings,
            $songbirdContext,
            $mocks->renderer,
            $mocks->scriptRenderer,
            $mocks->serverJWTCreator,
            $mocks->cartPaymentDetailsPage
        );

        $paymentProcessor = new Processors\PaymentProcessor(
            $logger,
            $mocks->cartSettings
        );

        $cartProcessor = $cartProcessorFactory->create();

        $cartProcessor->renderScriptBlock(
            $cartOrder,
            $consumerObject
        );
    }

}