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

use \CardinalCommerce\Payments\Carts\Common\Payment\PaymentProcessor;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common as PaymentsCommon;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use CardinalCommerce\Payments\Carts\Common\Payment\Songbird\SongbirdProcessor;

require_once( __DIR__ . "/BasePaymentMethodsTestCase.php" );

class CartProcessorTest extends BasePaymentMethodsTestCase {

    private static $CARTPROCESSOR_METHODS = array(
        'createOrderDetails',
        'renderScriptBlock',
        'createOrderFromResponse',
        'processOrder',
        'processRefund'
    );

    public function testCartProcessor() {
        $logger = $this->getLoggerMock();

        $mocks = $this->getSongbirdMocks();
        $payMocks = $this->getPaymentMethodMocks();

        $commonPaymentMethod = $payMocks->commonPaymentMethod;
        $commonPaymentMethods = $payMocks->commonPaymentMethods;
        $songbirdPaymentMethod = $payMocks->songbirdPaymentMethod;

        // Prepare Values
        $cartSettings = $mocks->cartSettings;

        $cartOrderDetails = $mocks->cartOrderDetails;
        $authType = CommonInterfaces\PaymentAuthTypes::AUTH_ONLY;

        $consumerObject = $this->createConsumerObject();
        $responseJWTPayload = new \stdClass;

        // Set Expectations

        $amount = 999;
        $currencyCode = 840;

        $cartOrderDetails->expects( $this->atLeastOnce() )
            ->method( 'getOrderAmountForMidas' )
            ->will(
                $this->returnValue( $amount )
            );

        $cartOrderDetails->expects( $this->atLeastOnce() )
            ->method( 'getOrderNumericCurrencyForMidas' )
            ->will(
                $this->returnValue( $currencyCode )
            );

        // Setup payment method

        $songbirdPaymentMethod->expects( $this->once() )
            ->method( 'createServerJWTPayload' )
            ->with(
                // PaymentObjects\OrderDetails $orderDetailsObject
                $this->callback(function($arg_orderDetailsObject) use ( $amount, $currencyCode ) {
                    return $arg_orderDetailsObject->Amount === $amount &&
                        $arg_orderDetailsObject->CurrencyCode === $currencyCode;
                }),
                // PaymentObjects\Consumer $consumerObject
                $this->callback(function($arg_consumerObject) use ($consumerObject) {
                    return $arg_consumerObject === $consumerObject;
                })
            );

        $commonPaymentMethod->expects( $this->atLeastOnce() )
            ->method( 'getSongbirdPaymentMethod')
            ->will( $this->returnValue( $songbirdPaymentMethod ));

        $commonPaymentMethods->expects( $this->atLeastOnce() )
            ->method( 'getCurrent')
            ->will( $this->returnValue( $commonPaymentMethod ));

        $identifier = 'Identifier';
        $apiKey = 'ApiKey';

        $apiCreds = $this->createApiCredentialsMock(
            $identifier,
            $apiKey
        );

        $cartSettings->expects( $this->once() )
            ->method('getAPICredentials')
            ->will( $this->returnValue( $apiCreds ));

        // Create processor

        $songbirdProcessor = new SongbirdProcessor(
            $logger,
            $cartSettings,
            $commonPaymentMethods,
            $mocks->cartPaymentDetailsPage,
            $mocks->songbirdCartScriptRenderer
        );

        $songbirdProcessor->initialize();

        // Test Method

        $payload = $songbirdProcessor->createServerJWTPayload(
            $cartOrderDetails,
            $consumerObject
        );

        // Simulate response

        $responseObject = new PaymentObjects\Response((object) array(
            'ActionCode' => 'SUCCESS',
            'Validated' => true,
            'ErrorNumber' => 0,
            'ErrorDescription' => '',
            'Payment' => (object) array(
                'ReasonCode' => 0,
                'ReasonDescription' => '',
                'ProcessorTransactionId' => null,
                'ExtendedData' => null
            ),
            'Consumer' => $consumerObject,
            'Token' => null,
            'Authorization' => null
        ));

        // Test Method

        $order = $songbirdProcessor->createOrderFromResponse(
            $cartOrderDetails,
            $authType,
            $consumerObject,
            $responseObject
        );

        $this->assertTrue( true );
    }
}