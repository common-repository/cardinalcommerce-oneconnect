<?php
namespace CardinalCommerce\Payments\Carts\Common\Payment\Implementations\ProcessorModuleV2;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;
use \CardinalCommerce\Payments\Carts\Common\CommonObjects;

use \CardinalCommerce\Payments\Interfaces\CentinelClientInterface;

use \CardinalCommerce\Payments\Carts\Common\Songbird\Objects as SongbirdObjects;

class PaymentMethod extends BasePaymentMethod {
    const PAYMENT_METHOD_TITLE = "Credit Card";
    const PAYMENT_METHOD_DESCRIPTION = "Credit Card payment with optional Cardinal Consumer Authentication (CCA)";

    // NEXTREV: Determine if we can support an unique order_number assigned by the cart
    // available on the payment details page.
    protected static function createOrderNumberSuffix() {
        // NOTE: sha1 is used only to ensure the result is a hex string
        // we do not rely on collision behavior
        return substr(sha1(uniqid(mt_rand(), true)), 0, 6);
    }

    // NEXTREV: Support TransactionId?
    private function createOrderDetailsObject(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        $orderNumber = null
    ) {
        $amount = $cartOrderDetails->getOrderAmountForMidas();
        $currencyCode = $cartOrderDetails->getOrderNumericCurrencyForMidas();
        $orderDescription = $cartOrderDetails->getOrderDescription();

        $data = (object) array(
            'Amount' => $amount,
            'CurrencyCode' => $currencyCode
        );

        if ( ! empty( $orderDescription ) ) {
            $data->OrderDescription = $orderDescription;
        }

        if ( $orderNumber != null ) {
            $data->OrderNumber = $orderNumber;
        }

        $orderDetailsObject = new PaymentObjects\OrderDetails($data);

        return $orderDetailsObject;
    }

    /**
     * Create ServerJWTPayload for Songbird
     *
     * @return SongbirdObjects\ServerJWTPayload
     */
    public function createServerJWTPayload(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        PaymentObjects\OrderDetails $orderDetailsObject = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $logger = CommonObjects::instance()->getLogger();

        $logger->info('[PaymentMethod::createServerJWTPayload] cartOrderDetails: {cartOrderDetails}',
            array(
                'cartOrderDetails' => var_export($cartOrderDetails, true)
            ));

        $orderNumberSuffix = static::createOrderNumberSuffix();
        $orderNumber = "cardinalpm-${orderNumberSuffix}";

        if ( $orderDetailsObject == null ) {
            $orderDetailsObject = $this->createOrderDetailsObject(
                $cartOrderDetails,
                $orderNumber
            );
        }

        $payload = new SongbirdObjects\ServerJWTPayload((object) array(
            'OrderDetails' => $orderDetailsObject->toJSONObject()
        ));

        return $payload;
    }

    /**
     * Create an order using a Consumer (Account) object.
     *
     * Note: this method expects the Account object to be populated and is
     * normally implemented with a 'cmpi_lookup' message or equivalent.
     *
     * @param CartInterfaces\CartOrderDetailsInterface $cartOrderDetails The CartOrderDetailsInterface instance
     * @param PaymentObjects\Consumer $consumerObject The Cardinal Consumer object
     *
     * @return BasePaymentMethodOrder $order
     */
    public function createPaymentMethodOrder(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        $authType,
        PaymentObjects\Consumer $consumerObject
    ) {
        // NEXTREV: Implement this or provide mechanism for opting-out of these methods
        throw new Exception("Unsupported for this payment method");
    }

