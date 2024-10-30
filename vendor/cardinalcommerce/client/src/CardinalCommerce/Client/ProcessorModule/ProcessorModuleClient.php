<?php
namespace CardinalCommerce\Client\ProcessorModule;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Interfaces\APICredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelCredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelClientInterface;

use \CardinalCommerce\Client\Centinel\Messages as CentinelMessages;

class ProcessorModuleClient {

    private $_logger;
    private $_centinelClient;
    private $_apiCreds;
    private $_centinelCreds;

    public function __construct(
        LoggerInterface $logger,
        CentinelClientInterface $centinelClient,
        APICredentialsInterface $apiCreds,
        CentinelCredentialsInterface $centinelCreds
    ) {
        $this->_logger = $logger;
        $this->_centinelClient = $centinelClient;
        $this->_apiCreds = $apiCreds;
        $this->_centinelCreds = $centinelCreds;
    }

    public static function builder( LoggerInterface $logger ) {
        return new ProcessorModuleClientBuilder( $logger );
    }

    private function populateCredentials( $request ) {
        if ( $this->_centinelCreds != null ) {
            $request->MerchantId = $this->_centinelCreds->getMerchantId();
            $request->ProcessorId = $this->_centinelCreds->getProcessorId();
            $request->TransactionPwd = $this->_centinelCreds->getTransactionPwd();
        }
    }

    private function populateAddress( $request, $prefix, PaymentObjects\Address $addressObject ) {
        $prefixed = $addressObject->withPrefix( $prefix );
        foreach( $prefixed as $key => $value ) {
            if ( $key == 'Phone1' ) {
                $key = 'Phone';
            } else if ( $key == 'Phone2' ) {
                $key = 'AltPhone';
            }

            $request->$key = $value;
        }
    }

    private function populateReferenceData( $request, PaymentObjects\OrderDetails $orderDetails ) {
        $request->OrderNumber = $orderDetails->OrderNumber;
        if ( !empty( $orderDetails->OrderDescription ) ) {
            $request->OrderDescription = $orderDetails->OrderDescription;
        }
        $request->OrderDescription = $orderDetails->OrderDescription;
        $request->MerchantReferenceNumber = $orderDetails->OrderNumber;
        $request->MerchantData = $orderDetails->OrderNumber;
    }

    private function populateOrderDetails( $request, PaymentObjects\OrderDetails $orderDetails ) {
        $request->Amount = $orderDetails->Amount;
        $request->CurrencyCode = $orderDetails->CurrencyCode;
        $request->OrderDescription = $orderDetails->OrderDescription;
    }

    private function populateCardDetails( $request, PaymentObjects\Account $accountObject ) {
        if ( $accountObject != null ) {
            $request->CardNumber = $accountObject->AccountNumber;
            $request->CardCode = $accountObject->CardCode;
            $request->CardExpMonth = $accountObject->ExpirationMonth;
            $request->CardExpYear = $accountObject->ExpirationYear;
        }
    }

    private function populateConsumerData( $request, PaymentObjects\Consumer $consumerObject ) {
        if ( $consumerObject != null ) {
            if ( $consumerObject->BillingAddress != null ) {
                $request->EMail = $consumerObject->Email1;
                $this->populateAddress( $request, 'Billing', $consumerObject->BillingAddress );
            }

            if ( $consumerObject->ShippingAddress != null ) {
                $this->populateAddress( $request, 'Shipping', $consumerObject->ShippingAddress );
            }

            if ( $consumerObject->Account != null ) {
                $this->populateCardDetails( $request, $consumerObject->Account );
            }
        }
    }

    private function createAuthorizeMessage(
        PaymentObjects\OrderDetails $orderDetails,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $this->_logger->info('[ProcessorModuleClient::createAuthorizeMessage] orderDetails: ' . $orderDetails);

        $orderNumber = $orderDetails->OrderNumber;

        $this->_logger->info('[ProcessorModuleClient::createAuthorizeMessage] orderNumber: ' . $orderNumber);
        $this->_logger->info('[ProcessorModuleClient::createAuthorizeMessage] consumerObject: ' . $consumerObject);

        $authReq = new CentinelMessages\AuthorizationRequest();

        $this->populateCredentials( $authReq );
        $this->populateReferenceData( $authReq, $orderDetails );
        $this->populateOrderDetails( $authReq, $orderDetails );

        if ( $consumerObject != null ) {
            $this->populateConsumerData( $authReq, $consumerObject );
        }

        $authReq->TransactionType = 'CC';

        return $authReq;
    }

    /**
     * Authorize message
     *
     * @param PaymentObjects\OrderDetails $orderDetails The OrderDetails object
     * @param PaymentObjects\Consumer $consumerObject The Consumer object
     * @param object $responseJWTPayload The payload from the ResponseJWT (optional)
     *
     * @return bool Was the payment processed successfully?
     */
    public function authorize(
        PaymentObjects\OrderDetails $orderDetails,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $centinelClient = $this->_centinelClient;

        $authReq = $this->createAuthorizeMessage(
            $orderDetails,
            $consumerObject
        );

        return $centinelClient->sendMessage( $authReq );
    }

