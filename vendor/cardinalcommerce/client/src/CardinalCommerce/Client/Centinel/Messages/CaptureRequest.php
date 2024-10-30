<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class CaptureRequest {
    const MSG_TYPE = 'cmpi_capture';
    const MSG_VERSION = '1.7';

    public function __construct() {
        $this->MsgType = self::MSG_TYPE;
        $this->Version = self::MSG_VERSION;
    }

    public $MsgType;
    public $Version;

    public $ProcessorId;
    public $MerchantId;
    public $MerchantData;

    public $TransactionPwd;
    public $TransactionType;

    public $OrderId;
    public $AuthorizationCode;
    public $Amount;
    public $CurrencyCode;
    public $Description;
}    
