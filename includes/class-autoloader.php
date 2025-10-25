<?php
/**
 * PSR-4 Autoloader for OnTap Plugin
 *
 * @package OnTap
 * @since   1.0.0
 */

namespace OnTap;

/**
 * Autoloader class
 */
class Autoloader {

	/**
	 * Register the autoloader
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload classes
	 *
	 * @param string $class The fully-qualified class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		// Project-specific namespace prefix.
		$prefix = 'OnTap\\';

		// Base directory for the namespace prefix.
		$base_dir = ONTAP_PLUGIN_DIR . 'includes/';

		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// No, move to the next registered autoloader.
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class, $len );

		// Replace namespace separators with directory separators.
		// Replace underscores with hyphens.
		// Prepend with 'class-' and append '.php'.
		$file = $base_dir . str_replace( '\\', '/', $relative_class );
		$file = strtolower( str_replace( '_', '-', $file ) );

		// Convert class name to file name.
		$parts = explode( '/', $file );
		$class_name = array_pop( $parts );
		$class_name = 'class-' . $class_name . '.php';
		$file = implode( '/', $parts ) . '/' . $class_name;

		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
}
