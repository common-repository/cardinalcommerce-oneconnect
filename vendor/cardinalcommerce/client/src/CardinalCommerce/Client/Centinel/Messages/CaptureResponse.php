<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class CaptureResponse {

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

        if ( property_exists( $data, "MerchantData" ) ) {
            $this->MerchantData = $data->MerchantData;
        }

        if ( property_exists( $data, "MerchantReferenceNumber" ) ) {
            $this->MerchantReferenceNumber = $data->MerchantReferenceNumber;
        }

        if ( property_exists( $data, "ProcessorOrderNumber" ) ) {
            $this->ProcessorOrderNumber = $data->ProcessorOrderNumber;
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
}
