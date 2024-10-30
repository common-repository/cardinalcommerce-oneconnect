<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class AuthorizationRequest {
    const MSG_TYPE = 'cmpi_authorize';
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
    public $MerchantReferenceNumber;

    public $TransactionType;
    public $TransactionPwd;
    public $TransactionId;

    public $Cavv;
    public $Eci;
    public $Xid;

    public $OrderId;
    public $Amount;
    public $OrderNumber;
    public $CurrencyCode;
    public $Description;

    public $CardNumber;
    public $CardCode;
    public $CardExpMonth;
    public $CardExpYear;

    public $IPAddress;
    public $EMail;

    public $BillingFirstName;
    public $BillingMiddleName;
    public $BillingLastName;
    public $BillingAddress1;
    public $BillingAddress2;
    public $BillingPhone;
    public $BillingAltPhone;
    public $BillingCity;
    public $BillingState;
    public $BillingPostalCode;
    public $BillingCountryCode;

    public $ShippingFirstName;
    public $ShippingMiddleName;
    public $ShippingLastName;
    public $ShippingAddress1;
    public $ShippingAddress2;
    public $ShippingPhone;
    public $ShippingAltPhone;
    public $ShippingCity;
    public $ShippingState;
    public $ShippingPostalCode;
    public $ShippingCountryCode;

    public $MerchantProcessorAlias;
}
