<?php
namespace CardinalCommerce\Payments\Carts\Common\Songbird;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class ResponseJWTParser implements SongbirdInterfaces\ResponseJWTParserInterface {
    private $_logger;
    private $_settings;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
    }

    public function parse($responseJWT) {
        $logger = $this->_logger;
        $cartSettings = $this->_settings;

        $apiCreds = $this->_settings->getAPICredentials();

        $identifier = $apiCreds->getApiIdentifier();
        $orgUnitId = $apiCreds->getOrgUnitId();
        $apiKey = $apiCreds->getApiKey();

        $logger->debug('[ResponseJWTProcessor::_create] API Key: identifier: [{identifier}] apiKey: [{apiKey}] orgUnitId: [{orgUnitId}]', array(
            'identifier' => $identifier,
            'orgUnitId' => $orgUnitId,
            'apiKey' => '****'
        ));

        $logger->info('Processing ResponseJWT: {responseJWT}', array( 'responseJWT' => $jwt ));
        $parsed = (new Parser())
            ->parse((string) $responseJWT);

        $claims = $parsed->getClaims();
        $logger->info('Processing ResponseJWT: claims: ' . json_encode( $claims ));

        // Convert to basic objects
        $payload = json_decode(json_encode($claims));
        $logger->info('Processing ResponseJWT: payload: ' . var_export( $payload, TRUE ));

        return $payload;
    }

}