<?php
namespace CardinalCommerce\Payments\Objects;

class AuthorizationProcessor {

    public function __construct( $data ) {
        $this->ProcessorOrderId = $data->ProcessorOrderId;
        $this->ProcessorTransactionId = $data->ProcessorTransactionId;
        $this->ReasonCode = $data->ReasonCode;
        $this->ReasonDescription = $data->ReasonDescription;
    }

    public $ProcessorOrderId;
    public $ProcessorTransactionId;
    public $ReasonCode;
    public $ReasonDescription;

    public function __toString() {
        return json_encode( $this );
    }
}