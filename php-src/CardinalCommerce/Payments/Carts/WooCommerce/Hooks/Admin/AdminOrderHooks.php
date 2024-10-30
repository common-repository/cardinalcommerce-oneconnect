<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Hooks\Admin;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\CommonObjects;

class AdminOrderHooks {
    private $_logger;
    private $_settings;
    private $_scriptsUrl;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $settings,
        $scriptsUrl
    ) {
        $this->_logger = $logger;
        $this->_settings = $settings;
        $this->_scriptsUrl = $scriptsUrl;
    }

    // Order list actions

    public function orders_list_order_add_action_button_void( $actions, $order ) {
        if ( ! $order->has_status( array( 'completed') ) ) {
            return $actions;
        }

        $actions['void'] = array(
            'url' => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=voided&order_id=' . $order->id ), 'woocommerce-mark-order-status' ),
            'name'      => __( 'Void', 'woocommerce' ),
            'action'    => "view void", // setting "view" for proper button CSS
        );

        return $actions;
    }

    public function admin_head_actions_buttons_css() {
        echo '<style>.view.void::after { content: \"e013" !important; }</style>';
    }

    // Order details extra information


    protected function output_order_metadata( $order ) {
        $centinelConfig = CommonObjects::instance()->getCartIntegration()->getEnvironmentConfig()->getCentinelConfig();

        ?>
        <p class="form-field form-field-wide">
            <label>Order Number:</label>
            <span style="padding: 24px; padding-left: 0;">
                <?php echo \get_post_meta( $order->get_id(), '_CARDINAL_PROCESSOR_MODULE_RESULT_ORDER_NUMBER', true ); ?>
            </span>
        </p>
        <p class="form-field form-field-wide">
            <label>Processor Order ID:</label>
            <span style="padding: 24px; padding-left: 0;">
                <?php echo \get_post_meta( $order->get_id(), '_CARDINAL_COMMON_PROCESSOR_ORDER_ID', true ); ?>
            </span>
        </p>
        <p class="form-field form-field-wide">
            <label>Transaction ID:</label>
            <span style="padding: 24px; padding-left: 0;">
                <?php echo \get_post_meta( $order->get_id(), '_CARDINAL_PROCESSOR_MODULE_RESULT_TRANSACTION_ID', true ); ?>
            </span>
        </p>
        <p class="form-field form-field-wide">
            <label>Authorization Code:</label>
            <span style="padding: 24px; padding-left: 0;">
                <?php echo \get_post_meta( $order->get_id(), '_CARDINAL_PROCESSOR_MODULE_RESULT_AUTHORIZATION_CODE', true ); ?>
            </span>
        </p>
        <p class="form-field form-field-wide">
            <label>AVS Result:</label>
            <span style="padding: 24px; padding-left: 0;">
                <?php echo \get_post_meta( $order->get_id(), '_CARDINAL_PROCESSOR_MODULE_RESULT_AVSRESULT', true ); ?>
            </span>
        </p>
        <p class="form-field form-field-wide">
            <label>Card Code Result:</label>
            <span style="padding: 24px; padding-left: 0;">
                <?php echo \get_post_meta( $order->get_id(), '_CARDINAL_PROCESSOR_MODULE_RESULT_CARD_CODE_RESULT', true ); ?>
            </span>
        </p>
        <p class="form-field form-field-wide">
            <span style="padding: 24px; padding-left: 0;">
                <a href="<?php echo $centinelConfig->getMerchantConfigUrl(); ?>" target="_blank">Merchant Admin</a>&nbsp;
                <a href="<?php echo $centinelConfig->getMerchantReportsUrl(); ?>" target="_blank">Merchant Reporting</a>
            </span>
        </p>
        <?php
    }

    public function order_payment_method_data( $order ) {
        $this->output_order_metadata( $order );
    }

    // Order details page `Order Actions` meta box

    public function order_actions_metabox_add_action_void( $actions ) {
        global $theorder;

        if ( !  $theorder->has_status( array( 'completed') ) ) {
            return $actions;
        }

        $actions['wc_order_action_void'] = __( 'Void transaction', 'wc-cardinalprocessormodule' );

        return $actions;
    }

    // Order details page `Cardinal` meta box

    public function order_details_cardinal_meta_box( $order ) {
        $this->output_order_metadata( $order );
    }

    public function order_details_add_cardinal_meta_box() {
        global $order;

        \add_meta_box( "cardinalpm-order-data-meta-box", __( 'Cardinal Transaction', 'wc-cardinalprocessormodule' ), array( $this, order_details_cardinal_meta_box ), "shop_order", "side", "low", array( $order ) );
    }

    // Order details actions

    public function order_add_action_button_void( $order ) {
        if ( ! $order->has_status( array( 'completed') ) ) {
            return;
        }

        ?>
            <button type="button" class="button void-transaction"><?php _e( 'Void Transaction', 'woocommerce' ); ?></button>
        <?php
    }

    // Extra admin script

    public function admin_scripts( $hook_suffix ) {
        $admin_script_url = wc_gateway_cardinalpm()->assets_url() . "/js/admin/woocommerce.cardinalcruise.admin.js";
        $this->_logger->info( '[AdminOrderHooks::admin_scripts] enqueuing script WooCommerceCardinalCruiseAdmin at ' . $admin_script_url );
        \wp_enqueue_script( 'WooCommerceCardinalCruiseAdmin', $admin_script_url );
    }

    // Process 'void' action

    public function process_void_action( $order ) {
        $logger = $this->_logger;

        $logger->info( '[AdminOrderHooks::process_void_action] order: ' . var_export( $order, true ));
        // Set status and trigger void action
        $order->set_status( 'wc-voided' );
    }

    public function setup() {
        $logger = $this->_logger;

        // Orders list actions
        \add_action( 'admin_head', array( $this, 'admin_head_actions_buttons_css' ) );
        \add_filter( 'woocommerce_admin_order_actions', array( $this, 'orders_list_order_add_action_button_void' ), PHP_INT_MAX, 2 );

        // Order details extra information
        \add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'order_payment_method_data' ) );

        // Order details page `Order Actions` meta box
        \add_action( 'woocommerce_order_actions', array( $this, 'order_actions_metabox_add_action_void' ), 10, 1 );

        // Order details page `Cardinal` meta box
        \add_action( 'add_meta_boxes', array( $this, 'order_details_add_cardinal_meta_box' ), 10 );

        // Order details actions
        \add_action( 'woocommerce_order_item_add_action_buttons', array( $this, 'order_add_action_button_void' ), 10, 1 );

        // Extra admin script
        //\add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        // Process void action
        \add_action( 'woocommerce_order_action_void_order', array( $this, 'process_void_action' ), 10, 1 );
    }

}