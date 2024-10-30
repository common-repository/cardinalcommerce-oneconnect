<?php
namespace CardinalCommerce\Payments\Carts\Common\Songbird\Objects;

use \CardinalCommerce\Payments\Objects as PaymentObjects;

class ServerJWTPayload {

    public function __construct( $data ) {
        $this->OrderDetails = null;
        $this->Consumer = null;

        if ( property_exists( $data, 'OrderDetails' ) ) {
            $this->OrderDetails = new PaymentObjects\OrderDetails(
                $data->OrderDetails
            );
        }

        if ( property_exists( $data, 'Consumer' ) ) {
            $this->Consumer = $data->Consumer;
        }
    }

    public $OrderDetails;
    public $Consumer;

    public function toJSONObject() {
        $data = (object) array();

        if ( !empty( $this->OrderDetails ) && method_exists( $this->OrderDetails, 'toJSONObject' )) {
            $data->OrderDetails = $this->OrderDetails->toJSONObject();
        }

        if ( !empty( $this->Consumer ) && method_exists( $this->Consumer, 'toJSONObject' ) ) {
            $data->Consumer = $this->Consumer->toJSONObject();
        }

        return $data;
    }

    public function __toString() {
        return json_encode( $this->toJSONObject() );
    }
}