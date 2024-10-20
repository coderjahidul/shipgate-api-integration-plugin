<?php

/**
 * Load files for plugin which are not responsible for autoloading.
 *
 * @link              https://github.com/coderjahidul/shipgate-api-integration-plugin
 * @since             1.0.0
 * @package           shipgate-api-integration-plugin
 */

// include helper functions
include_once PLUGIN_BASE_PATH . '/inc/helpers/helper-functions.php';

// include file custom files
include_once PLUGIN_BASE_PATH . '/inc/files/file-custom-function.php';

// include file fetch shipping method list file
include_once PLUGIN_BASE_PATH . '/inc/files/file-fetch-shipping-method-list.php';

// include file ajax handle file
include_once PLUGIN_BASE_PATH . '/inc/files/file-ajax-handle.php';
