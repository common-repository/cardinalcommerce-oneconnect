<?php
namespace CardinalCommerce\Payments\Carts\Common\Processors;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\CardinalPaymentMethodsInterface;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart\CartSettings;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart\CartOrderDetails;

use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;

// Common

use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdRenderer;
use \CardinalCommerce\Payments\Carts\Common\Songbird\SongbirdContext;
use \CardinalCommerce\Payments\Carts\Common\Songbird\ServerJWTCreator;

class PaymentProcessor {
    private $_logger;
    private $_settings;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
    }

    public function processPayment(
        BasePaymentMethod $paymentMethod,
        BaseCartOrder $cartOrder,
        $authType,
        PaymentObjects\Consumer $consumerObject = null,
        PaymentObjects\Response $responseObject = null,
        $responseJWTPayload = null
    ) {
        $logger = $this->_logger;
        $cartSettings = $this->_settings;

        $cartOrderDetails = $cartOrder->getCartOrderDetails();
        $this->_logger->info('[PaymentProcessor::processPayment] cartOrderDetails: ' . json_encode($cartOrderDetails));
        $this->_logger->info('[PaymentProcessor::processPayment] consumerObject: ' . json_encode($consumerObject));
        $this->_logger->info('[PaymentProcessor::processPayment] responseObject: ' . json_encode($responseObject));

        // Create a PaymentMethodOrder from the ResponseJWT payload

        $order = $paymentMethod->createPaymentMethodOrderFromResponse(
            $cartOrder,
            $authType,
            $responseObject,
            $consumerObject
        );

        $this->_logger->info('[PaymentProcessor::processPayment] order: ' . json_encode($order));
        $this->_logger->info('[PaymentProcessor::processPayment] authType: ' . $authType);

        $result = $paymentMethod->processOrderAuthorization(
            $order,
            $cartOrder,
            $consumerObject
        );
        $this->_logger->info('[PaymentProcessor::processPayment] PaymentMethod::processOrderAuthorization result: ' . json_encode($result));

        $success = $result->wasSuccessful();

        if( $success ) {

            $this->_logger->info('[PaymentProcessor::processPayment] authorization success. authType was: ' . $authType);
            $needsCapture = $authType === CommonInterfaces\PaymentAuthTypes::AUTH_ONLY;

            // Store common order metadata
            $cartOrder->storeCardinalProcessorTransactionId( $result->getProcessorTransactionId() );
            $cartOrder->storeCardinalProcessorOrderId( $result->getProcessorOrderId() );

            // Store payment method specific order metadata
            $cartOrder->storePaymentMethodResultData( $result );

            if ($needsCapture) {
                $this->_logger->info('[PaymentProcessor::processPayment] setting order status to processing.');
                
                $cartOrder->markInProcessing( $result );
            } else {
                $this->_logger->info('[PaymentProcessor::processPayment] marking payment complete.');

                $cartOrder->markComplete( $result );
            }

            // Reduce stock
            $cartOrder->reduceStock();

            // Empty cart
            $cartOrder->clearCart();

            // Success
            return true;
        }

        // Failure
        return false;
    }

    public function processCapture(
        BasePaymentMethod $paymentMethod,
        BaseCartOrder $cartOrder
    ) {
        $logger = $this->_logger;
        $cartSettings = $this->_settings;

        $order = $paymentMethod->createPaymentMethodOrderForExistingOrder(
            $cartOrder
        );

        $result = $paymentMethod->processOrderCapture(
            $order,
            $cartOrder
        );
        $this->_logger->info('[PaymentProcessor::processCapture] PaymentMethod::processOrderCapture result: ' . json_encode($result));

        $success = $result->wasSuccessful();

        if( $success ) {
            $this->_logger->info('[PaymentProcessor::processCapture] capture success.');
            $this->_logger->info('[PaymentProcessor::processCapture] marking payment complete.');

            $cartOrder->markComplete( $result );
        } else {
            // TODO: Make failed
        }

        return $success;
    }

}