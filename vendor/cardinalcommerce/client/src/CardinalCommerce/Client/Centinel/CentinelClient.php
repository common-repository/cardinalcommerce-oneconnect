<?php
namespace CardinalCommerce\Client\Centinel;

use \Psr\Log\LoggerInterface;
use \Httpful\Mime;

use \CardinalCommerce\Payments\Interfaces\CentinelClientInterface;

class CentinelClient implements CentinelClientInterface {

    const CENTINEL_SOURCE = "PHPTC2";
    const CENTINEL_SOURCE_VERSION = "1.0.0";

    private $_logger;
    private $_transactionUrl;
    private $_timeout;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $transactionUrl
     * @param string $transactionPwd
     * @param int $timeout Timeout in milliseconds.
     */
    public function __construct( LoggerInterface $logger, $transactionUrl, $transactionPwd, $timeout ) {
        $this->_logger = $logger;
        $this->_transactionUrl = $transactionUrl;
        $this->_timeout = $timeout;

        $this->_messageProcessor = new CentinelMessageProcessor( $logger, $transactionUrl, $transactionPwd, $timeout );
    }

    private function createMessage( $msg_type, $version, $data ) {
        $msg = new \stdClass;
        $msg->MsgType = $msg_type;
        $msg->Version = sprintf("%s", $version);

        foreach( $data as $key => $value ) {
            if ( $key != 'MsgType' && $key != 'Version' ) {
                $msg->$key = $value;
            }
        }

        return $msg;
    }

    private function createResponseMessage( $msg_type, $data ) {
        switch ( $msg_type ) {
            case 'cmpi_lookup':
                return new Messages\LookupResponse($data);
            case 'cmpi_authenticate':
                return new Messages\AuthenticateResponse($data);
            case 'cmpi_authorize':
                return new Messages\AuthorizationResponse($data);
            case 'cmpi_capture':
                return new Messages\CaptureResponse($data);
            case 'cmpi_sale':
                return new Messages\SaleResponse($data);
            case 'cmpi_refund':
                return new Messages\RefundResponse($data);
            case 'cmpi_void':
                return new Messages\VoidResponse($data);
            default:
                throw new \Exception('Unsupported response message type');
        }
    }

    private function processResponse( $msg_type, $xml ) {
        $logger = $this->_logger;
        $messageProcessor = $this->_messageProcessor;

        $responseData = $messageProcessor->parseXmlAsObject( $xml );
        $responseObj = $this->createResponseMessage( $msg_type, $responseData );

        return $responseObj;
    }

    protected function doFormPost( $url, $timeout, $body ) {
        $response = \Httpful\Request::post( $url )
            ->body( $body )
            ->timeout( $timeout )
            ->sendsType( Mime::FORM )
            ->send();

        $this->_logger->info('[CentinelClient::doFormPost] Got response: {response}', array(
            'response' => $response
        ));

        $responseBody = sprintf("%s", $response);

        return $responseBody;        
    }

    private function makeRequest( $msg_type, $version, $data ) {
        $url = $this->_transactionUrl;
        $timeout = $this->_timeout;

        $msg = $this->createMessage( $msg_type, $version, $data );

        $request = $this->_messageProcessor->createXmlMessage( $msg );

        $this->_logger->info('[CentinelClient::makeRequest] Making request: {request}', array(
            'request' => $request
        ));

        $body = $this->_messageProcessor->createPostBody( $msg );

        $this->_logger->info('[CentinelClient::makeRequest] Sending POST to {transactionUrl} with body: {body}', array(
            'transactionUrl' => $url,
            'body' => $body
        ));

        $response = $this->doFormPost(
            $url,
            $timeout,
            $body
        );

        $this->_logger->info('[CentinelClient::makeRequest] Got response: {response}', array(
            'response' => $response
        ));

        $responseObj = $this->processResponse( $msg_type, $response );

        $this->_logger->info('[CentinelClient::makeRequest] Got response object: {responseObj}', array(
            'responseObj' => json_encode($responseObj)
        ));

        return $responseObj;
    }

    public function sendMessage( $obj ) {
        $ref = new \ReflectionClass( $obj );
        $props = $ref->getProperties( \ReflectionProperty::IS_PUBLIC );

        $data = new \stdClass;

        foreach( $props as $prop ) {
            $name = $prop->getName();
            $value = $prop->getValue( $obj );
            $data->$name = $value;
        }

        $msg_type = $obj->MsgType;
        $version = $obj->Version;

        return $this->makeRequest( $msg_type, $version, $data );
    }
}
