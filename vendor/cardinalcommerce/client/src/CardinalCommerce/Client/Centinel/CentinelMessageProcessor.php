<?php
namespace CardinalCommerce\Client\Centinel;

use \Psr\Log\LoggerInterface;

class CentinelMessageProcessor {

    const CENTINEL_TAG = 'CardinalMPI';

    private $_logger;
    private $_transactionUrl;
    private $_timeout;

    public function __construct( LoggerInterface $logger, $transactionUrl, $transactionPwd, $timeout ) {
        $this->_logger = $logger;
        $this->_transactionUrl = $transactionUrl;
        $this->_timeout = $timeout;
    }

    public function createXmlMessage( $msg ) {
        $xml = new CentinelClientXMLWriter( self::CENTINEL_TAG );
        $xml->openMemory();

        $merchantDate = new \DateTime;

        $xml->start();
        $xml->centinelHeader(
            CentinelClient::CENTINEL_SOURCE,
            CentinelClient::CENTINEL_SOURCE_VERSION,
            $this->_transactionUrl,
            $merchantDate,
            $this->_timeout
        );

        foreach( $msg as $key => $value ) {
            $xml->element( $key, $value );
        }

        $xml->end();

        return $xml->outputMemory( TRUE );
    }

    public function generatePayload( $msg ) {
        $fields = array();
        foreach( $msg as $key => $value ) {
            if ( $key != 'TransactionPwd' ) {
                $fields[$key] = $value;
            }
        }
        $payload = http_build_query( $data );
        $hash = sha1( $payload );
        $payload .= sprintf("&Hash=%s", $hash);

        return $payload;
    }

    public function createPostBody( $msg ) {
        $logger = $this->_logger;
        $xml = $this->createXmlMessage( $msg );

        $logger->info('[CentinelMessageProcessor::createPostBody] Sending request: ' . $xml);

        $data = array(
            'cmpi_msg' => $xml
        );

        return http_build_query( $data );
    }

    public function parseXmlAsObject( $xml ) {
        $logger = $this->_logger;

        $xmlReader = new \XMLReader();
        $xmlReader->xml( $xml );
        $xmlProcessor = new CentinelClientXMLProcessor(
            $logger,
            $xmlReader,
            self::CENTINEL_TAG
        );

        return $xmlProcessor->readAsObject();
    }
}