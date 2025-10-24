<?php
/**
 * Plugin Name: OnTap - Brewery Management
 * Plugin URI: https://ontapbrewing.com
 * Description: Modern brewery management system with Untappd and Dropbox integration
 * Version: 1.0.0
 * Author: OnScript Tech, LLC
 * License: GPL v2 or later
 * Text Domain: ontap
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ONTAP_VERSION', '1.0.0');
define('ONTAP_PLUGIN_FILE', __FILE__);
define('ONTAP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ONTAP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ONTAP_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once ONTAP_PLUGIN_DIR . 'includes/autoloader.php';

// Initialize plugin
add_action('plugins_loaded', function() {
    OnTap\OnTap::get_instance();
});
