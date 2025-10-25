<?php
/**
 * Fired during plugin deactivation
 *
 * @package OnTap
 * @since   1.0.0
 */

namespace OnTap;

/**
 * Deactivator class
 */
class Deactivator {

	/**
	 * Run deactivation tasks
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Clear scheduled cron events
		$timestamp = wp_next_scheduled( 'ontap_sync_taplist' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'ontap_sync_taplist' );
		}

		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
