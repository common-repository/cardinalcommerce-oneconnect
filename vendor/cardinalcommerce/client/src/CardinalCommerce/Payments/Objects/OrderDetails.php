<?php
namespace CardinalCommerce\Payments\Objects;

class OrderDetails {

    public function __construct( $data ) {
        $this->OrderNumber = null;
        $this->Amount = $data->Amount;
        $this->CurrencyCode = $data->CurrencyCode;
        $this->OrderDescription = null;
        $this->OrderChannel = null;
        $this->TransactionId = null;
        
        if ( property_exists($data, 'OrderNumber') && !empty( $data->OrderNumber ) ) {
            $this->OrderNumber = $data->OrderNumber;
        }

        if ( property_exists($data, 'OrderDescription') && !empty( $data->OrderDescription ) ) {
            $this->OrderDescription = $data->OrderDescription;
        }

        if ( property_exists($data, 'OrderChannel') && !empty( $data->OrderChannel ) ) {
            $this->OrderChannel = $data->OrderChannel;
        }

        if ( property_exists($data, 'TransactionId') && !empty( $data->TransactionId ) ) {
            $this->TransactionId = $data->TransactionId;
        }
    }

    public $OrderNumber;
    public $Amount;
    public $CurrencyCode;
    public $OrderDescription;
    public $OrderChannel;
    public $TransactionId;

    public function __toString() {
        return json_encode( $this );
    }

    public function toJSONObject() {
        $data = (object) array(
            'Amount' => $this->Amount,
            'CurrencyCode' => $this->CurrencyCode
        );

        if ( !empty( $this->OrderNumber ) ) {
            $data->OrderNumber = $this->OrderNumber;
        }

        if ( !empty( $this->OrderDescription ) ) {
            $data->OrderDescription = $this->OrderDescription;
        }

        if ( !empty( $this->OrderChannel ) ) {
            $data->OrderChannel = $this->OrderChannel;
        }

        if ( !empty( $this->TransactionId ) ) {
            $data->TransactionId = $this->TransactionId;
        }

        return $data;
    }
}