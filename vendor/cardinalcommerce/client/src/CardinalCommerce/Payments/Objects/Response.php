<?php
namespace CardinalCommerce\Payments\Objects;

class Response {
    private static function parseData( &$self, $data ) {
        if ( property_exists( $data, 'ErrorNumber' ) ) {
            $self->ActionCode = $data->ActionCode;
        }

        if ( property_exists( $data, 'ErrorDescription' ) ) {
            $self->ActionCode = $data->ActionCode;
        }

        if ( property_exists( $data, 'ActionCode' ) ) {
            $self->ActionCode = $data->ActionCode;
        }

        if ( property_exists( $data, 'Validated' )
               && $data->Validated === true ) {
            $self->Validated = true;
        }

        if ( property_exists( $data, 'Payment' ) ) {
            $self->Payment = new Payment(
                $data->Payment
            );
        }

        // NEXTREV: Support Consumer object
        // NEXTREV: Support Token object
        // NEXTREV: Support Authorization object

        if ( property_exists( $data, 'AuthorizationProcessor' ) ) {
            $self->AuthorizationProcessor = new AuthorizationProcessor(
                $data->AuthorizationProcessor
            );
        }
    }

    public function __construct( $data ) {
        $this->ErrorNumber = 0;
        $this->ErrorDescription = '';

        $this->Consumer = null;
        $this->Payment = null;
        $this->Token = null;
        $this->Authorization = null;
        $this->AuthorizationProcessor = null;

        $this->Validated = false;
        $this->ActionCode = null;

        if ( property_exists($data, "Payload") ) {
            self::parseData( $this, $data->Payload );
        } else {
            self::parseData( $this, $data );
        }
    }

    public $ErrorNumber;
    public $ErrorDescription;
    public $Consumer;
    public $Payment;
    public $Token;
    public $Authorization;
    public $AuthorizationProcessor;
    public $Validated;
    public $ActionCode;
}