<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Processors;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\PaymentAuthTypes;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\WooCommerce\Settings\CartSettings;
use \CardinalCommerce\Payments\Carts\WooCommerce\Settings\AdminSettings;
use \CardinalCommerce\Payments\Carts\WooCommerce\Order as CartOrders;
use \CardinalCommerce\Payments\Carts\WooCommerce\Forms as CartForms;

class CheckoutProcessor {

    public function __construct( $paymentMethodId ) {
        $this->_formProcessor = new CartForms\FormProcessor( $paymentMethodId );
    }

    private function getCartOrderDetails() {
        $cart = \WC()->cart;
        $cartOrderDetails = CartOrders\CartOrderDetails::fromWCCart($cart);

        return $cartOrderDetails;
    }

    public function renderHiddenInputs() {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();
        $cartProcessor = \wc_gateway_cardinalpm()->objects()->common_objects()->getCartProcessor();
        $cartOrderDetails = $this->getCartOrderDetails();

        echo $cartProcessor->renderServerProvidedHiddenInputs(
            $cartOrderDetails
        );

        echo $cartProcessor->renderClientProvidedHiddenInputs(
            $cartOrderDetails
        );
    }

    public function renderScriptBlock() {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();
        $cartProcessor = \wc_gateway_cardinalpm()->objects()->common_objects()->getCartProcessor();
        $cartOrderDetails = $this->getCartOrderDetails();

        $logger->info('[CheckoutProcessor::renderScriptBlock] cartOrderDetails: ' . $cartOrderDetails );

        echo $cartProcessor->renderScriptBlock(
            $cartOrderDetails
        );
    }

    private function parsePostData( $postData ) {
        return $this->_formProcessor->processPostData(
            $postData
        );
    }

    protected function createResponseObject( CartForms\FormValues $formValues ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();
        $responseJWTParser = \wc_gateway_cardinalpm()->objects()->common_objects()->getResponseJWTParser();

        $responseJWT = $formValues->getResponseJWT();
        $logger->info('[CheckoutProcessor::processPayment] responseJWT: ' . $responseJWT);

        $responseJWTPayload = $responseJWTParser->parse( $responseJWT );
        $logger->info('[CheckoutProcessor::processPayment] responseJWTPayload: ' . json_encode($responseJWTPayload));

        return new PaymentObjects\Response( $responseJWTPayload );
    }

    public function processPayment(
        $order_id,
        $authType,
        $postData
    ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();
        $cartProcessor = \wc_gateway_cardinalpm()->objects()->common_objects()->getCartProcessor();

        $logger->info('[CheckoutProcessor::processPayment] order_id: ' . json_encode($order_id));
        $logger->info('[CheckoutProcessor::processPayment] authType: ' . $authType);
        $logger->info('[CheckoutProcessor::processPayment] postData: ' . json_encode($postData));

        $formValues = $this->parsePostData( $postData );

        // Get complete account details (including card number and CVV)
        $consumerObject = $formValues->getConsumerObject( TRUE );
        $logger->info('[CheckoutProcessor::processPayment] consumerObject: ' . json_encode($consumerObject));

        // Wrap the order currently being processed
        $cartOrder = CartOrders\CartOrder::forCartOrderId( $order_id );

        $responseObject = $this->createResponseObject( $formValues );

        $paymentMethodOrder = $cartProcessor->createPaymentMethodOrderFromResponse(
            $cartOrder,
            $authType,
            $responseObject,
            $consumerObject
        );

        // Call Payment Processor

        $success = $cartProcessor->processOrder(
            $cartOrder,
            $authType,
            $consumerObject,
            $responseObject
        );

        $logger->info('[CheckoutProcessor::processPayment] authType: ' . json_encode($authType));

        if ( PaymentAuthTypes::AUTH_CAPTURE === $authType ) {
            $logger->info('[CheckoutProcessor::processPayment] capturing due to auth type ' . json_encode($authType));

            $success = $cartProcessor->processCapture(
                $cartOrder
            );
        }

        return $success;
    }

    public function processCapture( $order_id ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();
        $cartProcessor = \wc_gateway_cardinalpm()->objects()->common_objects()->getCartProcessor();

        $logger->info('[CheckoutProcessor::processCapture] order_id: ' . json_encode($order_id));

        // Wrap the order currently being processed
        $cartOrder = CartOrders\CartOrder::forCartOrderId( $order_id );
        $logger->info('[CheckoutProcessor::processCapture] cartOrder: ' . var_export( $cartOrder, true ));

        $success = $cartProcessor->processCapture(
            $cartOrder
        );

        return $success;
    }

}