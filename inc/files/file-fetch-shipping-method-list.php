<?php
// Add a button before the billing form on the WooCommerce checkout page
/*
add_action('woocommerce_checkout_after_customer_details', 'add_custom_button_before_billing_form');
function add_custom_button_before_billing_form() {
    echo '<div class="custom-checkout-button">';
    echo '<button type="button" class="check-shipping-rates-button" id="custom-button" onclick="fetchShippingRates()"><span class="loader display-none"></span><span class="button-text">Check Shipping Rates</span></button>';
    echo '</div>';
    
    // Modal structure
    ?>
<div id="shipping-modal" class="shipping-modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Available Shipping Rates</h2>
        <div id="api-response-container">
            <!-- Dynamic shipping methods will be rendered here -->
            <div class="couriers-grid"></div>
        </div>
    </div>
</div>
<?php
}
*/

/*
// Hook into the woocommerce_after_shipping_rate action to add custom shipping methods
add_action( 'woocommerce_after_shipping_rate', 'add_custom_shipping_methods', 10, 2 );

function add_custom_shipping_methods( $method, $package_index ) {
    
    if ( is_checkout() ) {
        $chosen_method = WC()->session->get( 'chosen_shipping_methods' )[ $package_index ];
        $first_custom_shipping = 'custom_shipping_flat_rate';  
        $second_custom_shipping = 'custom_shipping_express';  
        
        echo '<li class="shipping-method__option custom-shipping-method">';
        echo sprintf(
            '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%2$s" class="shipping_method" %3$s />',
            $package_index,
            esc_attr( $first_custom_shipping ),
            checked( $chosen_method, $first_custom_shipping, false )
        );
        echo sprintf(
            '<label for="shipping_method_%1$d_%2$s" class="shipping-method__option-label" style="display: flex; justify-content: space-between; font-weight: 500">YSL <div class="price" style="font-weight: 500">$25.00</div></label>',
            $package_index,
            esc_attr( $first_custom_shipping )
        );
        echo '</li>';

        
        echo '<li class="shipping-method__option custom-shipping-method">';
        echo sprintf(
            '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%2$s" class="shipping_method" %3$s />',
            $package_index,
            esc_attr( $second_custom_shipping ),
            checked( $chosen_method, $second_custom_shipping, false )
        );
        echo sprintf(
            '<label for="shipping_method_%1$d_%2$s" class="shipping-method__option-label" style="display: flex; justify-content: space-between; font-weight: 500">International Standard <div class="price" style="font-weight: 500">$35.00</div></label>',
            $package_index,
            esc_attr( $second_custom_shipping )
        );
        echo '</li>';
    }
}
*/
add_filter( 'woocommerce_checkout_fields', 'custom_require_checkout_fields' );

function custom_require_checkout_fields( $fields ) {
    // Make billing postcode (Zip Code) required
    $fields['billing']['billing_postcode']['required'] = true;

    // Make shipping postcode (Zip Code) required, if you want it required for shipping too
    $fields['shipping']['shipping_postcode']['required'] = true;

    return $fields;
}

add_filter( 'woocommerce_checkout_fields', 'customize_checkout_postcode_field' );

function customize_checkout_postcode_field( $fields ) {
    // For billing postcode field
    $fields['billing']['billing_postcode']['required'] = true;
    $fields['billing']['billing_postcode']['label'] = 'Postcode / ZIP <abbr class="required" title="required">*</abbr>';

    // For shipping postcode field
    $fields['shipping']['shipping_postcode']['required'] = true;
    $fields['shipping']['shipping_postcode']['label'] = 'Postcode / ZIP <abbr class="required" title="required">*</abbr>';

    return $fields;
}

