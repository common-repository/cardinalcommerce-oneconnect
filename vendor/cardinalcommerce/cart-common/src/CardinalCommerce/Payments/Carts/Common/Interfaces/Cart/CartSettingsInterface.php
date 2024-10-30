<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Cart;

/**
 * Common settings configured by the shopping cart.
 */
interface CartSettingsInterface {
    public function getEnvironmentKey();
    public function getAPICredentials();
    public function getCentinelCredentials();
    public function getSongbirdLoggingEnabled();
    public function getPaymentAuthType();
}