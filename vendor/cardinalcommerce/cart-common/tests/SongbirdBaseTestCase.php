<?php


ini_set('display_errors', 'on');
error_reporting(E_ALL);

//require_once( __DIR__ . "/../vendor/autoload.php" );

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodResult;

use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdContext;

use \CardinalCommerce\Payments\Interfaces\APICredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelCredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelClientInterface;

class SongbirdBaseTestCase extends PHPUnit_Framework_TestCase {

    protected static $LOGGER_METHODS = array(
        'debug',
        'info',
        'notice',
        'warning',
        'alert',
        'error',
        'critical',
        'emergency',
        'log'
    );

    protected static $CARTPAYMENTDETAILSPAGE_METHODS = array(
        'getFormSelector',
        'getSubmitButtonSelector',
        'getCardNumberSelector',
        'getCardExpSelector',
        'getCardExpDelimiter',
        'getCardExpMonthSelector',
        'getCardExpYearSelector',
        'getCardCVVSelector',
        'getFormattedTotalAmountSelector',
        'getServerJWTHiddenInputName',
        'getResponseJWTHiddenInputName'
    );

    protected static $SETTINGS_METHODS = array(
        'getEnvironment',
        'getAPICredentials',
        'getCentinelCredentials',
        'getSongbirdLoggingEnabled'
    );

    protected static $CARTORDERDETAILS_METHODS = array(
        'getOrderKey',
        'getOrderDescription',
        'getOrderCurrencyForMidas',
        'getOrderNumericCurrencyForMidas',
        'getOrderAmountForMidas'
    );

    protected static $SONGBIRDRENDERER_METHODS = array(
        'renderServerProvidedHiddenInputs',
        'renderClientProvidedHiddenInputs'
    );

    protected static $SONGBIRDCARTSCRIPTBLOCKRENDERER_METHODS = array(
        'renderSongbirdScriptBlock'
    );

    protected static $APICREDS_METHODS = array(
        'getApiIdentifier',
        'getOrgUnitId',
        'getApiKey'
    );

    protected static $SONGBIRDCONTEXT_METHODS = array(
        'getSettings',
        'getCartPaymentDetailsPage',
        'getConfigureOptions',
        'getSetupInitOptions',
        'getServerJWTHiddenInputName',
        'getResponseJWTHiddenInputName'
    );

    protected static $SONGBIRDPROCESSOR_METHODS = array(
        'getSongbirdContext',
        'renderScriptBlock',
        'renderServerProvidedHiddenInputs',
        'renderClientProvidedHiddenInputs'
    );

    protected static $PAYMENTPROCESSOR_METHODS = array(
        'processPayment'
    );

    protected static $BASEPAYMENTMETHOD_ABSTRACT_METHODS = array(
        'createServerJWTPayload',
        'createPaymentMethodOrder',
        'createPaymentMethodOrderFromResponse',
        'processOrderAuthorization',
        'processOrderCapture',
        'processRefund'
    );

    protected static $BASECARTORDER_ABSTRACT_METHODS = array(
        'getCartOrderDetails',
        'getOrderNumber',

        'storePaymentMethodResultData',
        'storeCardinalProcessorTransactionId',
        'storeCardinalProcessorOrderId',

        'getCardinalPaymentMethodKey',
        'getCardinalProcessorTransactionId',
        'getCardinalProcessorOrderId',

        'markInProcessing',
        'markComplete',
        'reduceStock',
        'clearCart'
    );

    protected static $BASECARTPROCESSOR_ABSTRACT_METHODS = array(
        'getPaymentMethod'
    );

    protected function getLoggerMock() {
        $logger = $this->getMockBuilder( LoggerInterface::class )
            ->setMethods(static::$LOGGER_METHODS)->getMock();

        return $logger;
    }

    protected function getSongbirdMocks() {
        $mocks = new \stdClass;

        $mocks->cartSettings = $this->getMockBuilder( CartInterfaces\CartSettingsInterface::class )
            ->setMethods(static::$SETTINGS_METHODS)->getMock();

        $mocks->renderer = $this->getMockBuilder( SongbirdInterfaces\SongbirdRendererInterface::class )
            ->setMethods(static::$SONGBIRDRENDERER_METHODS)->getMock();

        $mocks->scriptRenderer = $this->getMockBuilder( SongbirdInterfaces\SongbirdCartScriptRendererInterface::class )
            ->setMethods(static::$SONGBIRDCARTSCRIPTBLOCKRENDERER_METHODS)->getMock();

        $mocks->cartOrderDetails = $this->getMockBuilder( CartInterfaces\CartOrderDetailsInterface::class )
            ->setMethods(static::$CARTORDERDETAILS_METHODS)->getMock();

        $mocks->serverJWTCreator = $this->getMockBuilder( SongbirdInterfaces\ServerJWTCreatorInterface::class )
            ->setMethods( ['create'] )->getMock();

        $mocks->cartPaymentDetailsPage = $this->getMockBuilder( PageInterfaces\CartPaymentDetailsPageInterface::class )
            ->setMethods(static::$CARTPAYMENTDETAILSPAGE_METHODS)->getMock();

        return $mocks;
    }

