<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Factories;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Cart as CartInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Pages as PageInterfaces;
use \CardinalCommerce\Payments\Carts\Common\Interfaces\Songbird as SongbirdInterfaces;

use \CardinalCommerce\Payments\Carts\Common\Interfaces\Payment\Common\CommonPaymentMethodsInterface;
use \CardinalCommerce\Payments\Carts\Common\BaseOrderPaymentMethodResolver;

use \CardinalCommerce\Payments\Carts\WooCommerce\Pages\CartPaymentDetailsPage;
use \CardinalCommerce\Payments\Carts\WooCommerce\Songbird\ScriptRenderer;
use \CardinalCommerce\Payments\Carts\WooCommerce\Processors;

class CartObjectsFactory implements CartInterfaces\CartObjectsFactoryInterface {

    /**
     * @param CommonPaymentMethodsInterface $paymentMethods
     *
     * @return BaseOrderPaymentMethodResolver
     */
    public function createCartOrderPaymentMethodResolver(
        CommonPaymentMethodsInterface $paymentMethods
    ) {
        return new Processors\OrderPaymentMethodResolver(
            wc_gateway_cardinalpm()->objects()->logger(),
            wc_gateway_cardinalpm()->objects()->settings(),
            $paymentMethods
        );
    }

    /**
     * @return PageInterfaces\CartPaymentDetailsPageInterface
     */
    public function createPaymentDetailsPage() {
        return new CartPaymentDetailsPage();
    }

    /**
     * @return SongbirdInterfaces\SongbirdCartScriptRendererInterface
     */
    public function createScriptRenderer() {
        return new ScriptRenderer(
            wc_gateway_cardinalpm()->objects()->logger(),
            wc_gateway_cardinalpm()->objects()->settings()
        );
    }
}