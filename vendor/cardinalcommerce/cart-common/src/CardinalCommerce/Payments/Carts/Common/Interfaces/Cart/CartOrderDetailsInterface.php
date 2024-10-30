<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Cart;

interface CartOrderDetailsInterface {
    function getOrderKey();
    function getOrderDescription();
    function getOrderCurrencyForMidas();
    function getOrderNumericCurrencyForMidas();
    function getOrderAmountForMidas();
}