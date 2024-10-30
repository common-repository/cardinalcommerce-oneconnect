<?php
namespace CardinalCommerce\Client\Centinel\Exceptions;

class InvalidXMLResponseException extends \Exception {

    public function __construct() {
        parent::__construct("The XML response message is not well-formed or does not begin with the expected root element.");
    }

    public function __toString() {
        return "The XML response message is not well-formed or does not begin with the expected root element.";
    }
}