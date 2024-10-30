<?php
namespace CardinalCommerce\Payments\Objects;

class Account {

    public function __construct( $data ) {
        $this->AccountNumber = $data->AccountNumber;
        $this->ExpirationMonth = $data->ExpirationMonth;
        $this->ExpirationYear = $data->ExpirationYear;
        $this->NameOnAccount = $data->NameOnAccount;
        $this->CardCode = $data->CardCode;
    }

    public $AccountNumber;
    public $ExpirationMonth;
    public $ExpirationYear;
    public $NameOnAccount;
    public $CardCode;

    public function __toString() {
        return json_encode( $this );
    }
}