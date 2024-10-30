<?php
namespace CardinalCommerce\Payments\Carts\Common\Songbird;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

const NBF_SKEW = 30; // 30 seconds
const MAX_JWT_AGE = 300; // 5 minutes

class ServerJWTCreator implements SongbirdInterfaces\ServerJWTCreatorInterface {
    private $_logger;
    private $_settings;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $settings
    ) {
        $this->_logger = $logger;
        $this->_settings = $settings;
    }

    /**
     * Generate a ServerJWT for CardinalCruise
     *
     * @param CartInterfaces\CartOrderDetailsInterface $cartOrderDetails
     * @param Objects\ServerJWTPayload $serverJWTPayload
     *
     * @return string the ServerJWT
     */
    public function create(
        CartInterfaces\CartOrderDetailsInterface $cartOrderDetails,
        Objects\ServerJWTPayload $serverJWTPayload
    ) {
        $cartSettings = $this->_settings;
        $apiCreds = $cartSettings->getAPICredentials();

        $identifier = $apiCreds->getApiIdentifier();
        $orgUnitId = $apiCreds->getOrgUnitId();
        $apiKey = $apiCreds->getApiKey();

        // TODO: Remove
        $this->_logger->info('[ServerJWTCreator::create] API Key: identifier: [{identifier}] apiKey: [{apiKey}] orgUnitId: [{orgUnitId}]', array(
            'identifier' => $identifier,
            'orgUnitId' => $orgUnitId,
            'apiKey' => $apiKey
        ));

        $issuedAt = time();
        $exp = $issuedAt + MAX_JWT_AGE;

        $orderId = $cartOrderDetails->getOrderKey();

        $jti = sprintf("%s-%s", $orderId, $issuedAt);

        $payloadData = $serverJWTPayload->toJSONObject();

        $serverJWT = (new Builder())
            ->setIssuer($identifier)
            ->setId($jti)
            ->setIssuedAt($issuedAt)
            ->setExpiration($exp)
            // NEXTREV: Set NFB?
            ->set('OrgUnitId', $orgUnitId)
            ->set('Payload', $payloadData)
            ->set('ObjectifyPayload', true)
            ->sign(new Sha256(), $apiKey)
            ->getToken();

        $this->_logger->info('Generated ServerJWT: {serverJWT}', array( 'serverJWT' => $serverJWT ));
        return sprintf("%s", $serverJWT);
    }
}