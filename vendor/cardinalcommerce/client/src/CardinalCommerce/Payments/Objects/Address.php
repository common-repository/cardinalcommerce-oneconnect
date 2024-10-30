<?php
namespace CardinalCommerce\Payments\Objects;

class Address {

    public function __construct( $data ) {
        $this->FirstName = $data->FirstName;
        $this->MiddleName = $data->MiddleName;
        $this->LastName = $data->LastName;
        $this->Address1 = $data->Address1;
        $this->Address2 = $data->Address2;
        $this->Address3 = $data->Address3;
        $this->City = $data->City;
        $this->State = $data->State;
        $this->PostalCode = $data->PostalCode;
        $this->CountryCode = $data->CountryCode;
        $this->Phone1 = $data->Phone1;
        $this->Phone2 = $data->Phone2;
    }

    public $FirstName;
    public $MiddleName;
    public $LastName;
    public $Address1;
    public $Address2;
    public $Address3;
    public $City;
    public $State;
    public $PostalCode;
    public $CountryCode;
    public $Phone1;
    public $Phone2;

    public function withPrefix( $prefix ) {
        $obj = new \stdClass;
        foreach( $this as $key => $value ) {
            $prefixed = $prefix . $key;
            $obj->$prefixed = $value;
        }
        return $obj;
    }

    public function toJSONObject() {
        $data = (object) array();

        if ( !empty( $this->FirstName ) ) {
            $data->MiddleName = $this->MiddleName;
        }

        if ( !empty( $this->MiddleName ) ) {
            $data->MiddleName = $this->MiddleName;
        }

        if ( !empty( $this->LastName ) ) {
            $data->MiddleName = $this->MiddleName;
        }

        if ( !empty( $this->Address1 ) ) {
            $data->Address1 = $this->Address1;
        }

        if ( !empty( $this->Address2 ) ) {
            $data->Address2 = $this->Address2;
        }

        if ( !empty( $this->Address3 ) ) {
            $data->Address3 = $this->Address3;
        }

        if ( !empty( $this->City ) ) {
            $data->City = $this->City;
        }

        if ( !empty( $this->State ) ) {
            $data->State = $this->State;
        }

        if ( !empty( $this->PostalCode ) ) {
            $data->PostalCode = $this->PostalCode;
        }

        if ( !empty( $this->CountryCode ) ) {
            $data->CountryCode = $this->CountryCode;
        }

        if ( !empty( $this->Phone1 ) ) {
            $data->Phone1 = $this->Phone1;
        }

        if ( !empty( $this->Phone2 ) ) {
            $data->Phone2 = $this->Phone2;
        }

        return $data;
    }

    public function __toString() {
        return json_encode( $this );
    }
}