    /**
     * Create an order from Consumer and Response (ResponseJWT payload) object
     *
     * @param BaseCartOrder $cartOrder
     * @param PaymentObjects\Consumer $consumerObject The Cardinal Consumer object
     * @param PaymentObjects\Response $responseObject The Cardinal Response object (ResponseJWT payload)
     *
     * @return BasePaymentMethodOrder $order
     */
    public function createPaymentMethodOrderFromResponse(
        BaseCartOrder $cartOrder,
        $authType,
        PaymentObjects\Response $responseObject = null,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $logger = CommonObjects::instance()->getLogger();

        $orderNumber = $cartOrder->getOrderNumber();

        $cartOrderDetails = $cartOrder->getCartOrderDetails();
        $orderDetailsObject = $this->createOrderDetailsObject(
            $cartOrderDetails
        );

        $orderId = null;
        $orderNumber = null;

        $logger->info('[PaymentMethod::createPaymentMethodOrderFromResponse] responseObject: {responseObject}',
            array(
                'responseObject' => var_export($responseObject, true)
            ));

        // NOTE: This PaymentMethod should only be used with ProcessorOrderId.

        // NEXTREV: Resolve which PaymentMethod implementation to use based on
        // objects defined in the response payload. This can be done in the
        // BaseCartOrderPaymentMethodResolver implementation.
        if ( $responseObject->AuthorizationProcessor == null ) {
            throw new \Exception('AuthorizationProcessor Object Missing in Response Object (ResponseJWT payload)');
        }

        // TODO: Fix this, it appears to be missing on the ResponseJWT payload.
        if ( $responseObject->Payment != null && property_exists($responseObject->Payment, "OrderNumber" ) ) {
            $orderNumber = $responseObject->Payment->OrderNumber;
        }

        $logger->info('[PaymentMethod::createPaymentMethodOrderFromResponse] orderNumber: {orderNumber}',
            array(
                'orderNumber' => var_export($orderNumber, true)
            ));

        // AuthorizationProcessor
        $authorizationProcessorObject = $responseObject->AuthorizationProcessor;

        $processorOrderId = $authorizationProcessorObject->ProcessorOrderId;
        $processorTransactionId = $authorizationProcessorObject->ProcessorTransactionId;

        $paymentMethodOrder = PaymentMethodOrder::forPayment(
            $cartOrder,
            $orderDetailsObject,
            $responseObject,
            $consumerObject
        );

        return $paymentMethodOrder;
    }

    /**
     * Create an order for an existing (authorized) order
     *
     * @param BaseCartOrder $cartOrder
     *
     * @return BasePaymentMethodOrder $order
     */
    public function createPaymentMethodOrderForExistingOrder(
        BaseCartOrder $cartOrder
    ) {
        $processorTransactionId = $cartOrder->getCardinalProcessorTransactionId();
        $processorOrderId = $cartOrder->getCardinalProcessorOrderId();

        $paymentMethodOrder = PaymentMethodOrder::forExistingOrder( $cartOrder );

        return $paymentMethodOrder;
    }

    /**
     * Process authorization for the given order
     *
     *  NEXTREV: Make $consumerObject nullable or allow empty Account field
     *  for cases using OrderId in authorize method.
     *
     * @param CardinalPaymentContextInterface $paymentContext
     * @param CartInterfaces\CartOrderDetailsInterface $cartOrderDetails The CartOrderDetailsInterface instance
     * @param PaymentObjects\Consumer $consumerObject The Cardinal Consumer object
     * @param string $authType { authCapture | authOnly }
     * @param object $responseJWTPayload
     *
     * @return bool Was the payment processed successfully?
     */
    public function processOrderAuthorization(
        BasePaymentMethodOrder $order,
        BaseCartOrder $cartOrder,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $logger = CommonObjects::instance()->getLogger();
        $processorModuleClient = CommonObjects::instance()->getProcessorModuleClient();

        $orderNumber = $order->getOrderNumber();

        $cartOrderDetails = $cartOrder->getCartOrderDetails();

        $orderDetailsObject = $this->createOrderDetailsObject(
            $cartOrderDetails,
            $orderNumber
        );

        $logger->info('[PaymentMethod::processOrderAuthorization] orderNumber: {orderNumber}',
            array(
                'orderNumber' => $orderNumber
            ));

        $authorizationProcessorObject = $order->getAuthorizationProcessorObject();

        $paymentExtensions = $order->getPaymentExtensions();

        // Leave out the Consumer object from this request

        $response = $processorModuleClient->authorizeWithAuthorizationProcessorObject(
            $orderDetailsObject,
            $authorizationProcessorObject,
            $paymentExtensions
        );

        $result = new PaymentMethodResult(
            $response,
            $orderNumber
        );

        return $result;
    }

