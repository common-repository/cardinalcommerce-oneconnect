<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Forms;

use Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

class FormValues {
    private $_logger;
    private $_paymentDetailsPage;
    private $_paymentMethodPrefix;
    private $_postData;

    public function __construct(
        $paymentMethodPrefix,
        $postData
    ) {
        $this->_paymentMethodPrefix = $paymentMethodPrefix;
        $this->_postData = $postData;
    }

    private function getFieldValue( $key, $required = false ) {
        $logger = \wc_gateway_cardinalpm()->objects()->logger();

        $logger->info( '[WCFormProcessor::getFieldValue] key: ' . $key );
        if ( !isset( $this->_postData[$key] ) ) {
            if ( $required ) {
                throw new Error( sprintf('Missing required field [%s]', $key) );
            }
        }

        $value = $this->_postData[$key];
        $logger->info( '[WCFormProcessor::getFieldValue] value: ' . json_encode($value) );
        return $value;
    }

    private function getPaymentMethodFieldValue( $key, $required = false ) {
        $postKey = sprintf( "%s-%s", $this->_paymentMethodPrefix, $key );
        return $this->getFieldValue( $postKey, $required );
    }

    public function getAddressData( $prefix ) {
        $firstName = $this->getFieldValue( $prefix . '_first_name');
        $lastName =  $this->getFieldValue( $prefix . '_last_name');
        $address1 = $this->getFieldValue( $prefix . '_address_1');
        $address2 = $this->getFieldValue( $prefix . '_address_2');
        $phone1 = $this->getFieldValue( $prefix . '_phone' );
        $city = $this->getFieldValue( $prefix . '_city');
        $state = $this->getFieldValue( $prefix . '_state');
        $postalCode = $this->getFieldValue( $prefix . '_postcode');
        $countryCode = $this->getFieldValue( $prefix . '_country');

        return (object) array(
            'FirstName' => $firstName,
            'MiddleName' => '',
            'LastName' => $lastName,
            'Address1' => $address1,
            'Address2' => $address2,
            'Address3' => '',
            'Phone1' => $phone1,
            'Phone2' => '',
            'City' => $city,
            'State' => $state,
            'PostalCode' => $postalCode,
            'CountryCode' => $countryCode
        );
    }

    private function getCardExpirationDate() {
        $cardExp = $this->getPaymentMethodFieldValue('card-expiry');

        $cardExpParts = explode('/', $cardExp);
        $cardExpMonth = intval($cardExpParts[0]);
        $cardExpYear = intval($cardExpParts[1]);

        $cardExpYear = $cardExpYear < 2000 ? (2000 + $cardExpYear) : $cardExpYear;

        return (object) array(
            'CardExpMonth' => $cardExpMonth,
            'CardExpYear' => $cardExpYear
        );
    }

    /**
     *
     * @return PaymentObjects\Account
     */
    public function getCompleteAccountObject() {
        $address = $this->getAddressData( 'billing' );
        $cardName = sprintf( "%s %s", $address->FirstName, $address->LastName );
        $cardNumber = preg_replace('/[^0-9]/', '', $this->getPaymentMethodFieldValue('card-number') );
        $cardCode = preg_replace('/[^0-9]/', '', $this->getPaymentMethodFieldValue('card-cvc') );

        $expDate = $this->getCardExpirationDate();

        return new PaymentObjects\Account((object) array(
            'AccountNumber' => $cardNumber,
            'ExpirationMonth' => $expDate->CardExpMonth,
            'ExpirationYear' => $expDate->CardExpYear,
            'CardCode' => $cardCode,
            'NameOnAccount' => $cardName
        ));
    }
    
    /**
     * @param $includeCardDetails bool Whether to include complete account
     *        details including card number and CVV.
     *
     * @return PaymentObjects\Consumer
     */
    public function getConsumerObject(
        $includeCardDetails = false
    ) {
        $accountObject = null;

        if ( $includeCardDetails ) {
            $accountObject = $this->getCompleteAccountObject();
        }

        $billingAddress = $this->getAddressData( 'billing' );
        $email1 = $this->getFieldValue( 'billing_email' );

        return new PaymentObjects\Consumer((object) array(
            'Email1' => $email1,
            'Email2' => '',
            'ShippingAddress' => null,
            'BillingAddress' => $billingAddress,
            'Account' => $accountObject,
        ));
    }

    public function getResponseJWT() {
        $page = \wc_gateway_cardinalpm()->objects()->common_objects()->getPaymentDetailsPage();
        $fieldName = $page->getResponseJWTHiddenInputName();

        return $this->getFieldValue( $fieldName );
    }
}