<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class AuthenticationResponse {

    public $ErrorNo;
    public $ErrorDesc;

    public $ReasonCode;
    public $ReasonDesc;

    public $PAResStatus;
    public $SignatureVerification;
    public $Cavv;
    public $EciFlag;
    public $Xid;
    public $OrderNumber;
    public $OrderId;
    public $MerchantData;
    public $MerchantReferenceNumber;
    public $TransactionId;
    public $StatusCode;
    public $AuthorizationCode;
    public $ProcessorOrderNumber;
    public $ProcessorTransactionId;

    public $AVSResult;
    public $CardCodeResult;
    public $CardNumber;
    public $CardExpMonth;
    public $CardExpYear;
    public $CardCode;
}
