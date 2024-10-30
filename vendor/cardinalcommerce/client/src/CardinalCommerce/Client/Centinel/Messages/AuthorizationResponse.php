<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class AuthorizationResponse {

    public function __construct( $data ) {
        $this->ErrorNo = $data->ErrorNo;
        $this->ErrorDesc = $data->ErrorDesc;
        $this->ReasonCode = $data->ReasonCode;
        $this->ReasonDesc = $data->ReasonDesc;

        $this->StatusCode = $data->StatusCode;
        $this->OrderId = $data->OrderId;
        $this->TransactionId = $data->TransactionId;
        $this->MerchantData = null;
        $this->MerchantReferenceNumber = null;
        $this->ProcessorOrderNumber = null;

        $this->AuthorizationCode = null;
        $this->AVSResult = null;
        $this->CardCodeResult = null;

        if ( property_exists( $data, "ProcessorOrderNumber" ) ) {
            $this->ProcessorOrderNumber = $data->ProcessorOrderNumber;
        }

        if ( property_exists( $data, "MerchantData" ) ) {
            $this->MerchantData = $data->MerchantData;
        }

        if ( property_exists( $data, "MerchantReferenceNumber" ) ) {
            $this->MerchantReferenceNumber = $data->MerchantReferenceNumber;
        }

        if ( property_exists( $data, "AuthorizationCode" ) ) {
            $this->AuthorizationCode = $data->AuthorizationCode;
        }

        if ( property_exists( $data, "AVSResult" ) ) {
            $this->AVSResult = $data->AVSResult;
        }

        if ( property_exists( $data, "CardCodeResult" ) ) {
            $this->CardCodeResult = $data->CardCodeResult;
        }
    }

    public $ErrorNo;
    public $ErrorDesc;

    public $ReasonCode;
    public $ReasonDesc;

    public $StatusCode;
    public $OrderId;
    public $TransactionId;
    public $MerchantData;
    public $MerchantReferenceNumber;
    public $ProcessorOrderNumber;

    public $AuthorizationCode;
    public $AVSResult;
    public $CardCodeResult;
}
