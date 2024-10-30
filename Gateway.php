<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Payment_Gateway_Cardinal_OneConnect extends WC_Payment_Gateway_CC {
    public function __construct() {
        $this->id = 'cardinalpm';
        $this->title = 'Credit Card';
        $this->method_title = '3-D Secure Payment Gateway by CardinalCommerce';

        $this->has_fields = true;
        $this->supports = array(
            'products', 
            'refunds'
            //'tokenization'
        );

        add_action( 'woocommerce_update_options_payment_gateways_' .
            $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_admin_order_data_after_order_details',
            array( $this, 'display_order_details' ) );

        $this->init_form_fields();
        $this->init_settings();
        $this->register_scripts();

        if( $this->is_hidden() ) {
            $this->enabled = false;
        };

        $this->currencies = include
            plugin_dir_path(CARDINAL_ONECONNECT_PLUGIN_FILE) . 'currencies.php';

        $this->instances = include
            plugin_dir_path(CARDINAL_ONECONNECT_PLUGIN_FILE) . 'instances.php';

        require_once plugin_dir_path(CARDINAL_ONECONNECT_PLUGIN_FILE) .
        'Token.php';
    }

    private function register_scripts() {
        $songbird_domain = 'songbird.cardinalcommerce.com';
        if ($this->get_option('environment') == 'STAG') {
            $songbird_domain = 'songbirdstag.cardinalcommerce.com';
        }
        wp_register_script(
            'cardinalcommerce-oneconnect-songbird',
            "https://{$songbird_domain}/edge/v1/songbird.js");
        wp_register_script(
            'cardinalcommerce-oneconnect',
            plugins_url('cardinalcommerce-oneconnect.js',
                        CARDINAL_ONECONNECT_PLUGIN_FILE),
            array('jquery', 'cardinalcommerce-oneconnect-songbird'),
            CARDINAL_ONECONNECT_VERSION, true);
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Enable / Disable',
                'label' => 'Enable this gateway',
                'type' => 'checkbox',
                'default' => 'no',
            ),

            'ccaEnabled' => array(
                'title' => 'Enable / Disable CCA',
                'label' => 'Enable Cardinal Consumer Authentication',
                'type' => 'checkbox',
                'default' => 'yes',
            ),

            'songbirdLoggingEnabled' => array(
                'title' => 'Browser Logging',
                'label' => 'Enable logging in Cardinal Cruise',
                'description' =>
                    'Whether to enable logging on the checkout page for ' .
                    'Cardinal Cruise. You will need to open your browser ' .
                    'console during checkout to see this output.',
                'desc_tip' => true,
                'type' => 'checkbox',
                'default' => 'yes',
            ),

            'emulationEnabled' => array(
                'title' => 'Enable / Disable Emulation Mode',
                'label' => 'Enable gateway emulation for admin testing',
                'description' =>
                    'Choose to enable emulation mode. The plugin will ' .
                    'only be visible to a logged in administrator on the ' .
                    'checkout page.',
                'type' => 'checkbox',
                'default' => 'yes',

            ),

            'paymentAuthType' => array(
                'title' => 'Payment Action',
                'description' =>
                    'Choose whether you wish to capture funds immediately ' .
                    'or authorize payment only. (This will apply to all ' .
                    'products.)',
                'desc_tip' => true,
                'type' => 'select',
                'default' => 'AUTH_ONLY',
                'options' => array(
                    'AUTH_ONLY' => 'Authorize',
                    'AUTH_CAPTURE' => 'Sale',
                ),
            ),

            'environment' => array(
                'title' => 'Chosen Environment',
                'description' =>
                    'Choose environment to process your transaction.',
                'type' => 'select',
                'default' => 'PROD',
                'options' => array(
                    'STAG' => 'Centinel Test',
                    'CYBERSOURCE' => 'CyberSource',
                    'FIRSTDATA' => 'FirstData',
                    'FIRSTDATA_TEST' => 'FirstData Test',
                    'PAYMENTECH' => 'Paymentech',
                    'PAYPAL' => 'PayPal',
                    '200' => 'Production 200',
                    '300' => 'Production 300',
                    '400' => 'Production 400',
                    'PROD' => 'Production 600',
                    '800' => 'Production 800',
                    '1000' => 'Production 1000',
                    '1200' => 'Production 1200',
                ),
            ),

            'paymentBrands' => array(
                'title' => 'Supported Payment Brands',
                'description' => 'Select the payment brands that you would ' .
                    'like to accept with CardinalCommerce OneConnect.',
                'desc_tip' => true,
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'default' => array(
                    'visa',
                    'mastercard'
                ),
                'options' => array(
                    'visa' => 'Visa',
                    'mastercard' => 'MasterCard',
                    'amex' => 'American Express',
                    'discover' => 'Discover',
                    'diners' => 'Diners',
                    'jcb' => 'JCB',
                )
            ),

            'merchantContent' => array(
                'title' => 'Consumer Messaging',
                'description' =>
                    'Input text to display to customers while payment is ' .
                    'processing.',
                'desc_tip' => true,
                'type' => 'text',
            ),

            'apiIdentifier' => array(
                'title' => 'API Identifier',
                'description' =>
                    'Enter the API Identifier given to you when you created ' .
                    'your API key.',
                'desc_tip' => true,
                'type' => 'text',
            ),

            'orgUnitId' => array(
                'title' => 'API OrgUnitId',
                'description' =>
                    'Enter the OrgUnitId given to you when you created your ' .
                    'API key.',
                'desc_tip' => true,
                'type' => 'text',
            ),

            'apiKey' => array(
                'title' => 'API Key',
                'description' => 'Enter the API key you created.',
                'desc_tip' => true,
                'type' => 'text',
            ),
        );

        $this->form_fields = apply_filters( 'wc_payment_gateway_' . $this->id .
            '_form_fields', $this->form_fields, $this );
    }

    private static function base64_encode_urlsafe($source) {
        $rv = base64_encode($source);
        $rv = str_replace('=', '', $rv);
        $rv = str_replace('+', '-', $rv);
        $rv = str_replace('/', '_', $rv);
        return $rv;
    }

    private static function base64_decode_urlsafe($source) {
        $s = $source;
        $s = str_replace('-', '+', $s);
        $s = str_replace('_', '/', $s);
        $s = str_pad($s, strlen($s) + strlen($s) % 4, '=');
        $rv = base64_decode($s);
        return $rv;
    }

    public function sign_jwt($header, $body) {
        $secret = $this->get_option('apiKey');
        $plaintext = $header . '.' . $body;
        return self::base64_encode_urlsafe(hash_hmac(
            'sha256', $plaintext, $secret, true));
    }

    private function generate_jwt($data) {
        $header = self::base64_encode_urlsafe(json_encode(array(
            'alg' => 'HS256', 'typ' => 'JWT'
        )));
        $body = self::base64_encode_urlsafe(json_encode($data));
        $signature = $this->sign_jwt($header, $body);
        return $header . '.' . $body . '.' . $signature;
    }

    private function generate_cruise_jwt($order = null) {
        $iat = time();
        $data = array(
            'jti' => uniqid(),
            'iat' => $iat,
            'exp' => $iat + 7200,
            'iss' => $this->get_option('apiIdentifier'),
            'OrgUnitId' => $this->get_option('orgUnitId'),
        );
        if ( $order ) {
            $payload = $this->create_request_order_object($order);
            $data['Payload'] = $payload;
            $data['ObjectifyPayload'] = true;
        }
        $rv = $this->generate_jwt($data);
        return $rv;
    }

    public function parse_cruise_jwt($jwt) {
        $split = explode('.', $jwt);
        if (count($split) != 3) {
            return;
        }
        list($header, $body, $signature) = $split;
        if ($signature != $this->sign_jwt($header, $body)) {
            return;
        }
        $payload = json_decode(self::base64_decode_urlsafe($body));
        return $payload;
    }

    public function hidden_input($id, $value = '') {
        echo "<input type='hidden' id='{$id}' value='{$value}' />";
    }

    public function payment_fields() {
        wp_enqueue_script('cardinalcommerce-oneconnect');
        parent::payment_fields();

        if ( ! is_ajax() ) {
            $jwt = $this->generate_cruise_jwt();
            $this->hidden_input('CardinalOneConnectJWT', $jwt);
            $this->hidden_input('CardinalOneConnectLoggingLevel',
                $this->get_option('songbirdLoggingEnabled') == 'yes' ?
                    'verbose' : 'no');
        }

        $id = 'CardinalOneConnectResult';
        $merchant_content = $this->get_option('merchantContent');
        echo "<input type='hidden' autocomplete='off' id='{$id}' name='$id' /><div id='merchant-content-wrapper' style='display: none'><div id='actual-merchant-content'>{$merchant_content}</div></div>";
    }

    public function pm_message($type, $orderid, $amount, $currency,
                               $fields=array()) {
        $timestamp = time() * 1000;
        $plaintext = $timestamp . $this->get_option('apiKey');
        $signature = base64_encode(hash('sha256', $plaintext, true));
        $msg = array(
            'Version' => '1.7',
            'TransactionType' => 'CC',
            'MsgType' => "cmpi_{$type}",
            'OrgUnit' => $this->get_option('orgUnitId'),
            'OrderId' => $orderid,
            'Amount' => $amount,
            'CurrencyCode' => $this->currency_numeric($currency),
            'Identifier' => $this->get_option('apiIdentifier'),
            'Algorithm' => 'SHA-256',
            'Timestamp' => $timestamp,
            'Signature' => $signature,
        );
        $msg = array_merge($msg, $fields);
        return $msg;
    }

    public function mpi_xml($msg) {
        $rv = '<CardinalMPI>';
        foreach ($msg as $k => $v) {
            $v = str_replace('&', '&amp;', $v);
            $v = str_replace('<', '&lt;', $v);
            $rv .= "<{$k}>{$v}</{$k}>";
        }
        $rv .= '</CardinalMPI>';
        return $rv;
    }

    public function parse_mpi_xml($xml) {
        if (strpos($xml, '<CardinalMPI>') === false) {
            return "No mpi response received from centinel";
        }
        $msg = array();
        $fields = array(
            'AuthorizationCode', 'AVSResult', 'CardCodeResult', 'ErrorDesc',
            'ErrorNo', 'MerchantData', 'MerchantReferenceNumber', 'OrderId',
            'OrderNumber', 'ProcessorOrderNumber', 'ProcessorStatusCode',
            'ProcessorTransactionId', 'ReasonCode', 'ReasonDesc', 'StatusCode',
            'TransactionId',
        );
        foreach ($fields as $key) {
            $value = '';
            if (preg_match("{<{$key}>([^<]*)</{$key}>}", $xml, $m)) {
                $value = $m[1];
            }
            $msg[$key] = $value;
        }
        return $msg;
    }

    public function pm_send_message($msg) {
        $env = $this->get_option('environment');
        $mpi_domain = $this->instances[$env];
        $maps_url = "https://{$mpi_domain}/maps/txns.asp";
        $xml = $this->mpi_xml($msg);
        $response = wp_remote_post($maps_url, array(
            'method' => 'POST',
            'timeout' => 65,
            'body' => array('cmpi_msg' => $xml),
        ));
        if (is_wp_error($response)) {
            return $response->get_error_message();
        }
        $body = wp_remote_retrieve_body($response);
        if (!$body) {
            return "No response received from centinel";
        }
        return $this->parse_mpi_xml($body);
    }

    public function format_mpi_error($response) {
        $rv = $response['ErrorDesc'];
        if ($response['ErrorNo']) {
            $rv .= " ({$response['ErrorNo']})";
        }
        if ($response['ReasonDesc']) {
            $rv .= " {$response['ReasonDesc']}";
        }
        if ($response['ReasonCode']) {
            $rv .= " ({$response['ReasonCode']})";
        }
        return $rv;
    }

    public function reject_with_error($message, $permanent = false) {
        wc_add_notice("{$this->method_title}: {$message}", 'error');
        ob_start();
        wc_print_notices();
        $messages = ob_get_clean();
        if ( ! $permanent ) {
            $messages .= '<script>Cardinal.OneConnect.clear_results()</script>';
        }
        wp_send_json( array( 'messages' => $messages ) );
        exit;
    }

    public function order_add($order, $key, $value) {
        update_post_meta($order->get_id(), "_{$this->id}_{$key}", $value);
    }

    public function order_get($order, $key) {
        $meta = get_post_meta($order->get_id(), "_{$this->id}_{$key}");
        return isset($meta[0]) ? $meta[0] : null;
    }

    public function display_order_details_fields($order, $fields) {
        echo '<p class="form-field form-field-wide">';
        foreach ($fields as $key => $name) {
            $value = $this->order_get($order, $key);
            echo "{$name}: {$value}<br />";
        }
        echo '</p>';
    }

    public function display_order_details($order) {
        if ($order->get_payment_method() != $this->id) {
            return;
        }

        echo '<h3 class="form-field form-field-wide" style="margin: 2em 0 0;">' .
            $this->method_title . '</h3>';
        $order_fields = array(
            'OrderId' => 'Order Id',
            'ProcessorOrderNumber' => 'Processor Order Number',
        );
        $authorization_fields = array(
            'AuthorizationStatus' => 'Authorization Status',
            'AuthorizationCode' => 'Authorization Code',
            'AVSResult' => 'AVS Result',
            'CardCodeResult' => 'Card Code Result',
        );
        $capture_fields = array(
            'CaptureStatus' => 'Capture Status',
            'VoidStatus' => 'Void Status',
        );
        $action_fields = array(
            'ActionCode' => 'Action Code',
        );
        $this->display_order_details_fields($order, $order_fields);
        $this->display_order_details_fields($order, $authorization_fields);
        $this->display_order_details_fields($order, $capture_fields);
        $this->display_order_details_fields($order, $action_fields);
    }

    public function status_message($order, $message, $amount = null,
                                   $error = null) {
        if (!$amount) {
            $amount = $order->get_total();
        }
        $price = wc_price($amount, array('currency' => $order->get_currency()));
        $rv = "{$this->method_title}: {$message} for $price";
        if (isset($error)) {
            $rv .= " - {$error}";
        }
        return $rv;
    }

    // public function add_payment_method() {

    //     $old_token = $this->get_users_token();
    //     $token = $this->save_token();
    //     if ( is_null( $token ) ) {
    //         $this->reject_with_error('This payment method could not be saved.');
    //         return;
    //     }

    //     return array(
    //         'result'   => 'success',
    //         'redirect' => wc_get_endpoint_url( 'payment-methods' ),
    //     );
    // }

    // public function save_token(){

    //     $cruise_result_json = $_POST['CardinalOneConnectResult'];
    //     $cruise_result = json_decode(stripslashes($cruise_result_json));
    //     $jwt = $this->parse_cruise_jwt($cruise_result->jwt);
    //     $cc_token = $jwt->Token;

    //     $token = new WC_Payment_Gateway_Cardinal_OneConnect_Token();

    //     $token->set_token($cc_token->Token);
    //     $token->set_gateway_id($this->id);
    //     $token->set_last4($cc_token->CardLastFour);
    //     $token->set_expiry_year($cc_token->ExpirationYear);
    //     $token->set_expiry_month($cc_token->ExpirationMonth);
    //     $token->set_card_code($cc_token->CardCode);

    //     if ( is_user_logged_in() ) {
    //         $token->set_user_id( get_current_user_id() );
    //     }

    //     $token->save();

    //     if ( is_user_logged_in() ) {
    //         WC_Payment_Tokens::set_users_default( get_current_user_id(), $token->get_id() );
    //     }

    //     return $token;
    // }

    // protected function get_users_token() {
    //     $customer_token  = null;
    //     if ( is_user_logged_in() ) {
    //         $tokens = WC_Payment_Tokens::get_customer_tokens( get_current_user_id() ) ;
    //         foreach ( $tokens as $token ) {
    //             if ( $token->get_gateway_id() === $this->id ) {
    //                 $customer_token = $token;
    //                 break;
    //             }
    //         }
    //     }
    //     return $customer_token;
    // }

    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        $cruise_result_json = $_POST['CardinalOneConnectResult'];
        if ( ! $cruise_result_json ) {
            $jwt = $this->generate_cruise_jwt($order);
            wp_send_json(array(
                'messages' =>
                    "<script>Cardinal.OneConnect.start('{$jwt}')</script>"
            ));
            exit;
        }

        $cruise_result = json_decode(stripslashes($cruise_result_json));
        $data = $cruise_result->data;
        $this->order_add($order, "ActionCode", $data->ActionCode);

        switch ($data->ActionCode) {
        case 'SUCCESS':
        case 'NOACTION':
            break;
        case 'FAILURE':
            $this->reject_with_error('Payment was unsuccessful. ' .
                'Please try again or provide another form of payment.');
            break;
        case 'ERROR':
            $message = $data->ErrorDescription;
            if ( isset($data->ErrorNumber) ) {
                $message .= " ({$data->ErrorNumber})";
            }
            $this->reject_with_error($message, isset($data->PermanentFatal));
            break;
        default:
            $this->reject_with_error('Unknown ActionCode');
            break;
        }

        if (!isset($cruise_result->jwt)) {
            $this->reject_with_error('Missing jwt');
        }

        $jwt = $this->parse_cruise_jwt($cruise_result->jwt);
        if (!$jwt) {
            $this->reject_with_error('Failed to parse jwt');
        }

        $payload = $jwt->Payload;
        if ($payload->ActionCode != $data->ActionCode) {
            $this->reject_with_error('data and Payload ActionCode do not match');
        }

        // $token = $jwt->Token;
        // if( isset( $_POST['wc-cardinalpm-new-payment-method'] ) ) {
        //     if (!$token){
        //         $this->reject_with_error('This payment method could not be saved.');
        //         return;
        //     } else {
        //         $this->add_payment_method();
        //     }
        // }

        $orderid = $payload->AuthorizationProcessor->ProcessorOrderId;
        $cca = $payload->Payment->ExtendedData;
        $eci = isset($cca->ECIFlag) ? $cca->ECIFlag : '';
        $cavv = isset($cca->CAVV) ? $cca->CAVV : '';
        $xid = isset($cca->XID) ? $cca->XID : '';

        $currency = $order->get_currency();
        $amount = self::raw_amount($order->get_total(), $currency);
        $msg = $this->pm_message(
            'authorize', $orderid, $amount, $currency, array(
                'Eci' => $eci,
                'Cavv' => $cavv,
                'Xid' => $xid,
                'OrderNumber' => $order->get_order_number(),
                'EMail' => $order->get_billing_email(),
                "BillingFirstName" => $order->get_billing_first_name(),
                "BillingLastName" => $order->get_billing_last_name(),
                "BillingAddress1" => $order->get_billing_address_1(),
                "BillingAddress2" => $order->get_billing_address_2(),
                "BillingCity" => $order->get_billing_city(),
                "BillingState" => $order->get_billing_state(),
                "BillingPostalCode" => $order->get_billing_postcode(),
                "BillingCountryCode" => $order->get_billing_country(),
                "BillingPhone" => $order->get_billing_phone(),
                "ShippingFirstName" => $order->get_shipping_first_name(),
                "ShippingLastName" => $order->get_shipping_last_name(),
                "ShippingAddress1" => $order->get_shipping_address_1(),
                "ShippingAddress2" => $order->get_shipping_address_2(),
                "ShippingCity" => $order->get_shipping_city(),
                "ShippingState" => $order->get_shipping_state(),
                "ShippingPostalCode" => $order->get_shipping_postcode(),
                "ShippingCountryCode" => $order->get_shipping_country(),
            )
        );
        $auth_response = $response = $this->pm_send_message($msg);
        if (!is_array($response)) {
            $this->reject_with_error($response);
        }
        $auth_status = $response['StatusCode'];
        if ($auth_status == 'E' && $response['ReasonCode'] == '4' &&
                preg_match('/^25[23] /', $response['ReasonDesc'])) {
            $auth_status = 'P';
        }
        $this->order_add($order, 'AuthorizationStatus', $auth_status);
        if (!in_array($auth_status, array('Y', 'P'))) {
            $this->reject_with_error($this->format_mpi_error($response));
        }

        if ($auth_status == 'Y' &&
                $this->get_option('paymentAuthType') == 'AUTH_CAPTURE') {
            $msg = $this->pm_message('capture', $orderid, $amount, $currency);
            $response = $this->pm_send_message($msg);
            $void = $this->pm_message('void', $orderid, $amount, $currency);
            if (!is_array($response)) {
                $this->pm_send_message($void);
                $this->reject_with_error($response);
            }
            if ($response['StatusCode'] != 'Y') {
                $this->pm_send_message($void);
                $this->reject_with_error($this->format_mpi_error($response));
            }

            $this->order_add($order, 'CaptureStatus', $response['StatusCode']);
            $order->add_order_note($this->status_message(
                $order, 'Payment authorized and captured'));
            $order->payment_complete($orderid);
        } else {
            $order->set_transaction_id($orderid);
            if ($auth_status == 'Y') {
                $order->update_status('on-hold',
                    $this->status_message($order, 'Payment authorized'));
            } else {
                $order->update_status('on-hold',
                    $this->status_message($order, 'Payment held for review. Please, login to your processor account to manage this order.'));
            }
            $order->reduce_order_stock();
            WC()->cart->empty_cart();
        }

        foreach ($auth_response as $key => $value) {
            $this->order_add($order, $key, $value);
        }

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order )
        );
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        $error_code = "wc_{$this->id}_refund_error";
        if (!($amount > 0)) {
            return new WP_Error(
                $error_code, 'Amount must be specified for refund');
        }

        $order = wc_get_order( $order_id );
        $orderid = $this->order_get($order, 'OrderId');
        $currency = $order->get_currency();
        $amt = self::raw_amount($amount, $currency);

        $msg = $this->pm_message('refund', $orderid, $amt, $currency, array(
            'Description' => $reason,
        ));
        $response = $this->pm_send_message($msg);

        if (!is_array($response)) {
            return new WP_Error($error_code, $response);
        }
        if ($response['StatusCode'] != 'Y') {
            return new WP_Error($error_code, $this->format_mpi_error($response));
        }

        $order->add_order_note(
            $this->status_message($order, 'Refund processed', $amount));

        return true;
    }

    public function order_status_changed($order_id, $from_status, $to_status) {
        $order = wc_get_order( $order_id );
        if ($order->get_payment_method() != $this->id) {
            return;
        }

        $orderid = $this->order_get($order, 'OrderId');
        $currency = $order->get_currency();
        $amount = self::raw_amount($order->get_total(), $currency);

        if ($to_status == 'cancelled' &&
                $this->order_get($order, 'CaptureStatus') != 'Y' &&
                $this->order_get($order, 'VoidStatus') != 'Y') {
            $msg = $this->pm_message('void', $orderid, $amount, $currency);
            $response = $this->pm_send_message($msg);
            $error = null;
            if (!is_array($response)) {
                $error = $response;
            }
            if ($response['StatusCode'] != 'Y') {
                $error = $this->format_mpi_error($response);
            }
            if ($error) {
                $order->add_order_note(
                    $this->status_message($order, 'Void failed',
                                          $order->get_total(), $error));
            } else {
                $this->order_add($order, 'VoidStatus', $response['StatusCode']);
                $order->add_order_note(
                    $this->status_message($order, 'Void processed'));
            }
            return;
        }

        if (!in_array($to_status, array('processing', 'completed')) ||
                $this->order_get($order, 'AuthorizationStatus') != 'Y' ||
                $this->order_get($order, 'CaptureStatus') == 'Y') {
            return;
        }

        $msg = $this->pm_message('capture', $orderid, $amount, $currency);
        $response = $this->pm_send_message($msg);
        $error = null;
        if (!is_array($response)) {
            $error = $response;
        } else {
            $this->order_add($order, 'CaptureStatus', $response['StatusCode']);
            if ($response['StatusCode'] != 'Y') {
                $error = $this->format_mpi_error($response);
            }
        }
        if ($error) {
            $order->set_date_paid(null);
            $order->set_date_completed(null);
            $order->update_status('failed', $this->status_message(
                $order, 'Capture failed', $order->get_total(), $error));
            return;
        }

        $order->add_order_note(
            $this->status_message($order, 'Capture processed'));
    }

    public function currency_numeric($alpha) {
        return isset($this->currencies[$alpha]) ?
            $this->currencies[$alpha] : null;
    }

    public static function currency_exponent($alpha) {
        if (in_array($alpha, array(
            'ADP', 'BEF', 'BIF', 'BYR', 'CLP', 'DJF', 'ESP', 'GNF', 'ISK',
            'ITL', 'JPY', 'KMF', 'KRW', 'LUF', 'MGF', 'PTE', 'PYG', 'RWF',
            'TPE', 'TRL', 'UYI', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
        ))) {
            return 0;
        } elseif (in_array($alpha, array(
            'BHD', 'CSD', 'IQD', 'JOD', 'KWD', 'LYD', 'OMR', 'TND',
        ))) {
            return 3;
        } elseif ($alpha == 'CLF') {
            return 4;
        }
        return 2;
    }

    public static function raw_amount($amount, $currency_alpha) {
        $float_amount = (float) $amount;
        $exponent = self::currency_exponent($currency_alpha);
        $int_amount = (int) round($float_amount * pow(10, $exponent));
        return (string) $int_amount;
    }

    public function create_request_order_object($order) {
        $currency_alpha = $order->get_currency();
        $raw_amount = self::raw_amount($order->get_total(), $currency_alpha);

        $request_order_object = array(
            "Consumer" => array(
                "BillingAddress" => array(
                    "FirstName" => $order->get_billing_first_name(),
                    "LastName" => $order->get_billing_last_name(),
                    "Address1" => $order->get_billing_address_1(),
                    "Address2" => $order->get_billing_address_2(),
                    "City" => $order->get_billing_city(),
                    "State" => $order->get_billing_state(),
                    "PostalCode" => $order->get_billing_postcode(),
                    "CountryCode" => $order->get_billing_country(),
                    "Phone1" => $order->get_billing_phone(),
                ),
                "ShippingAddress" => array(
                    "FirstName" => $order->get_shipping_first_name(),
                    "LastName" => $order->get_shipping_last_name(),
                    "Address1" => $order->get_shipping_address_1(),
                    "Address2" => $order->get_shipping_address_2(),
                    "City" => $order->get_shipping_city(),
                    "State" => $order->get_shipping_state(),
                    "PostalCode" => $order->get_shipping_postcode(),
                    "CountryCode" => $order->get_shipping_country(),
                ),
                "Email1" => $order->get_billing_email(),
            ),
            "OrderDetails" => array(
                "OrderNumber" => $order->get_order_number(),
                "Amount" => $raw_amount,
                "CurrencyCode" => $currency_alpha,
                "OrderChannel" => "S",
            ),
            "Options" => array(
                "EnableCCA" => $this->get_option('ccaEnabled') == 'yes',
            ),
        );

        return $request_order_object;
    }

    public function field_name( $name ) {
        return '';
    }

    public function get_icon() {
        $card_types = $this->get_option('paymentBrands');
        $icon = '';
        foreach (array_reverse($card_types) as $type) {
            $lc = strtolower($type);
            $src = WC_HTTPS::force_https_url(WC()->plugin_url()) .
                "/assets/images/icons/credit-cards/{$lc}.svg";
            $style = $icon ? "style='margin-right: 2px;' " : '';
            $icon .= "<img src='{$src}' alt='{$type}' width='38' {$style}/>";
        }

        return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
    }

    private function is_hidden() {
        $is_admin = false;
        $emulation_enabled = $this->get_option('emulationEnabled');
        $user = new WP_User(get_current_user_id());
        if ( isset($user->roles[0]) and  $user->roles[0] == "administrator" ) {
            $is_admin = true;
        };
        if ( ( $emulation_enabled == "yes" ) and $is_admin ){
            return false;
        } elseif ( ( $emulation_enabled == "yes" ) and !$is_admin ) { 
            return true;
        } elseif ( $emulation_enabled == "no" ) {
            return false;
        } else {
            return false;
        };
    }

}
