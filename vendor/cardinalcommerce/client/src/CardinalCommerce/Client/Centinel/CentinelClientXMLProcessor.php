<?php
namespace CardinalCommerce\Client\Centinel;

use \Psr\Log\LoggerInterface;

class CentinelClientXMLProcessor {

    private $_logger;
    private $_xmlReader;
    private $_rootElementName;

    public function __construct(
        LoggerInterface $logger,
        $xmlReader,
        $rootElementName
    ) {
        $this->_logger = $logger;
        $this->_xmlReader = $xmlReader;
        $this->_rootElementName = $rootElementName;
    }

    protected function readTextContents() {
        $xmlReader = $this->_xmlReader;

        $textContents = "";
        while( $xmlReader->read() ) {
            $isTextNode = ($xmlReader->nodeType == \XMLReader::TEXT
                || $xmlReader->nodeType == \XMLReader::CDATA
                || $xmlReader->nodeType == \XMLReader::WHITESPACE
                || $xmlReader->nodeType == \XMLReader::SIGNIFICANT_WHITESPACE);

            if ( ! $isTextNode ) {
                break;
            }

            $textContents .= $xmlReader->value;
        }

        return $textContents;
    }

    protected function expectRootElement() {
        $xmlReader = $this->_xmlReader;
        $rootElementName = $this->_rootElementName;

        if ( $xmlReader->name != $rootElementName || $xmlReader->nodeType != \XMLReader::ELEMENT ) {
            throw new Exceptions\InvalidXMLResponseException();
        }
    }

    protected function expectElement() {
        $xmlReader = $this->_xmlReader;

        if ( $xmlReader->nodeType != \XMLReader::ELEMENT ) {
            throw new Exceptions\InvalidXMLResponseException();
        }
    }

    protected function expectEndElement() {
        $xmlReader = $this->_xmlReader;

        if ( $xmlReader->nodeType != \XMLReader::END_ELEMENT ) {
            throw new Exceptions\InvalidXMLResponseException();
        }
    }

    protected function readKeyValueElement( &$result ) {
        $logger = $this->_logger;
        $xmlReader = $this->_xmlReader;

        $this->expectElement();
            $key = $xmlReader->name;
            $value = $this->readTextContents();

            $logger->info('[CentinelClientXMLProcessor::readKeyValueElement] key [{key}] value [{value}]',
                array('key' => $key, 'value' => $value));

            $result->$key = $value;
        $this->expectEndElement();
    }

    public function readAsObject() {
        $logger = $this->_logger;
        $xmlReader = $this->_xmlReader;
        $rootElementName = $this->_rootElementName;

        $result = (object) array();

        $xmlReader->read();
        $this->expectRootElement();

        while( $xmlReader->read() ) {
            if ( $xmlReader->nodeType == \XMLReader::END_ELEMENT && $xmlReader->name == $rootElementName ) {
                $logger->info('[CentinelClientXMLProcessor::readAsObject] reached root element closing tag');
                break;
            } else if ( $xmlReader->nodeType == \XMLReader::ELEMENT ) {
                $logger->info('[CentinelClientXMLProcessor::readAsObject] reading key value');
                $this->readKeyValueElement( $result );
            }
        }

        return $result;
    }
}