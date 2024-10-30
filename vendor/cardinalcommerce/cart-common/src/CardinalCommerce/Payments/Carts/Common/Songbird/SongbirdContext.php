<?php
namespace CardinalCommerce\Payments\Carts\Common\Songbird;

use \Psr\Log\LoggerInterface;

use \CardinalCommerce\Payments\Carts\Common\Interfaces as CommonInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;
use \CardinalCommerce\Payments\Objects as PaymentObjects;

use \CardinalCommerce\Payments\Carts\Common\BaseCartOrder;

class SongbirdContext implements SongbirdInterfaces\SongbirdContextInterface {
    private $_logger;
    private $_settings;
    private $_cartPaymentDetailsPage;

    private $_serverJWTCreator;

    public function __construct(
        LoggerInterface $logger,
        CartInterfaces\CartSettingsInterface $cartSettings,
        PageInterfaces\CartPaymentDetailsPageInterface $cartPaymentDetailsPage
    ) {
        $this->_logger = $logger;
        $this->_settings = $cartSettings;
        $this->_cartPaymentDetailsPage = $cartPaymentDetailsPage;
    }

    public function getSettings() {
        return $this->_settings;
    }

    public function getCartPaymentDetailsPage() {
        return $this->_cartPaymentDetailsPage;
    }

    /**
     * Return Songbird Cardinal.configure options
     * @returns object
     */
    public function getConfigureOptions() {
        $opts = (object) [];

        $cartSettings = $this->_settings;

        if ($cartSettings->getSongbirdLoggingEnabled()) {
            $opts->logging = (object) ["level" => "verbose"];
        }

        if ($cartSettings->getCCAEnabled() === true) {
            $opts->EnableCCA = true;
        }
        else {
            $opts->EnableCCA = false;
        }

        return $opts;
    }

    /**
     * Return Songbird Cardinal.setup('init') options
     * @returns object
     */
    public function getSetupInitOptions(
        BaseCartOrder $cartOrder,
        PaymentObjects\Consumer $consumerObject,
        $serverJWT
    ) {
        $logger = $this->_logger;

        $logger->info('[getSetupInitOptions] serverJWT: ' . $serverJWT);

        $initParams = (object) array(
            "jwt" => sprintf("%s", $serverJWT)
        );

        $logger->info('[getSetupInitOptions] initParams: ' . json_encode($initParams));
        return $initParams;
    }

    // NEXTREV: Move to CartPaymentDetailsPageInterface
    public function getServerJWTHiddenInputName() {
        return "CardinalCruise:ServerJWT";
    }

    // NEXTREV: Move to CartPaymentDetailsPageInterface
    public function getResponseJWTHiddenInputName() {
        return "CardinalCruise:ResponseJWT";
    }
}