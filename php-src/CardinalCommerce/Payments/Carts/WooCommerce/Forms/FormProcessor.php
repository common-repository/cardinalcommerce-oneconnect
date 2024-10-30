<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Forms;

use Psr\Log\LoggerInterface;

class FormProcessor {

    /**
     * @var string
     */
    private $_paymentMethodPrefix;

    public function __construct( $paymentMethodPrefix ) {
        $this->_paymentMethodPrefix = $paymentMethodPrefix;
    }

    /**
     * @return FormValues
     */
    public function processPostData( $postData ) {
        return new FormValues(
            $this->_paymentMethodPrefix,
            $postData
        );
    }

}