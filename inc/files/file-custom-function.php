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

