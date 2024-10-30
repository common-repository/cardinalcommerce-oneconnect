<?php

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Interfaces\APICredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelCredentialsInterface;
use \CardinalCommerce\Payments\Interfaces\CentinelClientInterface;

use \CardinalCommerce\Client\ProcessorModule\ProcessorModuleClient;

ini_set( 'display_errors', 'on' );
error_reporting( E_ALL );

class ProcessorModuleClientTest extends PHPUnit_Framework_TestCase {

    public function testAuthorize() {

        $logger = $this->getMockBuilder( LoggerInterface::class )
            ->setMethods( ['info'] )->getMock();

        $centinelClient = $this->getMock( CentinelClientInterface::class );

        $centinelClient->expects( $this->once() )
            ->method( 'sendMessage' )
            ->with( $this->callback(function( $msg ) {

                return $msg->MsgType == 'cmpi_authorize' &&
                    $msg->Version == '1.7' &&
                    $msg->Amount == 999 &&
                    $msg->OrderNumber == 3333 &&
                    $msg->OrderDescription == 'Order #3333' &&
                    $msg->CurrencyCode == 840 &&
                    $msg->CAVV == 'cafedead' &&
                    $msg->ECIFlag == 'cafecafe';

            }));

        $apiCreds = $this->getMock( APICredentialsInterface::class );
        $centinelCreds = $this->getMock( CentinelCredentialsInterface::class );

        $orderDetails = new PaymentObjects\OrderDetails((object) array (
            'Amount' => 999,
            'CurrencyCode' => 840,
            'OrderNumber' => 3333,
            'OrderDescription' => 'Order #3333',
            'OrderChannel' => 'S',
            'TransactionId' => null
        ));

        $consumer = $this->getMock( PaymentObjects\Consumer::class );

        $client = ProcessorModuleClient::builder( $logger )
            ->setCentinelClient( $centinelClient )
            ->setAPICredentials( $apiCreds )
            ->setCentinelCredentials( $centinelCreds )
            ->build();

        $payload = (object) array(
            'Payload' => (object) array(
                'Payment' => (object) array(
                    'Type' => '',
                    'ReasonCode' => '',
                    'ReasonDescription' => '',
                    'ProcessorTransactionId' => '',
                    'ExtendedData' => (object) array(
                        'Enrolled' => 'Y',
                        'CAVV' => 'cafedead',
                        'ECIFlag' => 'cafecafe',
                        'PAResStatus' => 'Y',
                        'SignatureVerification' => 'Y',
                        'XID' => '',
                        'UCAFIndicator' => 2
                    )
                )
            )
        );

        $client->authorize( $orderDetails, $consumer, $payload );
    }
}