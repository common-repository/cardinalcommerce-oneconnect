<?php
namespace CardinalCommerce\Client\Centinel;

class CentinelClientXMLWriter extends \XMLWriter {

    private $_rootElementName;

    public function __construct( $rootElementName ) {
        $this->_rootElementName = $rootElementName;
    }

    public function start() {
        $this->startDocument( '1.0', 'UTF-8' );
        $this->startElement( $this->_rootElementName );
    }

    public function element( $tag, $value ) {
        $this->startElement( $tag );
        $this->text( $value );
        //$this->endElement( $tag );
        $this->endElement();
    }

    public function centinelHeader( $source, $sourceVersion, $transactionUrl, \DateTime $merchantDate, $timeout ) {
        $this->element( 'Source', $source );
        $this->element( 'SourceVersion', $sourceVersion );
        $this->element( 'SendTimeout' , sprintf( "%d", $timeout ) );
        $this->element( 'ReceiveTimeout', sprintf( "%d", $timeout ) );
        $this->element( 'ConnectTimeout', sprintf( "%d", $timeout ) );
        $this->element( 'TransactionUrl', $transactionUrl );
        $this->element( 'MerchantSystemDate', $merchantDate->format( 'Y-m-d\TH:i:s\Z' ) );
    }

    public function end() {
        //$this->endElement( $this->_rootElementName );
        $this->endElement();
    }
}