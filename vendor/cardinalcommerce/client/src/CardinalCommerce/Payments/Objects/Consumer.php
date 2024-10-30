<?php
namespace CardinalCommerce\Payments\Objects;

class Consumer {

    public function __construct( $data ) {
        $this->Email1 = null;
        $this->Email2 = null;
        $this->ShippingAddress = null;
        $this->BillingAddress = null;
        $this->Account = null;

        if ( property_exists($data, 'Email1') && $data->Email1 != null ) {
            $this->Email1 = $data->Email1;
        }

        if ( property_exists($data, 'Email2') && $data->Email2 != null ) {
            $this->Email2 = $data->Email2;
        }

        if ( property_exists($data, 'ShippingAddress') && $data->ShippingAddress != null ) {
            $this->ShippingAddress = new Address( $data->ShippingAddress );
        }

        if ( property_exists($data, 'BillingAddress') && $data->BillingAddress != null ) {
            $this->BillingAddress = new Address( $data->BillingAddress );
        }

        if ( property_exists($data, 'Account') && $data->Account != null ) {
            $this->Account = new Account( $data->Account );
        }
    }

    public $Email1;
    public $Email2;
    public $ShippingAddress;
    public $BillingAddress;
    public $Account;

    public function toJSONObject() {
        $data = (object) array();

        if ( !empty( $this->Email1 ) ) {
            $data->OrderDescription = $this->OrderDescription;
        }

        if ( !empty( $this->Email2 ) ) {
            $data->OrderChannel = $this->OrderChannel;
        }

        if ( !empty( $this->ShippingAddress ) && method_exists( $this->ShippingAddress, 'toJSONObject' ) ) {
            $data->ShippingAddress = $this->ShippingAddress->toJSONObject();
        }

        if ( !empty( $this->BillingAddress ) && method_exists( $this->BillingAddress, 'toJSONObject' ) ) {
            $data->BillingAddress = $this->BillingAddress;
        }

        if ( !empty( $this->Account ) && method_exists( $this->Account, 'toJSONObject' ) ) {
            $data->Account = $this->Account;
        }

        return $data;
    }

    public function __toString() {
        return json_encode( $this );
    }
}