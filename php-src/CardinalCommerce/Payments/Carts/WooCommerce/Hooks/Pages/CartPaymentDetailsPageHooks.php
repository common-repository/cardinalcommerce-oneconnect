<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Hooks\Pages;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\Processors;
use \CardinalCommerce\Payments\Carts\WooCommerce\Processors as CartProcessors;

class CartPaymentDetailsPageHooks {
    private $_logger;
    private $_settings;
    private $_checkoutProcessor;
    private $_scriptsDir;

    const SONGBIRD_SCRIPT_NAME = 'SongbirdJS';

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        CartProcessors\CheckoutProcessor $checkoutProcessor,
        $scriptsDir
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
        $this->_checkoutProcessor = $checkoutProcessor;
        $this->_scriptsDir = $scriptsDir;
    }

    public function payment_scripts() {
        $logger = $this->_logger;

        $isCheckout = \is_checkout();

        $logger->info('[CardPaymentDetailsPageHooks::payment_scripts] isCheckout: ' . json_encode($isCheckout) );
        
        if ( ! $isCheckout ) {
            $logger->info('[CardPaymentDetailsPageHooks::payment_scripts] not enqueuing scripts on this page' );
            return;
        }

        $logger->info('[CardPaymentDetailsPageHooks::payment_scripts] enqueuing scripts and init block' );

        // Enqueue checkout scripts for Songbird
        wp_enqueue_script( self::SONGBIRD_SCRIPT_NAME );
        wp_enqueue_script( 'CardinalCruiseJQueryPlugin' );
        wp_enqueue_script( 'CardinalCruiseWooCommerceJQueryPlugin' );

        $logger->info('[CardPaymentDetailsPageHooks::payment_scripts] addding wp_footer action for init calls' );

        // Render init script
        // (must be > 50 to render after enqueued scripts)
        // see https://robjscott.com/add-inline-scripts-and-conditionally-enqueue-scripts-styles-in-wordpress/
        add_action( 'wp_footer', array( $this, 'render_songbird_init_calls' ), 50 );
    }

    public function render_songbird_init_calls() {
        $logger = $this->_logger;
        $checkoutProcessor = $this->_checkoutProcessor;

        $isCheckout = \is_checkout();

        $logger->info('[CardPaymentDetailsPageHooks::render_songbird_init_calls] isCheckout: ' . json_encode($isCheckout) );
        
        if ( ! $isCheckout ) {
            $logger->info('[CardPaymentDetailsPageHooks::render_songbird_init_calls] not rendering init call' );
            return;
        }

        $logger->info('[CardPaymentDetailsPageHooks::render_songbird_init_calls]' );
        $checkoutProcessor->renderScriptBlock();
    }

    protected function registerScripts() {
        $logger = $this->_logger;
        $cartSettings = $this->_settings;

        $songbirdJsUrl = \wc_gateway_cardinalpm()->objects()->cart_integration()
            ->getEnvironmentConfig()->getSongbirdConfig()->getSongbirdJsUrl();

        $logger->info('[CardPaymentDetailsPageHooks::registerScripts] Registering Songbird script [{script}]',
            array( 'script' => $songbirdJsUrl ));
        wp_register_script( self::SONGBIRD_SCRIPT_NAME, $songbirdJsUrl );

        $cardinalcruisejquery_script_url = sprintf( "%s/jquery.cc.songbird.js", $this->_scriptsDir );
        $cardinalcruisewoocommercejquery_script_url = sprintf( "%s/jquery.cc.songbird.woocommerce.js", $this->_scriptsDir );

        wp_register_script( 'CardinalCruiseJQueryPlugin', $cardinalcruisejquery_script_url );
        wp_register_script( 'CardinalCruiseWooCommerceJQueryPlugin', $cardinalcruisewoocommercejquery_script_url );

    }

    public function setup() {
        $this->registerScripts();

        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
    }
}