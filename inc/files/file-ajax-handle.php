<?php
// Handle the AJAX request on the server side
add_action('wp_ajax_get_shipping_rates', 'get_shipping_rates');
add_action('wp_ajax_nopriv_get_shipping_rates', 'get_shipping_rates'); // If users are not logged in

function get_shipping_rates() {
    // Get the shipping address for a customer
    $tocountrycode = isset($_POST['tocountrycode']) ? sanitize_text_field($_POST['tocountrycode']) : '';
    $tocity = isset($_POST['tocity']) ? sanitize_text_field($_POST['tocity']) : '';
    $tostreet = isset($_POST['tostreet']) ? sanitize_text_field($_POST['tostreet']) : '';
    $tozipcd = isset($_POST['tozipcd']) ? sanitize_text_field($_POST['tozipcd']) : '';

    // Ensure all fields are provided
    if (empty($tocountrycode) || empty($tocity) || empty($tostreet) || empty($tozipcd)) {
        echo json_encode(array('error' => 'Missing required shipping address fields.'));
        wp_die();
    }

    // Product Information
    $skuCdList = array('TEST_01', 'TEST_02');
    $weightGram = 100;  // Product Weight
    $packagingType = 'box';  // Packaging Type
    $lengthCm = 1.0;
    $heightCm = 1.0;
    $widthCm = 1.0;

    // Construct the API URL
    $url = "https://app.shipgate.io/api/v1/rates?weightGram={$weightGram}&packagingType={$packagingType}&lengthCm={$lengthCm}&heightCm={$heightCm}&widthCm={$widthCm}&toCountryCode={$tocountrycode}&toState=Ohio&toCity={$tocity}&toStreet={$tostreet}&toZipCd={$tozipcd}";

    // Retrieve the Shipgate API key from WordPress options
    $shipgate_api_key = get_option('shipgate_api_key');

    // Check if the API key exists
    if (empty($shipgate_api_key)) {
        echo json_encode(array('error' => 'Shipgate API key is missing.'));
        wp_die();
    }

    // Set up request arguments
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $shipgate_api_key,
        ),
    );

    // Make the API request
    $response = wp_remote_get($url, $args);

    // Handle errors with the API request
    if (is_wp_error($response)) {
        echo json_encode(array('error' => $response->get_error_message()));
        wp_die();
    }

    // Check if the API request was successful
    if (wp_remote_retrieve_response_code($response) !== 200) {
        echo json_encode(array('error' => 'Failed to retrieve shipping rates.'));
        wp_die();
    } else {
        $response = json_decode(wp_remote_retrieve_body($response), true); // Decode the JSON response
        $shipping_options = array();

        foreach ($response['result'] as $shipment) {
            $carrier = $shipment['carrier'];

            foreach ($shipment['amountList'] as $amount) {
                $type = $amount['type'];
                $amountValue = $amount['amount'];

                // Add API-derived shipping methods to array
                $shipping_options[] = array(
                    'id' => $carrier . '_' . $type, // Unique ID for the shipping method
                    'label' => $carrier . ' - ' . $type,
                    'amount' => $amountValue,
                );
            }
        }

        // Add custom shipping method
        // $second_custom_shipping = 'custom_shipping_method';
        // $custom_shipping_label = 'Express Shipping';
        // $custom_shipping_price = 10.00; // Set your custom price
        // $shipping_options[] = array(
        //     'id' => $second_custom_shipping,
        //     'label' => $custom_shipping_label,
        //     'amount' => $custom_shipping_price,
        // );

        // Return the shipping options as a JSON response
        wp_send_json_success(array('shipping_options' => $shipping_options));
    }

    wp_die(); // End the AJAX request
}

// Display Custom Shipping Method on Checkout Page
add_action('woocommerce_after_shipping_rate', 'add_custom_shipping_methods');

function add_custom_shipping_methods() {
    if (is_checkout()) {
        $chosen_method = WC()->session->get('chosen_shipping_methods');
        $package_index = 0; // Assuming single package, adjust if necessary
        $custom_shipping = 'custom_shipping_method';

        echo '<li class="shipping-method__option custom-shipping-method">';
        echo sprintf(
            '<input type="radio" name="shipping_method[%1$d]" id="shipping_method_%1$d_%2$s" value="%2$s" class="shipping_method" %3$s />',
            $package_index,
            esc_attr($custom_shipping),
            checked(isset($chosen_method[$package_index]) ? $chosen_method[$package_index] : '', $custom_shipping, false)
        );
        echo sprintf(
            '<label for="shipping_method_%1$d_%2$s" class="shipping-method__option-label" style="display: flex; justify-content: space-between; font-weight: 500">%3$s <div class="price" style="font-weight: 500">$10.00</div></label>',
            $package_index,
            esc_attr($custom_shipping),
            esc_html('Express Shipping')
        );
        echo '</li>';
    }
}
