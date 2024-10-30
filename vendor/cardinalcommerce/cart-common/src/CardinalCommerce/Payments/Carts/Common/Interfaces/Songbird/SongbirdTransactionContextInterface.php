<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird;

/**
 * The transaction-specific Songbird context.
 */
 interface SongbirdTransactionContextInterface {

    /**
     * Get the ServerJWT with orderDetails for the current transaction.
     * @returns string
     */
    public function getServerJWT();

    /**
     * Parse the ResponseJWT
     *
     * @param string $responseJWT The ResponseJWT from Midas and Songbird
     * @returns object The payload
     */
    public function parseResponseJWT( $responseJWT );

    /**
     * Render hidden inputs whose values are provided by the server.
     * @returns string
     */
    public function renderServerProvidedHiddenInputs();

}