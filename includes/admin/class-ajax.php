<?php
/**
 * AJAX handlers for admin
 *
 * @package OnTap\Admin
 * @since   1.0.0
 */

namespace OnTap\Admin;

use OnTap\API\Untappd_Client;
use OnTap\API\Sync_Manager;

/**
 * Ajax class
 */
class Ajax {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_ontap_manual_sync', array( $this, 'handle_manual_sync' ) );
		add_action( 'wp_ajax_ontap_test_connection', array( $this, 'handle_test_connection' ) );
	}

	/**
	 * Handle manual sync AJAX request
	 *
	 * @return void
	 */
	public function handle_manual_sync() {
		// Verify nonce
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		// Check permissions
		if ( ! current_user_can( 'sync_ontap_taplist' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to sync taplists.', 'ontap' ) )
			);
		}

		// Initialize API client and sync manager
		$client  = new Untappd_Client();
		$manager = new Sync_Manager( $client );

		// Perform sync
		$results = $manager->sync_all();

		// Send response
		if ( $results['success'] ) {
			wp_send_json_success( $results );
		} else {
			wp_send_json_error( $results );
		}
	}

	/**
	 * Handle test connection AJAX request
	 *
	 * @return void
	 */
	public function handle_test_connection() {
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_ontap_settings' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to test the connection.', 'ontap' ) )
			);
		}

		$client = new Untappd_Client();
		$result = $client->test_connection();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array( 'message' => $result->get_error_message() )
			);
		}

		wp_send_json_success(
			array( 'message' => __( 'Connection successful!', 'ontap' ) )
		);
	}
}
