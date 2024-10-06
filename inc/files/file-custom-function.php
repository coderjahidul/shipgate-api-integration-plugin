<?php
// Hook to admin_menu to add the submenu page
add_action('admin_menu', 'shipgate_api_settings_submenu');

function shipgate_api_settings_submenu() {
    // Add submenu under the "Settings" menu
    add_submenu_page(
        'options-general.php',         // Parent slug
        'Shipgate API Settings',          // Page title
        'Shipgate API',             // Menu title
        'manage_options',              // Capability required to access the menu
        'shipgate-api-settings',          // Menu slug
        'shipgate_api_settings_page'      // Callback function to render the page content
    );
}

// Callback function to display the content of the settings page
function shipgate_api_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Shipgate API Settings', 'textdomain' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            // Output security fields for the registered setting
            settings_fields( 'shipgate_api_settings_group' );
            // Output setting sections and fields
            do_settings_sections( 'shipgate-api-settings' );
            // Submit button
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Hook to admin_init to register the settings
add_action('admin_init', 'shipgate_api_settings_init');

function shipgate_api_settings_init() {
    // Register a new setting for the custom settings page
    register_setting( 'shipgate_api_settings_group', 'shipgate_api_key' );

    // Add a new section to the custom settings page
    add_settings_section(
        'shipgate_api_settings_section',            // Section ID
        'Shipgate API Integration',                 // Section title
        'shipgate_api_settings_section_callback',   // Section callback
        'shipgate-api-settings'                     // Page slug (matches menu slug)
    );

    // Add a field to the custom settings section
    add_settings_field(
        'shipgate_api_setting_field',               // Field ID
        'Shipgate API Key',                         // Field title
        'shipgate_api_setting_field_callback',      // Field callback
        'shipgate-api-settings',                    // Page slug (matches menu slug)
        'shipgate_api_settings_section'             // Section ID
    );
}


// Callback for the section description (optional)
function shipgate_api_settings_section_callback() {
    echo '<p>' . esc_html__( 'Enter your Shipgate API key:', 'textdomain' ) . '</p>';
}

// Callback for the custom setting field
function shipgate_api_setting_field_callback() {
    $option = get_option( 'shipgate_api_key' );
    ?>
    <input type="password" placeholder="Enter your Shipgate API key" name="shipgate_api_key" value="<?php echo esc_attr( $option ); ?>" />
    <?php
}

// Add multiple custom fields to the WooCommerce General settings
add_filter('woocommerce_general_settings', 'add_custom_contact_fields');

function add_custom_contact_fields($settings) {
    // Array of new fields
    $new_settings = array(
        array(
            'name'     => __('Name', 'woocommerce'),
            'desc'     => __('Enter your name here.', 'woocommerce'),
            'id'       => 'custom_name',
            'type'     => 'text',
            'css'      => 'min-width:300px;',
            'default'  => '',
            'desc_tip' => true,
        ),
        array(
            'name'     => __('Company', 'woocommerce'),
            'desc'     => __('Enter your company name here.', 'woocommerce'),
            'id'       => 'custom_company',
            'type'     => 'text',
            'css'      => 'min-width:300px;',
            'default'  => '',
            'desc_tip' => true,
        ),
        array(
            'name'     => __('Business Number', 'woocommerce'),
            'desc'     => __('Enter your business number here.', 'woocommerce'),
            'id'       => 'custom_business_number',
            'type'     => 'text',
            'css'      => 'min-width:300px;',
            'default'  => '',
            'desc_tip' => true,
        ),
        array(
            'name'     => __('Email', 'woocommerce'),
            'desc'     => __('Enter your email address here.', 'woocommerce'),
            'id'       => 'custom_email',
            'type'     => 'email',
            'css'      => 'min-width:300px;',
            'default'  => '',
            'desc_tip' => true,
        ),
        array(
            'name'     => __('Phone Number', 'woocommerce'),
            'desc'     => __('Enter your primary phone number here.', 'woocommerce'),
            'id'       => 'custom_phone_num1',
            'type'     => 'text',
            'css'      => 'min-width:300px;',
            'default'  => '',
            'desc_tip' => true,
        )
    );

    // Insert the new fields before the Address 1 field
    foreach ($settings as $key => $setting) {
        if (isset($setting['id']) && $setting['id'] === 'woocommerce_store_address') {
            array_splice($settings, $key, 0, $new_settings);
            break; // Stop the loop after inserting the fields
        }
    }

    return $settings;
}

// Save the custom contact fields
add_action('woocommerce_update_options_general', 'save_custom_contact_fields');

function save_custom_contact_fields() {
    // Array of field IDs
    $fields = array('custom_name', 'custom_company', 'custom_business_number', 'custom_email', 'custom_phone_num1');

    foreach ($fields as $field) {
        $value = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : '';
        update_option($field, $value);
    }
}
