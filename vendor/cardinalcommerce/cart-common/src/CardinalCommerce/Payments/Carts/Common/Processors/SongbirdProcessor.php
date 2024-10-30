<?php
namespace CardinalCommerce\Payments\Carts\Common\Processors;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;
use \CardinalCommerce\Payments\Carts\Common\Songbird\Objects as SongbirdObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\CardinalPaymentMethodsInterface;

// Common

use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdRenderer;
use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdContext;
use \CardinalCommerce\Payments\Carts\Common\Songbird\ServerJWTCreator;

class SongbirdProcessor {
    private $_logger;
    private $_settings;
    private $_songbirdContext;

    // Inject
    private $_commonPaymentMethods;
    private $_paymentDetailsPage;
    private $_renderer;
    private $_scriptRenderer;
    private $_serverJWTCreator;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        SongbirdInterfaces\SongbirdContextInterface $songbirdContext,
        SongbirdInterfaces\SongbirdRendererInterface $renderer,
        SongbirdInterfaces\SongbirdCartScriptRendererInterface $scriptRenderer,
        SongbirdInterfaces\ServerJWTCreatorInterface $serverJWTCreator,
        PageInterfaces\CartPaymentDetailsPageInterface $paymentDetailsPage
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
        $this->_songbirdContext = $songbirdContext;
        $this->_serverJWTCreator = $serverJWTCreator;
        $this->_scriptRenderer = $scriptRenderer;
        $this->_renderer = $renderer;
        $this->_paymentDetailsPage = $paymentDetailsPage;
    }

    protected function getLogger() {
        return $this->_logger;
    }

    protected function getSettings() {
        return $this->_settings;
    }

    public function getSongbirdContext() {
        return $this->_songbirdContext;
    }

    public function getPaymentDetailsPage() {
        return $this->_paymentDetailsPage;
    }

    /**
     *
     * @return SongbirdObjects\ServerJWTPayload
     */
    public function createServerJWTPayload(
        BasePaymentMethod $paymentMethod,
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails
    ) {
        $logger = $this->_logger;
        $songbirdContext = $this->_songbirdContext;

        $method = $this->getCurrentPaymentMethod();

        $serverJWTPayload = $paymentMethod->createServerJWTPayload(
            $cartOrderDetails
        );

        return $serverJWTPayload;
    }

    public function renderScriptBlock(
        BasePaymentMethod $paymentMethod,
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails
    ) {
        $logger = $this->_logger;
        $songbirdContext = $this->_songbirdContext;
        $scriptRenderer = $this->_scriptRenderer;

        return $scriptRenderer->renderSongbirdScriptBlock(
            $songbirdContext,
            $paymentMethod,
            $cartOrderDetails
        );
    }

    /**
     * Render hidden inputs whose values are provided by the server.
     * @return string
     */
    public function renderServerProvidedHiddenInputs(
        BasePaymentMethod $paymentMethod,
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        BaseCartOrder $cartOrder = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $logger = $this->_logger;
        $songbirdContext = $this->_songbirdContext;
        $scriptRenderer = $this->_scriptRenderer;
        $renderer = $this->_renderer;

        $serverJWTCreator = $this->_serverJWTCreator;

        $serverJWTPayload = $paymentMethod->createServerJWTPayload(
            $cartOrderDetails,
            $cartOrder,
            $consumerObject
        );

        $serverJWT = $serverJWTCreator->create(
            $cartOrderDetails,
            $serverJWTPayload
        );

        return $renderer->renderServerProvidedHiddenInputs(
            $songbirdContext,
            $cartOrderDetails,
            $serverJWT
        );
    }

    /**
     * Render hidden inputs whose values will be provided by the client during the transaction.
     * @return string
     */
    public function renderClientProvidedHiddenInputs(
        BasePaymentMethod $paymentMethod,
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        BaseCartOrder $cartOrder = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $logger = $this->_logger;
        $songbirdContext = $this->_songbirdContext;
        $scriptRenderer = $this->_scriptRenderer;
        $renderer = $this->_renderer;

        return $renderer->renderClientProvidedHiddenInputs(
            $songbirdContext
        );
    }

}