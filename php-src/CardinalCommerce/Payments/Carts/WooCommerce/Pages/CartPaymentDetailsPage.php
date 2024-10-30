<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Pages;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages\CartPaymentDetailsPageInterface;

const FORM_SEL = "form[name=checkout]";
const INPUT_PREFIX = "#cardinalpm-";

class CartPaymentDetailsPage implements CartPaymentDetailsPageInterface {
    public function getFormSelector() {
        return FORM_SEL;
    }

    public function getSubmitButtonSelector() {
        return "input#place_order[type=submit]";
    }

    public function getCardNumberSelector() {
        return INPUT_PREFIX . "card-number";
    }

    public function getCardExpSelector() {
        return INPUT_PREFIX . "card-expiry";
    }

    public function getCardExpDelimiter() {
        return '/';
    }

    public function getCardExpMonthSelector() {
        return null;
    }

    public function getCardExpYearSelector() {
        return null;
    }

    public function getCardCVVSelector() {
        return INPUT_PREFIX . "card-cvc";
    }

    public function getFormattedTotalAmountSelector() {
        return '#order_review .cart-subtotal .amount';
    }

    public function getServerJWTHiddenInputName() {
        return "CardinalCruise:ServerJWT";
    }

    public function getResponseJWTHiddenInputName() {
        return "CardinalCruise:ResponseJWT";
    }
}