<?php
namespace CardinalCommerce\Payments\Carts\Common\Interfaces\Cart;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Environment as EnvironmentInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;
use \CardinalCommerce\Payments\Carts\Common\BaseOrderPaymentMethodResolver;

/**
 * Interface for creating cart-specific objects
 */
interface CartObjectsFactoryInterface {

    /**
     * @param CommonPaymentMethodsInterface $paymentMethods
     *
     * @return BaseOrderPaymentMethodResolver
     */
    public function createCartOrderPaymentMethodResolver(
        CommonPaymentMethodsInterface $paymentMethods
    );
    
    /**
     * @return PageInterfaces\CartPaymentDetailsPageInterface
     */
    public function createPaymentDetailsPage();

    /**
     * @return SongbirdInterfaces\SongbirdCartScriptRendererInterface
     */
    public function createScriptRenderer();
}