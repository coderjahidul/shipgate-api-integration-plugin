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
            url: ajax_object.ajaxurl, // WordPress AJAX URL
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_shipping_rates',
                tocountrycode: shippingCountry,
                tocity: shippingCity,
                tostreet: shippingStreet,
                tozipcd: shippingZipcode
            },
            beforeSend: function() {
                // Show loading indicator (optional)
                jQuery('#shipping_method').html('<p>Fetching shipping rates...</p>');
            },
            success: function(response) {
                if (response.success && response.data.shipping_options.length > 0) {
                    var shippingList = '';

                    // Loop through the shipping options and create HTML for each
                    response.data.shipping_options.forEach(function(option) {
                        var price = (option.amount / 100).toFixed(2); // Convert to proper currency format

                        shippingList += '<li class="shipping-method__option custom-shipping-method">';
                        shippingList += '<input type="radio" name="shipping_method[0]" id="shipping_method_0_' + option.id + '" value="' + option.id + '" class="shipping_method" />';
                        shippingList += '<label for="shipping_method_0_' + option.id + '" class="shipping-method__option-label" style="display: flex; justify-content: space-between;">' + option.label + '<span class="price">$' + price + '</span></label>';
                        shippingList += '</li>';
                    });

                    // Insert the shipping options into the shipping methods container
                    jQuery('#shipping_method').html(shippingList);
                    
                    // Recalculate totals after changing the shipping method
                    jQuery('body').trigger('update_checkout');
                } else {
                    // Handle case when no shipping options are returned
                    jQuery('#shipping_method').html('<p>No shipping options available for the given address.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr, status, error); // Log any error
                jQuery('#shipping_method').html('<p>Error fetching shipping rates. Please try again.</p>');
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
