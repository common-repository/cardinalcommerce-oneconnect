<?php
namespace CardinalCommerce\Client\Centinel\Messages;

class LookupRequest {
    const MSG_TYPE = 'cmpi_lookup';
    const MSG_VERSION = '1.7';

    public function __construct() {
        $this->MsgType = self::MSG_TYPE;
        $this->Version = self::MSG_VERSION;
    }

    public $MsgType;
    public $Version;
    public $ProcessorId;
    public $MerchantId;

    public $TransactionPwd;
    public $TransactionType;

    public $OrderNumber;
    public $Amount;
    public $CurrencyCode;

    public $CardNumber;
    public $CardExpMonth;
    public $CardExpYear;
    public $CardCode;

    public $OrderDescription;

    public $UserAgent;
    public $BrowserHeader;
    public $EMail;

    public $TransactionMode;
    public $IPAddress;
    public $Recurring;

    public $RecurringFrequency;
    public $RecurringEnd;
    public $Installment;

    public $AcquirerPassword;
    public $MerchantData;
    public $MerchantReferenceNumber;

    public $ProductCode;
    public $ShippingAmount;
    public $TaxAmount;

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

    public $Items;

    public function itemsXml() {
        return "";
    }

    public $CardType;
    public $OverridePaymentMethod;
    public $MobilePhone;
    public $CategoryCode;
    public $JavaScriptSupported;
}
