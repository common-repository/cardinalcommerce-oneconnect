<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class SaleResponse {

    public $ErrorNo;
    public $ErrorDesc;

    public $ReasonCode;
    public $ReasonDesc;

    public $StatusCode;
    public $OrderId;
    public $TransactionId;
    public $ProcessorOrderNumber;
    public $MerchantData;
    public $AuthorizationCode;

    public $AVSResult;
    public $CardCodeResult;
}
