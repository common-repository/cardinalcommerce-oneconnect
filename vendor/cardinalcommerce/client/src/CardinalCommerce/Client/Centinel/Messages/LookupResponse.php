<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class LookupResponse {

    public $ErrorNo;
    public $ErrorDesc;

    public $ReasonCode;
    public $ReasonDesc;

    public $TransactionType;

    public $TransactionId;
    public $OrderId;
    public $Enrolled;
    public $ACSUrl;
    public $Payload;
    public $EciFlag;
    public $OrderNumber;

    public $MerchantData;
    public $MerchantReferenceNumber;
    public $StatusCode;
    public $AuthorizationCode;

    public $ProcessorOrderNumber;
    public $ProcessorTransactionId;

    public $AVSResult;
    public $CardCodeResult;
}
