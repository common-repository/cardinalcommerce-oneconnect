<?php
namespace CardinalCommerce\Payments\Objects;

class Payment {
    public function __construct( $data ) {
        $this->ReasonCode = null;
        $this->ReasonDescription = null;
        $this->ProcessorTransactionId = null;
        $this->ExtendedData = null;

        if ( property_exists($data, 'ReasonCode') ) {
            $this->ReasonCode = $data->ReasonCode;
        }

        if ( property_exists($data, 'ReasonDescription') ) {
            $this->ReasonDescription = $data->ReasonDescription;
        }

        if ( property_exists($data, 'ProcessorTransactionId') ) {
            $this->ProcessorTransactionId = $data->ProcessorTransactionId;
        }

        if ( property_exists($data, 'ExtendedData') && $data->ExtendedData != null ) {
            $this->ExtendedData = new PaymentExtensions( $data->ExtendedData );
        }
    }

    public $ReasonCode;
    public $ReasonDescription;
    public $ProcessorTransactionId;
    public $ExtendedData;

    public function toJSONObject() {
        $data = (object) array();

        if ( !empty( $this->ReasonCode ) ) {
            $data->ReasonCode = $this->ReasonCode;
        }

        if ( !empty( $this->ReasonDescription ) ) {
            $data->ReasonCode = $this->ReasonCode;
        }

        if ( !empty( $this->ReasonCode ) ) {
            $data->ReasonCode = $this->ReasonCode;
        }

        if ( !empty( $this->ProcessorTransactionId ) ) {
            $data->ReasonCode = $this->ReasonCode;
        }

        if ( !empty( $this->ExtendedData ) && method_exists( $this->ExtendedData, 'toJSONObject' ) ) {
            $data->ExtendedData = $this->ExtendedData->toJSONObject();
        }

        if ( !empty( $this->ReasonCode ) ) {
            $data->ReasonCode = $this->ReasonCode;
        }
    }

    public function __toString() {
        return json_encode( $this );
    }
}