<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class AuthenticationRequest {
    const MSG_TYPE = 'cmpi_authenticate';
    const MSG_VERSION = '1.7';

    public function __construct() {
        $this->MsgType = self::MSG_TYPE;
        $this->Version = self::MSG_VERSION;
    }

    public $MsgType;
    public $Version;

    public $ProcessorId;
    public $MerchantId;

    public $TransactionType;
    public $TransactionPwd;
    public $TransactionId;

    public $PAResPayload;
}
