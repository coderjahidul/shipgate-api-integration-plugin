<?php
// get the WooCommerce shipping address for a customer

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

    // Retrieve the response body and status code
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    // Log the response for debugging purposes
    // put_program_logs("Shipgate API response: " . $body);

    // Check if the API returned a valid response
    if ($status_code !== 200) {
        echo json_encode(array('error' => "API returned status code: " . $status_code));
    } else {
        // Return the API response back to the AJAX request
        echo $body; // Assuming the response is a valid JSON string
    }

    wp_die(); // End the AJAX request
}