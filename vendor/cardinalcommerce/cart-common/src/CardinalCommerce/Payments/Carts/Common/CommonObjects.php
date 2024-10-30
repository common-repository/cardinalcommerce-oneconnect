<?php
namespace CardinalCommerce\Payments\Carts\Common;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Client\ProcessorModule\ProcessorModuleClient;
use \CardinalCommerce\Client\Centinel\CentinelClient;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdContext;
use \CardinalCommerce\Payments\Carts\Common\Songbird\ServerJWTCreator;
use \CardinalCommerce\Payments\Carts\Common\Songbird\ResponseJWTParser;
use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdRenderer;
use \CardinalCommerce\Payments\Carts\Common\Processors\SongbirdProcessor;
use \CardinalCommerce\Payments\Carts\Common\Processors\PaymentProcessor;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class CommonObjects {
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var CartInterfaces\CartIntegrationInterface
     */
    private $_cartIntegration;

    /**
     * @var CartInterfaces\CartSettingsInterface
     */
    private $_settings;

    private static $_instance = null;

    public static function initialize(
        LoggerInterface $logger,
        CartInterfaces\CartIntegrationInterface $cartIntegration
    ) {
        if ( self::$_instance != null ) {
            throw new \Exception( sprintf("Shared [%s] instance already initialized.", __CLASS__) );
        }

        self::$_instance = new self( $logger, $cartIntegration );
    }

    public static function instance() {
        if ( self::$_instance == null ) {
            throw new \Exception( sprintf("Shared [%s] instance not initialized.", __CLASS__) );
        }

        return self::$_instance;
    }

    private function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartIntegrationInterface $cartIntegration
    ) {
        $settings = $cartIntegration->getSettings();

        $this->_logger = $logger;
        $this->_cartIntegration = $cartIntegration;
        $this->_settings = $settings;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger() {
        return $this->_logger;
    }

    /**
     * @return CartInterfaces\CartIntegrationInterface
     */
    public function getCartIntegration() {
        return $this->_cartIntegration;
    }

    /**
     * @return CartInterfaces\CartSettingsInterface
     */
    public function getCartSettings() {
        return $this->_settings;
    }

    /**
     * @return CommonPaymentMethodsInterface
     */
    public function getPaymentMethods() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new CommonPaymentMethods( $this->_logger );
        }

        return $_instance;
    }

    /**
     * @return CommonPaymentMethodsInterface
     */
    public function getServerJWTCreator() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new ServerJWTCreator(
                $this->_logger,
                $this->_settings
            );
        }

        return $_instance;
    }

    /**
     * @return SongbirdInterfaces\ResponseJWTParserInterface
     */
    public function getResponseJWTParser() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new ResponseJWTParser(
                $this->_logger,
                $this->_settings
            );
        }

        return $_instance;
    }

    /**
     * @return SongbirdInterfaces\SongbirdRendererInterface
     */
    public function getRenderer() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new SongbirdRenderer(
                $this->_logger,
                $this->_settings
            );
        }

        return $_instance;
    }

    /**
     * @return SongbirdInterfaces\SongbirdContextInterface
     */
    public function getSongbirdContext() {
        static $_instance = null;

        if ( $_instance == null ) {
            $paymentDetailsPage = $this->getPaymentDetailsPage();

            $_instance = new SongbirdContext(
                $this->_logger,
                $this->_settings,
                $paymentDetailsPage
            );
        }

        return $_instance;
    }

    /**
     * @return Processors\SongbirdProcessor
     */
    public function getSongbirdProcessor() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new Processors\SongbirdProcessor(
                $this->_logger,
                $this->_settings,
                $this->getSongbirdContext(),
                $this->getRenderer(),
                $this->getScriptRenderer(),
                $this->getServerJWTCreator(),
                $this->getPaymentDetailsPage()
            );
        }

        return $_instance;
    }

    /**
     * @return Processors\PaymentProcessor
     */
    public function getPaymentProcessor() {
        static $_instance = null;

        if ( $_instance == null ) {
            $paymentDetailsPage = $this->getPaymentDetailsPage();

            $_instance = new Processors\PaymentProcessor(
                $this->_logger,
                $this->_settings
            );
        }

        return $_instance;
    }

    /**
     * @return Processors\CartProcessor
     */
    public function getCartProcessor() {
        static $_instance = null;

        if ( $_instance == null ) {
            $_instance = new Processors\CartProcessor( $this->_logger, $this );
        }

        return $_instance;
    }

    /**
     * @return CentinelClientInterface
     */
    public function getCentinelClient() {
        static $_instance = null;

        if ( $_instance == null ) {
            $apiCreds = $this->_settings->getAPICredentials();
            $centinelCreds = $this->_settings->getCentinelCredentials();

            $transactionUrl = $centinelCreds->getTransactionUrl();
            $transactionPwd = $centinelCreds->getTransactionPwd();
            $timeout = $centinelCreds->getTimeout();

            $_instance = new CentinelClient(
                $this->_logger,
                $transactionUrl,
                $transactionPwd,
                $timeout
            );
        }

        return $_instance;
    }

    /**
     * @return ProcessorModuleClient
     */
    public function getProcessorModuleClient() {
        static $_instance = null;

        if ( $_instance == null ) {
            $apiCreds = $this->_settings->getAPICredentials();
            $centinelCreds = $this->_settings->getCentinelCredentials();

            $_instance = ProcessorModuleClient::builder( $this->_logger )
                ->setCentinelClient( $this->getCentinelClient() )
                ->setApiCredentials( $apiCreds )
                ->setCentinelCredentials( $centinelCreds )
                ->build();
        }

        return $_instance;
    }

    /**
     * @return BaseOrderPaymentMethodResolver
     */
    public function getPaymentMethodResolver() {
        static $_instance = null;

        if ( $_instance == null ) {
            $paymentMethods = $this->getPaymentMethods();

            $_instance = $this->_cartIntegration->getCartObjectsFactory()->createCartOrderPaymentMethodResolver(
                $paymentMethods
            );
        }

        return $_instance;

        throw new \Exception( 'Not implemented' );
    }
    
    /**
     * @return PageInterfaces\CartPaymentDetailsPageInterface
     */
    public function getPaymentDetailsPage() {
        static $_instance = null;

        if ( $_instance == null ) {
            $paymentMethods = $this->getPaymentMethods();

            $_instance = $this->_cartIntegration->getCartObjectsFactory()->createPaymentDetailsPage();
        }

        return $_instance;
    }

    /**
     * @return SongbirdInterfaces\SongbirdCartScriptRendererInterface
     */
    public function getScriptRenderer() {
        static $_instance = null;

        if ( $_instance == null ) {
            $paymentMethods = $this->getPaymentMethods();

            $_instance = $this->_cartIntegration->getCartObjectsFactory()->createScriptRenderer();
        }

        return $_instance;
    }

}