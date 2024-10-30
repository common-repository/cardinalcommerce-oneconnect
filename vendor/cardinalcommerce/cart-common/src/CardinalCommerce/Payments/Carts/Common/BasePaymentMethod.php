<?php
namespace CardinalCommerce\Payments\Carts\Common;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethod;
use \CardinalCommerce\Payments\Carts\Common\BasePaymentMethodOrder;

/**
 * Implements a Songbird payment method.
 *
 * This can be CCA, ProcessorModule, or another payment integration.
 *
 * WIP
 */
abstract class BasePaymentMethod {

    /**
     * Create ServerJWTPayload for Songbird
     *
     * @return SongbirdObjects\ServerJWTPayload
     */
    public abstract function createServerJWTPayload(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        PaymentObjects\OrderDetails $orderDetailsObject = null,
        PaymentObjects\Consumer $consumerObject = null
    );

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
    public abstract function createPaymentMethodOrder(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        $authType,
        PaymentObjects\Consumer $consumerObject
    );

    /**
     * Create an order from Consumer and Response object
     *
     * @param BaseCartOrder $cartOrder
     * @param PaymentObjects\Consumer $consumerObject The Cardinal Consumer object
     * @param PaymentObjects\Response $responseObject The Cardinal Response object (ResponseJWT payload)
     *
     * @return BasePaymentMethodOrder $order
     */
    public abstract function createPaymentMethodOrderFromResponse(
        BaseCartOrder $cartOrder,
        $authType,
        PaymentObjects\Response $responseObject = null,
        PaymentObjects\Consumer $consumerObject = null
    );

    /**
     * Create an order for an existing (authorized) order
     *
     * @param BaseCartOrder $cartOrder
     *
     * @return BasePaymentMethodOrder $order
     */
    public abstract function createPaymentMethodOrderForExistingOrder(
        BaseCartOrder $cartOrder
    );

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
    public abstract function processOrderAuthorization(
        BasePaymentMethodOrder $order,
        BaseCartOrder $cartOrder,
        PaymentObjects\Consumer $consumerObject
    );

    /**
     * Process capture for the given (authorized) order
     *
     * @param BasePaymentMethodOrder $order
     * @param BaseCartOrder
     *
     * @return bool Was the payment processed successfully?
     */
    public abstract function processOrderCapture(
        BasePaymentMethodOrder $order,
        BaseCartOrder $cartOrder
    );

    /**
     * Void for the given order
     *
     * @param BasePaymentMethodOrder $order
     * @param BaseCartOrder
     *
     * @return bool Was the payment processed successfully?
     */
    public abstract function processVoid(
        BasePaymentMethodOrder $order,
        BaseCartOrder $cartOrder
    );

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
    public abstract function processRefund(
        BasePaymentMethodOrder $order,
        BaseCartOrder $cartOrder,
        $rawAmount,
        $reason = ''
    );

}