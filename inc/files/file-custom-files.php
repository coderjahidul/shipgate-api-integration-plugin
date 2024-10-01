<?php
// Add a button before the billing form on the WooCommerce checkout page
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

// Add JavaScript to trigger the AJAX request and handle modal
add_action('wp_footer', 'custom_checkout_button_script');
function custom_checkout_button_script() {
    if (is_checkout()) { // Only include script on the checkout page
        ?>
<script type="text/javascript">
    function fetchShippingRates() {
        // add class to button
        jQuery('.button-text').addClass('display-none');
        jQuery('.loader').addClass('display-block');

        // Serialize the billing form data and log it to the console
        let billingData = jQuery('form.checkout').serializeArray();
        console.log('Billing Data:', billingData);
        let billingCountry = billingData.find(item => item.name === 'billing_country').value;
        let billingCity = billingData.find(item => item.name === 'billing_city').value;
        let billingStreet = billingData.find(item => item.name === 'billing_address_1').value;
        let billingZipcode = billingData.find(item => item.name === 'billing_postcode').value;
        console.log('Billing Country:', billingCountry);
        console.log('Billing City:', billingCity);
        console.log('Billing Street:', billingStreet);
        console.log('Billing Zipcode:', billingZipcode);


        // Proceed with the AJAX call to fetch shipping rates (optional, if needed)
        jQuery.ajax({
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            type: 'POST',
            dataType: 'json', // Expect JSON response from the server
            data: {
                action: 'get_shipping_rates',
                tocountrycode: billingCountry,
                tocity: billingCity,
                tostreet: billingStreet,
                tozipcd: billingZipcode
            },
            success: function(response) {
                if (response.error) {
                    alert('Error: ' + response.error);
                } else {
                    // Render the shipping rates inside the modal
                    renderCourierData(response);
                    showModal();
                }
                // remove class to button
                jQuery('.button-text').removeClass('display-none');
                jQuery('.loader').removeClass('display-block');
            },
            error: function(error) {
                console.error('Error:', error); // Log any error
                alert('Failed to retrieve shipping rates.');
            }
        });
    }

    // Function to render the shipping rates data (if necessary)
    function renderCourierData(response) {
        let couriersGrid = jQuery('.couriers-grid');

        // Empty the grid before rendering new data
        couriersGrid.empty();

        // Iterate over the response result
        response.result.forEach(function (courierData) {
            // Create a new courier div
            let courierDiv = jQuery('<div class="courier"></div>');

            // Add Courier Name
            courierDiv.append('<span class="courier-name">Carrier: ' + courierData.carrier + '</span><br>');
            courierDiv.append('<span class="courier-service">Service: ' + courierData.service + '</span><br>');

            // Add Emergency Situation amount
            let emergencySituation = courierData.amountList.find(item => item.type === "Emergency Situation");
            courierDiv.append('<span class="courier-title">Emergency Situation: ' + (emergencySituation ? emergencySituation.amount : 'Not Available') + '</span><br>');

            // Add Export Declaration amount
            let exportDeclaration = courierData.amountList.find(item => item.type === "Export Declaration");
            courierDiv.append('<span class="courier-title">Export Declaration: ' + (exportDeclaration ? exportDeclaration.amount : 'Not Available') + '</span><br>');

            // Add Shipping amount
            let shipping = courierData.amountList.find(item => item.type === "Shipping");
            courierDiv.append('<span class="courier-title">Shipping: ' + (shipping ? shipping.amount : 'Not Available') + '</span><br>');

            // Append the courier div to the grid
            couriersGrid.append(courierDiv);
        });
    }

    // Show the modal (if necessary)
    function showModal() {
        let modal = jQuery('#shipping-modal');
        modal.show();
    }

    // Close the modal
    jQuery(document).on('click', '.close-modal', function() {
        jQuery('#shipping-modal').hide();
    });

    // Close the modal if the user clicks anywhere outside the modal
    jQuery(window).on('click', function(event) {
        if (jQuery(event.target).is('#shipping-modal')) {
            jQuery('#shipping-modal').hide();
        }
    });
</script>

<?php
    }
}


