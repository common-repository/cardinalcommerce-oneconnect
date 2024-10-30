<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Order;

use CardinalCommerce\Payments\Carts\Common\Interfaces\Cart\CartOrderDetailsInterface;

/**
 * Implementation of CardinalCommerce\Interfaces\Cart\CartOrderDetailsInterface for WooCommerce
 */
class CartOrderDetails implements CartOrderDetailsInterface {
    private $_orderKey;
    private $_orderDescription;
    private $_orderCurrencyForMidas;
    private $_orderNumericCurrencyForMidas;
    private $_orderAmountForMidas;

    /**
     * Create a CartOrderDetailsInterface instance from a WooCommerce \WC_Cart object.
     *
     */
    public static function fromWCCart( \WC_Cart $wc_cart) {

        $total = (float) $wc_cart->total;

        //NEXTREV: WooCommerce 3.0+
        //$total = $wc_cart->get_cart_total();

        /*
        $total = 0;
        if ( 0 < $cart->total ) {
            $total = (float) $cart->total;
        }
        */

        return new self((object) array(
            'orderKey' => '1',
            'orderDescription' => '',
            'orderCurrencyForMidas' => 'USD',
            'orderNumericCurrencyForMidas' => '840',
            'orderAmountForMidas' => $total * 100
        ));
    }

    /**
     * Create a CartOrderDetailsInterface instance from a WooCommerce \WC_Order object.
     *
     */
    public static function fromWCOrder( \WC_Order $wc_order) {
        $items = $wc_order->get_items();
        $item_descriptions = array();

        foreach( $items as $item ) {
            array_push($item_descriptions, sprintf( "Item: %s", $item['name'] ));
        }

        //$formattedTotal = $wc_order->get_formatted_order_total();
        //$total = floatval( preg_replace('/[^0-9.]/', '', $formattedTotal) );

        $total = $wc_order->get_total();

        $details = new \stdClass;
        $details->orderKey = $wc_order->get_order_number();
        $details->orderDescription = join( "\r\n", $item_descriptions );
        $details->orderCurrencyForMidas = 'USD';
        $details->orderNumericCurrencyForMidas = '840';
        $details->orderAmountForMidas = $total * 100;

        return new self( $details );
    }

    public function __construct($details) {
        $this->_orderKey = $details->orderKey;
        $this->_orderDescription = $details->orderDescription;
        $this->_orderCurrencyForMidas = $details->orderCurrencyForMidas;
        $this->_orderNumericCurrencyForMidas = $details->orderNumericCurrencyForMidas;
        $this->_orderAmountForMidas = $details->orderAmountForMidas;
    }

    public function getOrderKey() {
        return $this->_orderKey;
    }

    public function getOrderDescription() {
        return $this->_orderDescription;
    }

    public function getOrderCurrencyForMidas() {
        return $this->_orderCurrencyForMidas;
    }

    public function getOrderNumericCurrencyForMidas() {
        return $this->_orderNumericCurrencyForMidas;
    }

    public function getOrderAmountForMidas() {
        return $this->_orderAmountForMidas;
    }

    public function __toString() {
        return json_encode((object) array(
            'orderKey' => $this->_orderKey,
            'orderDescription' => $this->_orderDescription,
            'orderCurrencyForMidas' => $this->_orderCurrencyForMidas,
            'orderNumericCurrencyForMidas' => $this->_orderNumericCurrencyForMidas,
            'orderAmountForMidas' => $this->_orderAmountForMidas
        ));
    }
}