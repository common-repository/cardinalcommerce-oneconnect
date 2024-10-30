<?php
namespace CardinalCommerce\Payments\Interfaces;

interface CentinelClientInterface {
    public function sendMessage( $obj );
}