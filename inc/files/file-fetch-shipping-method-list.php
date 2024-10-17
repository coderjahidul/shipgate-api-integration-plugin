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

// Hook into the woocommerce_after_shipping_rate action to add custom shipping methods
add_action( 'woocommerce_after_shipping_rate', 'add_custom_shipping_methods', 10, 2 );

function add_custom_shipping_methods( $method, $package_index ) {
    // Ensure this function runs only on checkout page
    if ( is_checkout() ) {
        $chosen_method = WC()->session->get( 'chosen_shipping_methods' )[ $package_index ];
        $first_custom_shipping = 'custom_shipping_flat_rate';  // Custom shipping method 1
        $second_custom_shipping = 'custom_shipping_express';  // Custom shipping method 2
        
        // Custom Shipping Method 1
        echo '<li class="shipping-method__option custom-shipping-method">';
        echo sprintf(
            '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%2$s" class="shipping_method" %3$s />',
            $package_index,
            esc_attr( $first_custom_shipping ),
            checked( $chosen_method, $first_custom_shipping, false )
        );
        echo sprintf(
            '<label for="shipping_method_%1$d_%2$s" class="shipping-method__option-label" style="display: flex; justify-content: space-between; font-weight: 500">Flat Rate <div class="price" style="font-weight: 500">$5.00</div></label>',
            $package_index,
            esc_attr( $first_custom_shipping )
        );
        echo '</li>';

        // Custom Shipping Method 2
        echo '<li class="shipping-method__option custom-shipping-method">';
        echo sprintf(
            '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%2$s" class="shipping_method" %3$s />',
            $package_index,
            esc_attr( $second_custom_shipping ),
            checked( $chosen_method, $second_custom_shipping, false )
        );
        echo sprintf(
            '<label for="shipping_method_%1$d_%2$s" class="shipping-method__option-label" style="display: flex; justify-content: space-between; font-weight: 500">Express Shipping <div class="price" style="font-weight: 500">$10.00</div></label>',
            $package_index,
            esc_attr( $second_custom_shipping )
        );
        echo '</li>';
    }
}
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


// Add JavaScript to trigger the AJAX request and handle modal
add_action('wp_footer', 'custom_checkout_button_script');
function custom_checkout_button_script() {
    if (is_checkout()) { // Only include script on the checkout page
        ?>
        <script type="text/javascript">
            function fetchShippingRates() {
                // Serialize the checkout form data
                let ShippingData = jQuery('form.checkout').serializeArray();

                // Retrieve the shipping address details
                let shippingCountry = ShippingData.find(item => item.name === 'shipping_country')?.value;
                let shippingCity = ShippingData.find(item => item.name === 'shipping_city')?.value;
                let shippingStreet = ShippingData.find(item => item.name === 'shipping_address_1')?.value;
                let shippingZipcode = ShippingData.find(item => item.name === 'shipping_postcode')?.value;

                if (shippingCountry && shippingCity && shippingStreet && shippingZipcode) {
                    // Proceed with the AJAX call to fetch shipping rates
                    jQuery.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>", // WordPress AJAX URL
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'get_shipping_rates',
                            tocountrycode: shippingCountry,
                            tocity: shippingCity,
                            tostreet: shippingStreet,
                            tozipcd: shippingZipcode
                        },
                        success: function(response) {
                            console.log(response);
                            // let result =response.result;
                            // console.log(result);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', xhr, status, error); // Log any error
                        }
                    });
                } else {
                    alert('Please fill in all the required fields.');
                }
            }

            // Trigger the fetchShippingRates function when any shipping address field is changed
            jQuery(document).ready(function() {
                jQuery('form.checkout').on('change', 'input[name="shipping_postcode"]', function() {
                    fetchShippingRates();
                });
            });
        </script>

        <?php
    }
}