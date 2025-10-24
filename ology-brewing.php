<?php
/**
 * Plugin Name: Ology Brewing
 * Plugin URI: https://ologybrewing.com
 * Description: Modern brewery management system with Untappd and Dropbox integration
 * Version: 1.0.0
 * Author: Ology Brewing
 * License: GPL v2 or later
 * Text Domain: ology-brewing
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
define('OLOGY_BREWING_VERSION', '1.0.0');
define('OLOGY_BREWING_PLUGIN_FILE', __FILE__);
define('OLOGY_BREWING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OLOGY_BREWING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OLOGY_BREWING_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once OLGY_BREWING_PLUGIN_DIR . 'includes/class-autoloader.php';

// Initialize plugin
add_action('plugins_loaded', function() {
    OlogyBrewing\OlogyBrewing::get_instance();
});