    protected function createSongbirdContextMock(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        PageInterfaces\CartPaymentDetailsPageInterface $paymentDetailsPage
    ) {
        $songbirdContext = $this->getMockBuilder( SongbirdInterfaces\SongbirdContextInterface::class )
            ->setMethods( static::$SONGBIRDCONTEXT_METHODS )
            ->setConstructorArgs(array(
                $logger,
                $cartSettings,
                $paymentDetailsPage
            ))
            ->getMock();
        
        return $songbirdContext;
    }

    protected function createSongbirdProcessorMock(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        SongbirdInterfaces\SongbirdContextInterface $songbirdContext,
        SongbirdInterfaces\SongbirdRendererInterface $renderer,
        SongbirdInterfaces\SongbirdCartScriptRendererInterface $scriptRenderer,
        SongbirdInterfaces\ServerJWTCreatorInterface $serverJWTCreator,
        PageInterfaces\CartPaymentDetailsPageInterface $paymentDetailsPage        
    ) {
        $songbirdProcessor = $this->getMockBuilder( SongbirdProcessor::class )
            ->setMethods( static::$SONGBIRDPROCESSOR_METHODS )
            ->setConstructorArgs(array(
                $logger,
                $cartSettings,
                $songbirdContext,
                $renderer,
                $scriptRenderer,
                $serverJWTCreator,
                $paymentDetailsPage
            ))
            ->getMock();

        return $songbirdProcessor;
    }

    protected function createCartOrderMock() {
        $cartOrder = $this->getMockBuilder( BaseCartOrder::class )
            ->setMethods( static::$BASECARTORDER_ABSTRACT_METHODS )
            ->getMock();

        return $cartOrder;
    }

    protected function createCartProcessorMock(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        PaymentsCommon\CommonPaymentMethodsInterface $paymentMethods,
        PageInterfaces\CartPaymentDetailsPageInterface $paymentDetailsPage,
        SongbirdInterfaces\SongbirdCartScriptRendererInterface $scriptRenderer
    ) {
        $cartProcessor = $this->getMockBuilder( BaseCartProcessor::class )
            ->setMethods( static::$BASECARTPROCESSOR_ABSTRACT_METHODS )
            ->getMock();

        return $cartProcessor;
    }

    protected function createPaymentMethodMock(
    ) {

    }

    protected function createSongbirdContext(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        SongbirdInterfaces\SongbirdRendererInterface $renderer,
        SongbirdInterfaces\SongbirdCartScriptRendererInterface $scriptBlockRenderer,
        SongbirdInterfaces\ServerJWTCreatorInterface $serverJWTCreator,
        PageInterfaces\CartPaymentDetailsPageInterface $paymentDetailsPage
    ) {
        $songbirdContext = new SongbirdContext(
            $logger,
            $cartSettings,
            $renderer,
            $scriptBlockRenderer,

            $serverJWTCreator,
            $paymentDetailsPage
         );

         return $songbirdContext;
    }

    protected function createConsumerObject(
        PaymentObjects\Account $account = null
    ) {
        $consumerObject = new PaymentObjects\Consumer((object) array (
            'Email1' => 'email1@test.tld',
            'Email2' => 'email2@test.tld',
            'ShippingAddress' => (object) array(
                'FirstName' => 'Test',
                'MiddleName' => '',
                'LastName' => 'User',
                'Address1' => '8100 Tyler Blvd.',
                'Address2' => '',
                'Address3' => '',
                'City' => 'Mentor',
                'State' => 'OH',
                'PostalCode' => '44077',
                'CountryCode' => 'US',
                'Phone1' => '+13334445555',
                'Phone2' => ''
            ),
            'BillingAddress' => (object) array(
                'FirstName' => 'Test',
                'MiddleName' => '',
                'LastName' => 'User',
                'Address1' => '8100 Tyler Blvd.',
                'Address2' => '',
                'Address3' => '',
                'City' => 'Mentor',
                'State' => 'OH',
                'PostalCode' => '44077',
                'CountryCode' => 'US',
                'Phone1' => '+13334445555',
                'Phone2' => ''
            ),
            'Account' => $account
        ));

        return $consumerObject;
    }

    protected function createApiCredentialsMock(
        $identifier,
        $apiKey,
        $orgUnitId = null
    ) {
        $apiCredentials = $this->getMockBuilder( APICredentialsInterface::class )
            ->setMethods( static::$APICREDS_METHODS )->getMock();

        $apiCredentials->expects( $this->once() )
            ->method('getApiIdentifier')
            ->will( $this->returnValue( $identifier ));

        $apiCredentials->expects( $this->once() )
            ->method('getApiKey')
            ->will( $this->returnValue( $apiKey ));

        if ( $orgUnitId != null ) {
            $apiCredentials->expects( $this->once() )
                ->method('getOrgUnitId')
                ->will( $this->returnValue( $orgUnitId ));
        }

        return $apiCredentials;
    }
    
}