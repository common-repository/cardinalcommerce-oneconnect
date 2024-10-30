<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

require_once( __DIR__ . "/../vendor/autoload.php" );

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\CartSettings;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdContext;

use \CardinalCommerce\Payments\Carts\Common\Payment\Cardinal\PaymentProcessor;
use \CardinalCommerce\Payments\Carts\Common\Payment\Cardinal\Objects as CardinalPaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common as PaymentsCommon;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Songbird as PaymentsSongbird;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Cardinal as PaymentsCardinal;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Payment\BaseCartOrder;

require_once( __DIR__ . "/BasePaymentMethodsTestCase.php" );

class PaymentProcessorTest extends BasePaymentMethodsTestCase {


    /*
    private function createPaymentProcessorMock(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        // Inject
        PaymentsCommon\CommonPaymentMethodsInterface $commonPaymentMethods
    ) {
        $paymentProcessor = $this->getMockBuilder( PaymentProcessor::class )
            ->setConstructorArgs(array(
                $logger,
                $cartSettings,
                // Inject
                $commonPaymentMethods
            ))
            ->setMethods( static::$PAYMENTPROCESSOR_ABSTRACT_METHODS )
            ->getMock();

        return $paymentProcessor;
    }
    */

    private function createPaymentMethodOrderMock(
        $orderNumber,
        $orderId
    ) {
        $order = $this->getMockBuilder( CardinalPaymentObjects\BasePaymentMethodOrder::class )
            ->setMethods( static::$PAYMENTMETHODORDER_METHODS )->getMock();

        /*
        $order->expects( $this->atLeastOnce() )
            ->method( 'getOrderNumber' )
            ->will( $this->returnValue( $orderNumber ));

        $order->expects( $this->atLeastOnce() )
            ->method( 'getOrderId' )
            ->will( $this->returnValue( $orderId ));
        */

        return $order;
    }

    public function testAuthOnly() {
        $logger = $this->getLoggerMock();

        $mocks = $this->getSongbirdMocks();
        $payMocks = $this->getPaymentMethodMocks();

        $commonPaymentMethod = $payMocks->commonPaymentMethod;
        $commonPaymentMethods = $payMocks->commonPaymentMethods;
        $cardinalPaymentMethod = $payMocks->cardinalPaymentMethod;

        // Prepare Values

        $orderId = '2222';

        $order = $this->createPaymentMethodOrderMock(
            $orderId,
            null
        );

        $cartSettings = $mocks->cartSettings;

        $cartOrderDetails = $mocks->cartOrderDetails;
        $authType = CommonInterfaces\PaymentAuthTypes::AUTH_ONLY;

        $consumerObject = $this->createConsumerObject();
        $responseJWTPayload = new \stdClass;

        // Set Expectations

        $paymentMethodResult = $this->getMockBuilder( CardinalPaymentObjects\BasePaymentMethodResult::class )
            ->setMethods( static::$BASEPAYMENTMETHODRESULT_METHODS )->getMock();

        $paymentMethodResult->expects( $this->once() )
            ->method( 'wasSuccessful' )
            ->will( $this->returnValue( true ) );

        $cardinalPaymentMethod->expects( $this->once() )
            ->method( 'createOrder' )
            ->with(
                // CardinalPaymentContextInterface $ctx
                $this->callback(function($arg_paymentContext) use ($cartSettings) {
                    return $arg_paymentContext->getCartSettings()
                        === $cartSettings;
                }),
                // CartOrderDetails $cartOrderDetails
                $this->callback(function($arg_cartOrderDetails) use ($cartOrderDetails) {
                    //return $arg_cartOrderDetails === $cartOrderDetails;
                    return true;
                }),
                // PaymentObjects\Consumer $consumerObject
                $this->callback(function($arg_consumerObject) use ($consumerObject) {
                    return $arg_consumerObject === $consumerObject;
                })
            )
            ->will($this->returnValue( $order ));

        $cardinalPaymentMethod->expects( $this->once() )
            ->method( 'processOrderAuthorization' )
            ->with(
                // CardinalPaymentContextInterface $ctx
                $this->callback(function($arg_paymentContext) use ($cartSettings) {
                    return $arg_paymentContext->getCartSettings()
                        === $cartSettings;
                }),

                // BasePaymentMethodOrder $order
                $this->callback(function($arg_order) use ($order) {
                    return $arg_order === $order;
                }),

                // CartOrderDetails $cartOrderDetails
                $this->callback(function($arg_cartOrderDetails) use ($cartOrderDetails) {
                    return $arg_cartOrderDetails === $cartOrderDetails;
                }),

                // PaymentObjects\Consumer $consumerObject
                $this->callback(function($arg_consumerObject) use ($consumerObject) {
                    return true;
                    //return $arg_consumerObject === $consumerObject;
                }),

                // PaymentAuthTypes $authType (string)
                'AUTH_ONLY',

                // ResponseObject $responseObject (ResponseJWT Payload) (object)
                $this->callback(function($arg_responseJWTPayload) {
                    return true;
                })
            )
            ->will( $this->returnValue( $paymentMethodResult ) );

        $cartSettings = $mocks->cartSettings;

        $commonPaymentMethod->expects( $this->atLeastOnce() )
            ->method( 'getCardinalPaymentMethod')
            ->will( $this->returnValue( $cardinalPaymentMethod ));

        $commonPaymentMethods->expects( $this->atLeastOnce() )
            ->method( 'getCurrent')
            ->will( $this->returnValue( $commonPaymentMethod ));

        // Create cart order

        $cartOrder = $this->getMockBuilder( BaseCartOrder::class )
            ->setMethods( static::$BASECARTORDER_METHODS )->getMock();

        $cartOrder->expects( $this->once() )
            ->method( 'getCartOrderDetails' )
            ->will( $this->returnValue( $cartOrderDetails ) );

        $cartOrder->expects( $this->once() )
            ->method( 'markInProcessing' )
            ->with(
                // CardinalObjects\BasePaymentMethodResult $paymentMethodResult
                $this->callback(function($arg_paymentMethodResult) use ($paymentMethodResult) {
                    return $arg_paymentMethodResult === $paymentMethodResult;
                })
            );

        $cartOrder->expects( $this->once() )
            ->method( 'clearCart' );

        // Create processor

        $paymentProcessor = new PaymentProcessor(
            $logger,
            $cartSettings,
            // Inject
            $commonPaymentMethods
        );

        // Test Method

        $success = $paymentProcessor->processPayment(
            $cartOrder,
            $consumerObject,
            CommonInterfaces\PaymentAuthTypes::AUTH_ONLY,
            $responseJWTPayload
        );

        $this->assertTrue( $success );
    }
}