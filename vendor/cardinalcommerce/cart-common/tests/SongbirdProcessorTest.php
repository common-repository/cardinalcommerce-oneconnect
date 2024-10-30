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

use \CardinalCommerce\Payments\Carts\Common\Processors as Processors;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodResult;

require_once( __DIR__ . "/SongbirdBaseTestCase.php" );
require_once( __DIR__ . "/PaymentMethodMocks.php" );
require_once( __DIR__ . "/PaymentMethodOrderMocks.php" );

class SongbirdProcessorTest extends SongbirdBaseTestCase {
    public function testGetSongbirdContext() {
        $logger = $this->getLoggerMock();
        $mocks = $this->getSongbirdMocks();

        /*
        $songbirdProcessor = $this->createSongbirdProcessorMock(
            $logger,
            $mocks->cartSettings,
            $mocks->songbirdContext,
            $mocks->renderer,
            $mocks->serverJWTCreator,
            $mocks->paymentDetailsPage
        );
        */

        $songbirdContext = $this->createSongbirdContextMock(
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

        $this->assertEquals( $songbirdProcessor->getSongbirdContext(), $songbirdContext );
    }

    public function testRenderScriptBlock() {
        $logger = $this->getLoggerMock();
        $mocks = $this->getSongbirdMocks();

        $paymentMethod = PaymentMethodMocks::createPaymentMethodMock(
            $this,
            $logger,
            $mocks->cartSettings
        );

        $songbirdContext = $this->createSongbirdContextMock(
            $logger,
            $mocks->cartSettings,
            $mocks->cartPaymentDetailsPage
        );

        $scriptRenderer = $mocks->scriptRenderer;

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

        $scriptRenderer->expects( $this->once() )
            ->method( 'renderSongbirdScriptBlock' )
            ->with(
                $this->callback(function($arg_songbirdContext) use ($songbirdContext) {
                    return $arg_songbirdContext === $songbirdContext;
                }),
                $paymentMethod,
                $cartOrder
            );

        // Test

        $songbirdProcessor = new Processors\SongbirdProcessor(
            $logger,
            $mocks->cartSettings,
            $songbirdContext,
            $mocks->renderer,
            $mocks->scriptRenderer,
            $mocks->serverJWTCreator,
            $mocks->cartPaymentDetailsPage
        );

        $songbirdProcessor->renderScriptBlock(
            $paymentMethod,
            $cartOrder,
            $consumerObject
        );
    }
    
}