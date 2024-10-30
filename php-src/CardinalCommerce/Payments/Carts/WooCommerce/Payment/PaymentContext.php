<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Payment;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart\CartSettingsInterface;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\PaymentContextInterface;

class PaymentContext implements PaymentContextInterface {
    private $_settings;

    public function __construct(
        CartSettingsInterface $cartSettings
    ) {
        $this->_settings = $cartSettings;
    }

    public function getCartSettings() {
        return $this->_settings;
    }
}