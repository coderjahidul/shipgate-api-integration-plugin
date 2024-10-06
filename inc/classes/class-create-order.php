<?php

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Program_Logs;
use BOILERPLATE\Inc\Traits\Singleton;
use WC_Order;

class Create_Order {

    use Singleton;
    use Program_Logs;

    private $order_object_id;
    private $rate_object_id;
    private $house_bl;
    private $api_key;

    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        add_action( 'woocommerce_thankyou', [ $this, 'create_order' ] );
    }

    public function create_order( $order_id ) {

        // Get WooCommerce order object
        $order = wc_get_order( $order_id );

        if ( !$order ) {
            return; // Order not found
        }

        // Build the order payload dynamically
        $order_data = $this->generate_order_payload( $order );

        // Call the ShipGate API with the generated order payload
        $response = $this->call_create_order_api( $order_data );
        // If order place and response save result.object_id to this->order_object_id variable
        if ( $response ) {
            // put response to logs
            $this->put_program_logs( 'Create Order API Response: ' . $response );
            $response_decode       = json_decode( $response, true );
            $this->order_object_id = $response_decode['result']['objectId'];

            
        }
    }

    public function generate_order_payload(WC_Order $order) {
        // Fetch order details
        $price = $order->get_total();
        $currency = $order->get_currency();
        $recipient_name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        $recipient_email = $order->get_billing_email();
        $recipient_phone = $order->get_billing_phone();
        $recipient_address1 = $order->get_shipping_address_1();
        $recipient_city = $order->get_shipping_city();
        $recipient_state = $order->get_shipping_state();
        $recipient_zip = $order->get_shipping_postcode();
        $recipient_country_code = $order->get_shipping_country();
    
        // Fetch store details
        $store_name = get_option('custom_name');
        $store_company = get_option('custom_company');
        $store_business_number = get_option('custom_business_number');
        $store_email = get_option('custom_email');
        $phone_number = get_option('custom_phone_num1');
        // Divide phone number into 3 parts
        $store_phone_num1 = substr($phone_number, 0, 3);
        $store_phone_num2 = substr($phone_number, 3, 4);
        $store_phone_num3 = substr($phone_number, 7, 4);
        $store_address_1 = get_option('woocommerce_store_address');
        $store_address_2 = get_option('woocommerce_store_address_2');
        $store_city = get_option('woocommerce_store_city');
        $store_postcode = get_option('woocommerce_store_postcode');
    
        // Populate customsList from order items
        $customsList = [];
        $parcel_weight = 0.1;
        $parcel_width = 0.1;
        $parcel_length = 0.1;
        $parcel_height = 0.1;
    
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
    
            // Validate product data
            $sku = $product->get_sku();
            $name = $product->get_name();
            $qty = $item->get_quantity();
            $weight = $product->get_weight() ?: 0.01; // Default to 0.01 if weight is missing
            $total_price = $item->get_total();
            $hsCode = '15781245'; // Set default HS Code or fetch dynamically
    
            $customsList[] = [
                'skuCd'      => $sku,
                'name'       => $name,
                'qty'        => $qty,
                'weight'     => $weight,
                'price'      => $total_price,
                'currency'   => $currency,
                'origin'     => 'KR', // Assuming origin is Korea
                'hsCode'     => $hsCode,
                'weightUnit' => 'kg', // Set the weight unit
            ];
    
            // Accumulate weights and dimensions
            $parcel_weight += $weight * $qty;
    
            // Update dimensions if needed
            if ($product->get_length() > $parcel_length) {
                $parcel_length = $product->get_length();
            }
            if ($product->get_width() > $parcel_width) {
                $parcel_width = $product->get_width();
            }
            if ($product->get_height() > $parcel_height) {
                $parcel_height = $product->get_height();
            }
        }
    
        // Define parcel information
        $parcel = [
            'packagingType' => 'box',
            'width'         => $parcel_width,
            'length'        => $parcel_length,
            'height'        => $parcel_height,
            'lengthUnit'    => 'cm',
            'weight'        => $parcel_weight, // Total weight of all items
            'weightUnit'    => 'kg',
        ];
    
        // Build the final order payload
        $order_data = [
            'price'       => $price,
            'currency'    => $currency,
            'sender'      => [
                'name'           => $store_name,
                'company'        => $store_company,
                'businessNumber' => $store_business_number,
                'email'          => $store_email,
                'phoneNum1'      => $store_phone_num1,
                'phoneNum2'      => $store_phone_num2,
                'phoneNum3'      => $store_phone_num3,
                'city'           => $store_city,
                'street1'        => $store_address_1,
                'street2'        => $store_address_2,
                'zipCd'          => $store_postcode,
            ],
            'recipient'   => [
                'name'            => $recipient_name,
                'subName'         => null,
                'company'         => $order->get_shipping_company() ?: null,
                'email'           => $recipient_email,
                'phoneNum'        => $recipient_phone,
                'countryCode'     => $recipient_country_code,
                'street1'         => $recipient_address1,
                'city'            => $recipient_city,
                'state'           => $recipient_state,
                'zipCd'           => $recipient_zip,
                'residentialFlag' => true,
            ],
            'customsList' => $customsList,
            'parcel'      => $parcel,
        ];
    
        return $order_data;
    }
    
    

    public function call_create_order_api( array $order_data ) {

        // code program logs
        // $this->put_program_logs( 'Order API payload: ' . json_encode( $order_data ) );

        // Get API key
        $this->api_key = get_option( 'shipgate_api_key' );

        $curl = curl_init();
        curl_setopt_array( $curl, array(
            CURLOPT_URL            => 'https://app.shipgate.io/api/v1/order',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode( $order_data ),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json;charset=UTF-8',
                'Accept: application/json',
                'Authorization: ' . $this->api_key,
            ),
        ) );

        $response = curl_exec( $curl );

        curl_close( $curl );
        return $response;
    }

    public function call_create_rate_api( int $order_object_id ) {

        // code program logs
        $this->put_program_logs( 'Rate API payload: ' . $order_object_id );

        $curl = curl_init();
        curl_setopt_array( $curl, array(
            CURLOPT_URL            => 'https://app.shipgate.io/api/v1/rates/order/' . $order_object_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json;charset=UTF-8',
                'Accept: application/json',
                'Authorization: ' . $this->api_key,
            ),
        ) );

        $response = curl_exec( $curl );
        curl_close( $curl );

        // put response to logs
        $this->put_program_logs( 'Rate API Response: ' . $response );

        // decode response
        $response_decode = json_decode( $response, true );
        // extract result array
        $result = $response_decode['result'];
        // get carrier and service from cookie. key is _shipgate_shipping_method
        $cookie_key               = '_shipgate_shipping_method';
        $selected_shipping_method = isset( $_COOKIE[$cookie_key] ) ? $_COOKIE[$cookie_key] : '';
        // put selected shipping method to log
        $this->put_program_logs( 'Selected Shipping Method: ' . $selected_shipping_method );
        // decode selected shipping method
        $selected_shipping_method = json_decode( $selected_shipping_method, true );
        // extract carrier and service
        $carrier   = $selected_shipping_method['carrier'];
        $service   = $selected_shipping_method['service'];
        $incoterms = '';

        // loop through result for get selected shipping method via $service
        foreach ( $result as $rate ) {
            if ( $rate['service'] === $service ) {
                $this->rate_object_id = $rate['objectId'];
                // check which incoterms is selected dduFlag or ddpFlag
                if ( $rate['dduFlag'] === true ) {
                    $incoterms = 'ddu';
                } else {
                    $incoterms = 'ddp';
                }
                break;
            }
        }

    }

    public function call_create_new_shipment_api( int $rate_object_id, $incoterms ) {

        $data = [
            "purpose"      => null,
            "incoterms"    => $incoterms, // "ddu"
            "exportNumber" => null,
        ];

        $curl = curl_init();
        curl_setopt_array( $curl, array(
            CURLOPT_URL            => 'https://app.shipgate.io/api/v1/shipment/rate/' . $rate_object_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode( $data ),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json;charset=UTF-8',
                'Accept: application/json',
                'Authorization: ' . $this->api_key,
            ),
        ) );

        $response = curl_exec( $curl );
        curl_close( $curl );

        // put result to logs
        $this->put_program_logs( 'Shipment API Response: ' . $response );

        // decode response
        $response_decode = json_decode( $response, true );
        // extract result array
        $result         = $response_decode['result'];
        $this->house_bl = $result['houseBL'];
    }

    public function call_get_tracking_api( string $house_bl ) {

        $curl = curl_init();
        curl_setopt_array( $curl, array(
            CURLOPT_URL            => 'https://app.shipgate.io/api/v1/shipment/' . urlencode( $house_bl ) . '/tracking',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json;charset=UTF-8',
                'Accept: application/json',
                'Authorization: ' . $this->api_key,
            ),
        ) );

        $response = curl_exec( $curl );

        curl_close( $curl );
        echo $response;

    }

}