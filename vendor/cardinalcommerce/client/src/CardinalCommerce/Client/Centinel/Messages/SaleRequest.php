<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class SaleRequest {
    const MSG_TYPE = 'cmpi_sale';
    const MSG_VERSION = '1.7';

    public function __construct() {
        $this->MsgType = self::MSG_TYPE;
        $this->Version = self::MSG_VERSION;
    }

    public $ProcessorId;
    public $MerchantId;
    public $MerchantData;
    public $MerchantReferenceNumber;

    public $TransactionPwd;
    public $TransactionType;

    public $OrderId;
    public $Amount;
    public $CurrencyCode;
    public $Description;

    public $CardNumber;
    public $CardCode;
    public $CardExpMonth;
    public $CardExpYear;

    public $MerchantProcessorAlias;
}    
