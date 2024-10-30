<?php
namespace CardinalCommerce\Payments\Carts\Common\Processors;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Carts\Common\CommonObjects;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BaseOrderPaymentMethodResolver;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class CartProcessor {

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var CommonObjects
     */
    private $_commonObjects;

    public function __construct(
        LoggerInterface $logger,
        CommonObjects $commonObjects
    ) {
        $this->_logger = $logger;
        $this->_commonObjects = $commonObjects;
    }

    protected function resolvePaymentMethod(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        BaseCartOrder $cartOrder = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $paymentMethodResolver = $this->_commonObjects->getPaymentMethodResolver();

        return $paymentMethodResolver->resolvePaymentMethodForOrder(
            $cartOrderDetails,
            $cartOrder,
            $consumerObject
        );
    }

    protected function resolvePaymentMethodForExistingOrder(
        BaseCartOrder $cartOrder
    ) {
        $logger = $this->_logger;
        $paymentMethods = $this->_commonObjects->getPaymentMethods();

        $key = $cartOrder->getCardinalPaymentMethodKey();
        $logger->info('[CartProcessor::resolvePaymentMethodForExistingOrder] key: ' . json_encode($key));

        $paymentMethod = $paymentMethods->getPaymentMethodInstance($key);
        $logger->info('[CartProcessor::resolvePaymentMethodForExistingOrder] paymentMethod: ' . json_encode( $paymentMethod ));

        return $paymentMethod;
    }

    public function createServerJWTPayload(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $songbirdProcessor = $this->_commonObjects->getSongbirdProcessor();

        $paymentMethod = $this->resolvePaymentMethod(
            $cartOrderDetails
        );

        return $songbirdProcessor->createServerJWTPayload(
            $paymentMethod,
            $cartOrderDetails
        );
    }

    public function renderScriptBlock(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails
    ) {
        $songbirdProcessor = $this->_commonObjects->getSongbirdProcessor();

        $paymentMethod = $this->resolvePaymentMethod(
            $cartOrderDetails
        );

        return $songbirdProcessor->renderScriptBlock(
            $paymentMethod,
            $cartOrderDetails
        );
    }

    /**
     * Render hidden inputs whose values are provided by the server.
     * @return string
     */
    public function renderServerProvidedHiddenInputs(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails
    ) {
        $songbirdProcessor = $this->_commonObjects->getSongbirdProcessor();

        $paymentMethod = $this->resolvePaymentMethod(
            $cartOrderDetails
        );

        return $songbirdProcessor->renderServerProvidedHiddenInputs(
            $paymentMethod,
            $cartOrderDetails
        );
    }

    /**
     * Render hidden inputs whose values will be provided by the client during the transaction.
     * @return string
     */
    public function renderClientProvidedHiddenInputs(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails
    ) {
        $songbirdProcessor = $this->_commonObjects->getSongbirdProcessor();

        $paymentMethod = $this->resolvePaymentMethod(
            $cartOrderDetails
        );

        return $songbirdProcessor->renderClientProvidedHiddenInputs(
            $paymentMethod,
            $cartOrderDetails
        );
    }

    /**
     * Create an order
     * @param CardinalPaymentContextInterface $paymentContext
     * @param CartInterfaces\CartOrderDetailsInterface $cartOrderDetails The CartOrderDetailsInterface instance
     * @param PaymentObjects\Consumer $consumerObject The Cardinal Consumer object
     *
     * @return BasePaymentMethodOrder $order
     */
    public function createPaymentMethodOrder(
        BaseCartOrder $cartOrder,
        $authType,
        PaymentObjects\Consumer $consumerObject
    ) {
        $paymentMethod = $this->resolvePaymentMethod(
            $cartOrder->getCartOrderDetails(),
            $cartOrder,
            $consumerObject
        );

        return $paymentMethod->createPaymentMethodOrder(
            $cartOrder,
            $authType,
            $consumerObject
        );
    }

    /**
     * Create an order from a CardinalCruise Response JWT
     *
     * @param CardinalPaymentContextInterface $paymentContext
     * @param CartInterfaces\CartOrderDetailsInterface $cartOrderDetails The CartOrderDetailsInterface instance
     * @param PaymentObjects\Response $responseObject The Cardinal Response object (optional)
     * @param PaymentObjects\Consumer $consumerObject The Cardinal Consumer object (optional)
     *
     * @return BasePaymentMethodOrder $order
     */
    public function createPaymentMethodOrderFromResponse(
        BaseCartOrder $cartOrder,
        $authType,
        PaymentObjects\Response $responseObject = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $cartOrderDetails = $cartOrder->getCartOrderDetails();

        $paymentMethod = $this->resolvePaymentMethod(
            $cartOrderDetails,
            $cartOrder,
            $consumerObject
        );

        $order = $paymentMethod->createPaymentMethodOrderFromResponse(
            $cartOrder,
            $authType,
            $responseObject,
            $consumerObject
        );

        return $order;
    }

    public function processOrder(
        BaseCartOrder $cartOrder,
        $authType,
        PaymentObjects\Consumer $consumerObject = null,
        PaymentObjects\Response $responseObject = null,
        $responseJWTPayload = null
    ) {
        $paymentProcessor = $this->_commonObjects->getPaymentProcessor();

        $cartOrderDetails = $cartOrder->getCartOrderDetails();

        $paymentMethod = $this->resolvePaymentMethod(
            $cartOrderDetails,
            $cartOrder,
            $consumerObject
        );

        $success = $paymentProcessor->processPayment(
            $paymentMethod,
            $cartOrder,
            $authType,
            $consumerObject,
            $responseObject,
            $responseJWTPayload
        );

        return $success;
    }

    public function processCapture(
        BaseCartOrder $cartOrder
    ) {
        $paymentProcessor = $this->_commonObjects->getPaymentProcessor();

        $paymentMethod = $this->resolvePaymentMethodForExistingOrder(
            $cartOrder
        );

        $success = $paymentProcessor->processCapture(
            $paymentMethod,
            $cartOrder
        );

        return $success;
    }

    public function processRefund() {
    }
}