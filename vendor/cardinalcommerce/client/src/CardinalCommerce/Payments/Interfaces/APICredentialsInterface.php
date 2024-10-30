<?php
namespace CardinalCommerce\Payments\Interfaces;

interface APICredentialsInterface {
    public function getApiIdentifier();
    public function getOrgUnitId();
    public function getApiKey();
}