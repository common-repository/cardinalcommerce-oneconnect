<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Environment;

interface EnvironmentConfigInterface {
    public function getEnvironmentKey();

    public function getTitle();

    public function getDescription();

    /**
     * @return EnvironmentCentinelConfigInterface
     */
    public function getCentinelConfig();

    /**
     * @return EnvironmentSongbirdConfigInterface
     */
    public function getSongbirdConfig();
}