<?php
namespace CardinalCommerce\Payments\Credentials;

use \CardinalCommerce\Payments\Interfaces\APICredentialsInterface;

class APICredentials implements APICredentialsInterface {
    private $_apiIdentifier;
    private $_orgUnitId;
    private $_apiKey;

    public function __construct($apiIdentifier, $orgUnitId, $apiKey) {
        $this->_apiIdentifier = $apiIdentifier;
        $this->_orgUnitId = $orgUnitId;
        $this->_apiKey = $apiKey;
    }

    public function getApiIdentifier() {
        return $this->_apiIdentifier;
    }

    public function getOrgUnitId() {
        return $this->_orgUnitId;
    }

    public function getApiKey() {
        return $this->_apiKey;
    }

    public function __toString() {
        return json_encode((object) array(
            'apiIdentifier' => $this->_apiIdentifier,
            'orgUnitId' => $this->_orgUnitId,
            'apiKey' => $this->_apiKey
        ));
    }
}