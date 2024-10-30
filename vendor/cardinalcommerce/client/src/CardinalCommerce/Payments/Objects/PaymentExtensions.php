<?php
namespace CardinalCommerce\Payments\Objects;

class PaymentExtensions {
    public function __construct( $data ) {
        $this->Enrolled = $data->Enrolled;
        $this->Cavv = $data->CAVV;
        $this->Eci = $data->ECIFlag;
        $this->PAResStatus = $data->PAResStatus;
        $this->SignatureVerification = $data->SignatureVerification;
        $this->Xid = $data->XID;
        $this->UCAFIndicator = $data->UCAFIndicator;
    }

    public $Enrolled;
    public $Cavv;
    public $Eci;
    public $PAResStatus;
    public $SignatureVerification;
    public $Xid;
    public $UCAFIndicator;

    public function __toString() {
        return json_encode( $this );
    }
}
