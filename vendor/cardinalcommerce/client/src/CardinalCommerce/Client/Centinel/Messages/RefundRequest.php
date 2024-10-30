<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class RefundRequest {
    const MSG_TYPE = 'cmpi_refund';
    const MSG_VERSION = '1.7';

    public function __construct($data = null) {
        $this->MsgType = self::MSG_TYPE;
        $this->Version = self::MSG_VERSION;

        $this->Reason = self::REFUND_REASON_OTHER;

        if ( $data != null ) {
            if ( property_exists( $data, 'Reason' ) && $data->Reason != null ) {
                $this->Reason = $data->Reason;
            }
        }
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

    const REFUND_REASON_OTHER = 'Default';
    const REFUND_REASON_NOINVENTORY = 'NoInventory';
    const REFUND_REASON_CUSTOMER_RETURN = 'CustomerReturn';
    const REFUND_REASON_GENERAL_ADJUSTMENT = 'GeneralAdjustment';
    const REFUND_REASON_COULDNOTSHIP = 'CouldNotShip';
    const REFUND_REASON_CUSTOMERCANCEL = 'CustomerCancel';
    const REFUND_REASON_PRODUCTOUTOFSTOCK = 'ProductOutofStock'; // TODO: Verify letter case
    const REFUND_REASON_EXCHANGE = 'Exchange';

    public $Reason;
}    
