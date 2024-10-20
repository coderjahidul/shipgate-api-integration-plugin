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