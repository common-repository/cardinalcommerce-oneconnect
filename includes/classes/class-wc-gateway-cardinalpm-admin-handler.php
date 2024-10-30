<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Gateway_CardinalPM_Admin_Handler {

    public function __construct() {
        $this->_register_custom_statuses();

        // Custom statuses filter
        add_filter( 'wc_order_statuses', array( $this, 'order_statuses' ), 10, 1 );

        // Actions from admin page
        add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed'), 10, 3 );

        //add_action( 'wp_ajax_woocommerce_mark_order_status' , array( $this, 'wp_ajax_woocommerce_mark_order_status') );

        // Void action
        //add_action( 'woocommerce_order_action_void_order', array( $this, 'process_void_action' ), 10, 1 );
        add_action( 'wp_ajax_woocommerce_void_order', array( $this, 'process_void_order_action' ) );

        // Admin script
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 50, 1 );
    }

    public function order_statuses( $statuses ) {
        $statuses[ 'wc-voided' ] = 'Voided';

        return $statuses;
    }

    protected function _register_custom_statuses() {
        register_post_status( 'wc-voided', array(
            'label'		=> 'Transaction Voided.',
            'public'	=> true,
            'show_in_admin_status_list' => true,
            'label_count'	=> \_n_noop( 'Transaction Voided <span class="count">(%s)</span>', 'Transaction voided <span class="count">(%s)</span>' )
    	));
    }

    public function order_status_changed( $order_id, $from_status, $to_status ) {
        error_log('[WC_Gateway_CardinalPM_Admin_Handler:order_status_changed] order_id: ' .json_encode( $order_id ) );
        error_log('[WC_Gateway_CardinalPM_Admin_Handler:order_status_changed] from_status: ' .json_encode( $from_status ) );
        error_log('[WC_Gateway_CardinalPM_Admin_Handler:order_status_changed] to_status: ' .json_encode( $to_status ) );

        if ( 'completed' === $to_status ) {
            error_log('[WC_Gateway_CardinalPM_Admin_Handler:order_status_changed] status changed to completed, triggering capture.' );

            wc_gateway_cardinalpm()->objects()->order_management()
                ->capture_payment( $order_id );

        }
    }

    public function process_void_order_action() {
        error_log( '[WC_Gateway_CardinalPM_Admin_Handler::process_void_action] in handler' );
        ob_start();

        //check_ajax_referer( 'order-item', 'security' );

        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            wp_die( -1 );
        }

        $order_id = absint( $_POST['order_id'] );
        error_log( '[WC_Gateway_CardinalPM_Admin_Handler::process_void_action] order_id: ' . var_export( $order, true ));

        try {
            $success = wc_gateway_cardinalpm()->objects()->order_management()
                ->void_order( $order_id );

            if ( $success ) {
                wp_send_json_success( array( 'status' => 'voided' ) );
            } else {
                wp_send_json_error( array( 'error' => 'Failed to void transaction' ) );
            }
        } catch ( Exception $e ) {
            //
            wp_send_json_error( array( 'error' => $e->getMessage() ) );
        }
    }

    public function admin_scripts( $hook_suffix ) {
        error_log( '[WC_Gateway_CardinalPM_Admin_Handler::admin_scripts] hook_suffix: ' . json_encode($hook_suffix) );
        error_log( '[WC_Gateway_CardinalPM_Admin_Handler::admin_scripts] GET: ' . json_encode($_GET) );

        if ( 'post.php' == $hook_suffix && 'edit' == $_GET['action'] ) {
            $admin_script_url = \wc_gateway_cardinalpm()->assets_url() . "/js/admin/woocommerce.cardinalcruise.admin.js";

            error_log( '[WC_Gateway_CardinalPM_Admin_Handler::admin_scripts] enqueuing script WooCommerceCardinalCruiseAdmin at ' . $admin_script_url );
            wp_enqueue_script( 'WooCommerceCardinalCruiseAdmin', $admin_script_url );
        }

    }

}