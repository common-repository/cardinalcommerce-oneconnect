<?php
namespace CardinalCommerce\Payments\Carts\Common;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart\CartSettingsInterface;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Cardinal\CardinalPaymentContextInterface;

// NEXTREV: Eliminate
class PaymentContext implements CardinalPaymentContextInterface {
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