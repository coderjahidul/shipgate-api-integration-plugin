<?php

/**
 * Plugin Name: Shipgate API Integration Plugin
 * Plugin URI:  https://github.com/coderjahidul/shipgate-api-integration-plugin
 * Author:      Jahidul Islam
 * Author URI:  https://github.com/coderjahidul
 * Description: Integrates with the Shipgate API to provide shipping functionality.
 * Version:     1.0.0
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: shipgate-api-integration-plugin
 * Domain Path: /languages
 */

defined( "ABSPATH" ) || exit( "Direct Access Not Allowed" );

// Define plugin base path
if ( !defined( 'PLUGIN_BASE_PATH' ) ) {
    define( 'PLUGIN_BASE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Define plugin base url
if ( !defined( 'PLUGIN_BASE_URL' ) ) {
    define( 'PLUGIN_BASE_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}

// Define admin assets dir path
if ( !defined( 'PLUGIN_ADMIN_ASSETS_DIR_PATH' ) ) {
    define( 'PLUGIN_ADMIN_ASSETS_DIR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) . '/assets/admin' ) );
}

// Define plugin admin assets url
if ( !defined( 'PLUGIN_ASSETS_DIR_URL' ) ) {
    define( 'PLUGIN_ASSETS_DIR_URL', untrailingslashit( plugin_dir_url( __FILE__ ) . '/assets/admin' ) );
}

// Define plugin public assets url
if ( !defined( 'PLUGIN_PUBLIC_ASSETS_URL' ) ) {
    define( 'PLUGIN_PUBLIC_ASSETS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) . '/assets/public' ) );
}

// Define plugin libs dir path
if ( !defined( 'PLUGIN_LIBS_DIR_PATH' ) ) {
    define( 'PLUGIN_LIBS_DIR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) . '/inc/libs' ) );
}

// Define plugin libs url
if ( !defined( 'PLUGIN_LIBS_DIR_URL' ) ) {
    define( 'PLUGIN_LIBS_DIR_URL', untrailingslashit( plugin_dir_url( __FILE__ ) . '/inc/libs' ) );
}

// Require files
require_once PLUGIN_BASE_PATH . '/loader.php';
require_once PLUGIN_BASE_PATH . '/inc/helpers/autoloader.php';

function my_plugin_enqueue_scripts() {
    // Enqueue the custom JS file
    wp_enqueue_script(
        'custom-js',
        plugin_dir_url( __FILE__ ) . '/assets/public/js/custom.js',
        array('jquery'), // Dependencies
        '1.0.0',         // Version
        true             // Load in footer
    );

    // Localize the script with AJAX URL
    wp_localize_script( 'custom-js', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_scripts');

/**
 * The code that runs during plugin activation.
 * This action is documented in inc/classes/class-plugin-activator.php file
 */
function wpb_plugin_activator() {
    require_once PLUGIN_BASE_PATH . '/inc/classes/class-plugin-activator.php';
    Plugin_Activator::activate();
}

// Register activation hook
register_activation_hook( __FILE__, 'wpb_plugin_activator' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in inc/classes/class-plugin-deactivator.php file
 */
function wpb_plugin_deactivator() {
    require_once PLUGIN_BASE_PATH . '/inc/classes/class-plugin-deactivator.php';
    Plugin_Deactivator::deactivate();
}

// Register deactivation hook
register_deactivation_hook( __FILE__, 'wpb_plugin_deactivator' );


function get_plugin_instance() {
    \BOILERPLATE\Inc\Autoloader::get_instance();
}

// Load plugin
get_plugin_instance();

