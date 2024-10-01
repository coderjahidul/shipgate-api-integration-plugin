<?php
// get the WooCommerce shipping address for a customer

// Handle the AJAX request on the server side
add_action('wp_ajax_get_shipping_rates', 'get_shipping_rates');
add_action('wp_ajax_nopriv_get_shipping_rates', 'get_shipping_rates'); // If users are not logged in
function get_shipping_rates() {
    // get the shipping address for a customer
    $tocountrycode = $_POST['tocountrycode'];
$tocity = $_POST['tocity'];
$tostreet = $_POST['tostreet'];
$tozipcd = $_POST['tozipcd'];

// Constructing the URL using the variables
$url = "https://app.shipgate.io/api/v1/rates?weightGram=100&packagingType=box&lengthCm=1.0&heightCm=1.0&widthCm=1.0&skuCdList=TEST_01&skuCdList=TEST_02&toCountryCode={$tocountrycode}&toState=Ohio&toCity={$tocity}&toStreet={$tostreet}&toZipCd={$tozipcd}";

    
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'a0edd49c95e9399f45a9036d32b7171915f'
        )
    );
    
    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        echo json_encode(array('error' => $response->get_error_message()));
    } else {
        echo wp_remote_retrieve_body($response); // Ensure the response is JSON
    }

    wp_die(); // End the AJAX request
}