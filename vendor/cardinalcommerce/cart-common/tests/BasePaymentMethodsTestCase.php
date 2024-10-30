<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

//require_once( __DIR__ . "/../vendor/autoload.php" );

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\CartSettings;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdContext;

use \CardinalCommerce\Payments\Carts\Common\Payment\PaymentProcessor;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common as PaymentsCommon;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Songbird as PaymentsSongbird;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Cardinal as PaymentsCardinal;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Payment\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdProcessor;

require_once( __DIR__ . "/SongbirdBaseTestCase.php" );

class BasePaymentMethodsTestCase extends SongbirdBaseTestCase {

    // Common payment method

    private static $COMMONPAYMENTMETHODS_METHODS = array(
        'getAvailableKeys',
        'getCurrentKey',
        'getCurrent',
        'getPaymentMethodInfo'
    );

    private static $COMMONPAYMENTMETHOD_METHODS = array(
        'getSongbirdPaymentMethod',
        'getCardinalPaymentMethod'
    );

    // Cardinal payment method

    private static $CARDINALPAYMENTMETHOD_METHODS = array(
        'createOrder',
        'processOrderAuthorization',
        'canCapture',
        'processOrderCapture'
    );

    private static $PAYMENTCONTEXT_METHODS = array(
        'getCartSettings'
    );

    protected static $BASEPAYMENTMETHODRESULT_METHODS = array(
        'wasSuccessful'
    );

    protected static $BASECARTORDER_METHODS = array(
        'getCartOrderDetails',
        'markInProcessing',
        'markComplete',
        'reduceStock',
        'clearCart'
    );

    protected static $PAYMENTMETHODORDER_METHODS = array(
        'getOrderId',
        'getOrderNumber',
        'getOrderDetailsObject'
    );

    /*
    protected static $PAYMENTPROCESSOR_ABSTRACT_METHODS = array(
        'markOrderInProcessing',
        'markOrderComplete',
        'markOrderForShipping',
        'clearCart'
    );
    */

    // Songbird payment method

    private static $SONGBIRDPAYMENTMETHOD_METHODS = array(
        'createServerJWTPayload',
        'createOrderFromResponse'
    );

    private static $SONGBIRDPROCESSOR_ABSTRACT_METHODS = array(
    );

    protected function getPaymentMethodMocks() {
        $mocks = new \stdClass;

        $mocks->commonPaymentMethod = $this->getMockBuilder( PaymentInterfaces\PaymentMethodInterface::class )
            ->setMethods( self::$COMMONPAYMENTMETHOD_METHODS )->getMock();

        $mocks->commonPaymentMethods = $this->getMockBuilder( PaymentsCommon\CommonPaymentMethodsInterface::class )
            ->setMethods( self::$COMMONPAYMENTMETHODS_METHODS )->getMock();

        $mocks->songbirdPaymentMethod = $this->getMockBuilder( PaymentsSongbird\SongbirdPaymentMethodInterface::class )
            ->setMethods( self::$SONGBIRDPAYMENTMETHOD_METHODS )->getMock();

        $mocks->cardinalPaymentMethod = $this->getMockBuilder( PaymentsCardinal\CardinalPaymentMethodInterface::class )
            ->setMethods( self::$CARDINALPAYMENTMETHOD_METHODS )->getMock();

        return $mocks;
    }

    protected function getCartOrderMock() {
        return $this->getMockBuilder( CardinalPaymentObjects\BaseCartOrder::class )
            ->setMethods( self::$BASECARTORDER_METHODS )->getMock();
    }
}