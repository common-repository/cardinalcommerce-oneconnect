<?php

/**
 * Plugin Name:  3-D Secure Payment Gateway by CardinalCommerce
 * Plugin URI:   https://developer.cardinalcommerce.com/
 * Description:  Module for processing payments with CardinalCommerce and optional Cardinal Consumer Authentication (CCA)
 * Version:      1.2.8
 * Author:       CardinalCommerce
 * Author URI:   https://cardinalcommerce.com/
 * License:      Proprietary
 * License URI:  data:Proprietary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CARDINAL_ONECONNECT_VERSION', '1.2.8' );
define( 'CARDINAL_ONECONNECT_PLUGIN_FILE', __FILE__ );

function Cardinal_OneConnect_add_gateway( $methods ) {
    require_once plugin_dir_path(CARDINAL_ONECONNECT_PLUGIN_FILE) .
        'Gateway.php';
    $methods[] = 'WC_Payment_Gateway_Cardinal_OneConnect';
    return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'Cardinal_OneConnect_add_gateway' );

function Cardinal_OneConnect_order_status_changed(
        $order_id, $from_status, $to_status) {
    require_once plugin_dir_path(CARDINAL_ONECONNECT_PLUGIN_FILE) .
        'Gateway.php';
    $gateway = new WC_Payment_Gateway_Cardinal_OneConnect();
    return $gateway->order_status_changed($order_id, $from_status, $to_status);
}

add_action( 'woocommerce_order_status_changed',
            'Cardinal_OneConnect_order_status_changed', 10, 3 );