    /**
     * Authorize message
     *
     * @param PaymentObjects\OrderDetails $orderDetails The OrderDetails object
     * @param PaymentObjects\Consumer $consumerObject The Consumer object
     * @param object $responseJWTPayload The payload from the ResponseJWT (optional)
     *
     * @return bool Was the payment processed successfully?
     */
    public function authorizeWithPaymentExtensions(
        PaymentObjects\OrderDetails $orderDetailsObject,
        PaymentObjects\PaymentExtensions $paymentExtensions,
        PaymentObjects\Consumer $consumerObject = null
    ) {
        $centinelClient = $this->_centinelClient;

        $authReq = $this->createAuthorizeMessage(
            $orderDetails,
            $consumerObject
        );

        $authReq->CAVV = $paymentExtensions->Cavv;
        $authReq->ECIFlag = $paymentExtensions->Eci;

        return $centinelClient->sendMessage( $authReq );
    }

    /**
     * Authorize message
     *
     * @param PaymentObjects\OrderDetails $orderDetails The OrderDetails object
     * @param PaymentObjects\PaymentExtensions $paymentExtensions The payment extensions object
     *
     * @return bool Was the payment processed successfully?
     */
    public function authorizeWithAuthorizationProcessorObject(
        PaymentObjects\OrderDetails $orderDetails,
        PaymentObjects\AuthorizationProcessor $authorizationProcessorObject,
        PaymentObjects\PaymentExtensions $paymentExtensions
    ) {
        $centinelClient = $this->_centinelClient;

        $orderNumber = $orderDetails->OrderNumber;
        $this->_logger->info('[ProcessorModuleClient::authorizeWithAuthorizationProcessorObject] orderNumber: ' . $orderNumber);

        $authReq = $this->createAuthorizeMessage( $orderDetails );

        if ( $authorizationProcessorObject->ProcessorOrderId != null ) {
            $authReq->OrderId = $authorizationProcessorObject->ProcessorOrderId;
        }

        $authReq->Cavv = $paymentExtensions->Cavv;
        $authReq->Eci = $paymentExtensions->Eci;
        $authReq->Xid = $paymentExtensions->Xid;

        return $centinelClient->sendMessage( $authReq );
    }

    /**
     * Capture message
     *
     * @param string $processorOrderId
     * @param PaymentObjects\OrderDetails $orderDetails The OrderDetails object
     * @param PaymentObjects\Consumer $consumerObject The Consumer object
     * @param object $responseJWTPayload The payload from the ResponseJWT (optional)
     *
     * @return bool Was the payment processed successfully?
     */
    public function captureWithProcessorOrderId(
        $processorOrderId,
        PaymentObjects\OrderDetails $orderDetailsObject
    ) {
        $centinelClient = $this->_centinelClient;

        $this->_logger->info('[ProcessorModuleClient::captureWithProcessorOrderId] processorOrderId: ' . $processorOrderId);

        $captureReq = new CentinelMessages\CaptureRequest();
        $this->populateCredentials( $captureReq );
        $captureReq->TransactionType = 'CC';

        $captureReq->OrderId = $processorOrderId;
        $captureReq->Amount = $orderDetailsObject->Amount;
        $captureReq->CurrencyCode = $orderDetailsObject->CurrencyCode;

        return $centinelClient->sendMessage( $captureReq );
    }

    /**
     * Void message
     *
     * @param string $processorOrderId
     * @param PaymentObjects\OrderDetails $orderDetails The OrderDetails object
     * @param PaymentObjects\Consumer $consumerObject The Consumer object
     * @param object $responseJWTPayload The payload from the ResponseJWT (optional)
     *
     * @return bool Was the payment processed successfully?
     */
    public function voidWithProcessorOrderId(
        $processorOrderId,
        PaymentObjects\OrderDetails $orderDetailsObject
    ) {
        $centinelClient = $this->_centinelClient;

        $this->_logger->info('[ProcessorModuleClient::voidWithProcessorOrderId] processorOrderId: ' . $processorOrderId);

        $voidReq = new CentinelMessages\VoidRequest();
        $this->populateCredentials( $voidReq );
        $voidReq->TransactionType = 'CC';

        $voidReq->OrderId = $processorOrderId;
        $voidReq->Amount = $orderDetailsObject->Amount;
        $voidReq->CurrencyCode = $orderDetailsObject->CurrencyCode;

        return $centinelClient->sendMessage( $voidReq );
    }

    /**
     * Refund message
     *
     * @param string $processorOrderId
     * @param PaymentObjects\OrderDetails $orderDetails The OrderDetails object
     * @param PaymentObjects\Consumer $consumerObject The Consumer object
     * @param object $responseJWTPayload The payload from the ResponseJWT (optional)
     *
     * @return bool Was the payment processed successfully?
     */
    public function refundWithProcessorOrderId(
        $processorOrderId,
        PaymentObjects\OrderDetails $orderDetailsObject
    ) {
        $centinelClient = $this->_centinelClient;

        $this->_logger->info('[ProcessorModuleClient::refundWithProcessorOrderId] processorOrderId: ' . $processorOrderId);

        $refundReq = new CentinelMessages\RefundRequest();
        $this->populateCredentials( $refundReq );
        $refundReq->TransactionType = 'CC';

        $refundReq->OrderId = $processorOrderId;
        $refundReq->Amount = $orderDetailsObject->Amount;
        $refundReq->CurrencyCode = $orderDetailsObject->CurrencyCode;

        return $centinelClient->sendMessage( $refundReq );
    }
}