    /**
     * Process capture for the given (authorized) order
     *
     * @param BasePaymentMethodOrder $order
     * @param BaseCartOrder
     *
     * @return bool Was the payment processed successfully?
     */
    public function processOrderCapture(
        BasePaymentMethodOrder $order,
        BaseCartOrder $cartOrder
    ) {
        $logger = CommonObjects::instance()->getLogger();
        $processorModuleClient = CommonObjects::instance()->getProcessorModuleClient();

        $orderNumber = $order->getOrderNumber();

        $processorTransactionId = $cartOrder->getCardinalProcessorTransactionId();
        $processorOrderId = $cartOrder->getCardinalProcessorOrderId();

        $logger->info('[PaymentMethod::processOrderCapture] processorTransactionId: ' . $processorTransactionId);
        $logger->info('[PaymentMethod::processOrderCapture] processorOrderId: ' . $processorOrderId);

        $cartOrderDetails = $cartOrder->getCartOrderDetails();

        $orderDetailsObject = $this->createOrderDetailsObject(
            $cartOrderDetails,
            $orderNumber
        );

        $logger->info('[PaymentMethod::processOrderCapture] orderDetailsObject: ' . json_encode($orderDetailsObject));

        $response = $processorModuleClient->captureWithProcessorOrderId(
            $processorOrderId,
            $orderDetailsObject
        );

        $logger->info('[PaymentMethod::processOrderCapture] response: ' . json_encode($response));

        $result = new PaymentMethodResult(
            $response,
            $orderNumber
        );

        return $result;
    }

    /**
     * Void for the given order
     *
     * @param BasePaymentMethodOrder $order
     * @param BaseCartOrder
     *
     * @return bool Was the payment processed successfully?
     */
    public function processVoid(
        BasePaymentMethodOrder $order,
        BaseCartOrder $cartOrder
    ) {
        $logger = CommonObjects::instance()->getLogger();
        $processorModuleClient = CommonObjects::instance()->getProcessorModuleClient();

        $orderNumber = $order->getOrderNumber();

        $cartOrderDetails = $cartOrder->getCartOrderDetails();

        $orderDetailsObject = $this->createOrderDetailsObject(
            $cartOrderDetails,
            $orderNumber
        );

        $logger->info('[PaymentMethod::processVoid] orderDetailsObject: ' . json_encode($orderDetailsObject));

        $processorTransactionId = $cartOrder->getCardinalProcessorTransactionId();
        $processorOrderId = $cartOrder->getCardinalProcessorOrderId();

        $logger->info('[PaymentMethod::processVoid] processorTransactionId: ' . $processorTransactionId);
        $logger->info('[PaymentMethod::processVoid] processorOrderId: ' . $processorOrderId);

        $response = $processorModuleClient->voidWithProcessorOrderId(
            $processorOrderId,
            $orderDetailsObject
        );

        $logger->info('[PaymentMethod::processVoid] response: ' . json_encode($response));

        $result = new PaymentMethodResult(
            $response,
            $orderNumber
        );

        return $result;
    }

    /**
     * Process refund for the given order
     *
     * @param BasePaymentMethodOrder $order
     * @param BaseCartOrder
     * @param int $rawAmount
     * @param string $reason
     *
     * @return bool Was the payment processed successfully?
     */
    public function processRefund(
        BasePaymentMethodOrder $order,
        BaseCartOrder $cartOrder,
        $rawAmount,
        $reason = ''
    ) {
        $logger = CommonObjects::instance()->getLogger();
        $processorModuleClient = CommonObjects::instance()->getProcessorModuleClient();

        $orderNumber = $order->getOrderNumber();

        $cartOrderDetails = $cartOrder->getCartOrderDetails();

        $orderDetailsObject = $this->createOrderDetailsObject(
            $cartOrderDetails,
            $orderNumber
        );

        $logger->info('[PaymentMethod::processRefund] orderDetailsObject: ' . json_encode($orderDetailsObject));

        $processorTransactionId = $cartOrder->getCardinalProcessorTransactionId();
        $processorOrderId = $cartOrder->getCardinalProcessorOrderId();

        $logger->info('[PaymentMethod::processRefund] processorTransactionId: ' . $processorTransactionId);
        $logger->info('[PaymentMethod::processRefund] processorOrderId: ' . $processorOrderId);

        $response = $processorModuleClient->refundWithProcessorOrderId(
            $processorOrderId,
            $orderDetailsObject
        );

        $logger->info('[PaymentMethod::processRefund] response: ' . json_encode($response));

        $result = new PaymentMethodResult(
            $response,
            $orderNumber
        );

        return $result;
    }

    public function __toString() {
        return sprintf("[%s instance]", __CLASS__);
    }
}
