<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class VoidResponse {

    public function __construct( $data ) {
        $this->ErrorNo = $data->ErrorNo;
        $this->ErrorDesc = $data->ErrorDesc;
        $this->ReasonCode = $data->ReasonCode;
        $this->ReasonDesc = $data->ReasonDesc;

        $this->StatusCode = $data->StatusCode;
        $this->OrderId = $data->OrderId;
        $this->TransactionId = $data->TransactionId;
        $this->MerchantData = null;

        if ( property_exists( $data, "MerchantData" ) ) {
            $this->MerchantData = $data->MerchantData;
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
}
