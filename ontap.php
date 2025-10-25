<?php
/**
 * OnTap - Brewery Taplist Plugin
 *
 * @package           OnTap
 * @author            OnTap
 * @copyright         2025 OnTap
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       OnTap - Brewery Taplist
 * Plugin URI:        https://ontapbrewery.com
 * Description:       Integrate with Untappd to display live taproom menus on your brewery website. Automatically sync your taplist and display what's on tap.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            OnTap
 * Author URI:        https://ontapbrewery.com
 * Text Domain:       ontap
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'ONTAP_VERSION', '1.0.1' );

/**
 * Plugin base paths and URLs
 */
define( 'ONTAP_PLUGIN_FILE', __FILE__ );
define( 'ONTAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ONTAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ONTAP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * PSR-4 Autoloader
 */
require_once ONTAP_PLUGIN_DIR . 'includes/class-autoloader.php';
OnTap\Autoloader::register();

/**
 * The code that runs during plugin activation.
 */
function activate_ontap() {
	require_once ONTAP_PLUGIN_DIR . 'includes/class-activator.php';
	OnTap\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ontap() {
	require_once ONTAP_PLUGIN_DIR . 'includes/class-deactivator.php';
	OnTap\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ontap' );
register_deactivation_hook( __FILE__, 'deactivate_ontap' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ontap() {
	$plugin = OnTap\Plugin::get_instance();
	$plugin->run();
}

run_ontap();
