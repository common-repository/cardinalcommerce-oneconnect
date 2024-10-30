<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Pages;

/**
 * Interface with front-end CSS selectors for cart payment details page
 */
interface CartPaymentDetailsPageInterface {
    function getFormSelector();
    function getSubmitButtonSelector();

    function getCardNumberSelector();

    function getCardExpSelector();
    function getCardExpDelimiter();

    function getCardExpMonthSelector();
    function getCardExpYearSelector();
    function getCardCVVSelector();

    function getFormattedTotalAmountSelector();
    
    function getServerJWTHiddenInputName();
    function getResponseJWTHiddenInputName();
}