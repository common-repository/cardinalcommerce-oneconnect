<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Payment_Gateway_Cardinal_OneConnect_Token extends WC_Payment_Token_CC {

	public function __construct(){

		$this->extra_data['card_code'] = '';

	}

	public function get_card_code(){
		return $this->extra_data['card_code'];
	}

	public function set_card_code($card_code){
		$this->extra_data['card_code'] = $card_code;
	}

}