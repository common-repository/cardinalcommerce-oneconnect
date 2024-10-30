<?php
// No namespace, this is a class instanced by WooCommerce

if ( !defined( 'ABSPATH' )) {
    exit;
}

require( __DIR__ . '/../abstracts/abstract-wc-gateway-cardinalpm.php' );

/**
* CardinalCommerce Processor Module
*
* Provides a WooCommerce extension for Cardinal Processor Module with CardinalCruise.
*
* @class       WC_Gateway_CardinalPM
* @extends     WC_Payment_Gateway
* @version     2.0
* @package     CardinalCommerce/Carts/WooCommerce/Payment
* @author      CardinalCommerce
*/

/**
* WC_Gateway_CardinalPM_Common Class.
*/
class WC_Gateway_CardinalPM_Common extends \WC_Gateway_CardinalPM {
    const PAYMENT_METHOD_ID = 'cardinalpm';

    const DEFAULT_TITLE = 'Credit Card';
    const DEFAULT_DESCR = 'Credit Card payment with optional Cardinal Consumer Authentication (CCA)';

    const DEFAULT_METHOD_TITLE = 'CardinalCommerce OneConnect';
    const DEFAULT_METHOD_DESCR = 'Process payments with CardinalCommerce with optional Cardinal Consumer Authentication (CCA)';
}
