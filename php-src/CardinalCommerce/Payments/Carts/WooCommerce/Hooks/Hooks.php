<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Hooks;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\WooCommerce\Hooks\Admin as AdminHooks;
use \CardinalCommerce\Payments\Carts\WooCommerce\Hooks\Checkout as CheckoutHooks;
use \CardinalCommerce\Payments\Carts\WooCommerce\Hooks\Gateway\GatewayHooks;
use \CardinalCommerce\Payments\Carts\WooCommerce\Hooks\OrderStatus as OrderStatusHooks;
use \CardinalCommerce\Payments\Carts\WooCommerce\Hooks\Pages as PageHooks;

use \CardinalCommerce\Payments\Carts\WooCommerce\Processors as CartProcessors;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;

class Hooks {

    private $_logger;
    private $_settings;
    private $_paymentMethods;
    private $_checkoutProcessor;
    private $_gateway_class;
    private $_scriptsDir;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        CommonPaymentMethodsInterface $paymentMethods,
        CartProcessors\CheckoutProcessor $checkoutProcessor,
        $gateway_class,
        $scriptsDir
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
        $this->_paymentMethods = $paymentMethods;
        $this->_checkoutProcessor = $checkoutProcessor;
        $this->_gateway_class = $gateway_class;
        $this->_scriptsDir = $scriptsDir;
    }

    protected function setupAdminHooks() {
        $adminOrderHooks = new AdminHooks\AdminOrderHooks(
            $this->_logger,
            $this->_settings,
            $this->_scriptsDir
        );
        $adminOrderHooks->setup();
    }

    protected function setupMainHooks() {
        $paymentDetailsPageHooks = new PageHooks\CartPaymentDetailsPageHooks(
            $this->_logger,
            $this->_settings,
            $this->_checkoutProcessor,
            $this->_scriptsDir);
        $paymentDetailsPageHooks->setup();

        $orderHooks = new CheckoutHooks\CheckoutOrderHooks(
            $this->_logger,
            $this->_settings,
            $this->_paymentMethods,
            $this->_checkoutProcessor
        );
        $orderHooks->setup();

        $orderStatusHooks = new OrderStatusHooks\OrderStatusHooks(
            $this->_logger,
            $this->_settings,
            $this->_checkoutProcessor
        );
        $orderStatusHooks->setup();
    }

    public function setup() {
        $gatewayHooks = new GatewayHooks(
            $this->_logger,
            $this->_settings,
            $this->_gateway_class);
        $gatewayHooks->setup();

        if ( \is_admin() ) {
            $this->setupAdminHooks();
        } else {
            $this->setupMainHooks();
        }
    }
}