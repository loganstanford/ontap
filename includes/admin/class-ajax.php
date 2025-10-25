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
use OnTap\Debug_Logger;
use OnTap\Taplist;

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
		add_action( 'wp_ajax_ontap_get_debug_logs', array( $this, 'handle_get_debug_logs' ) );
		add_action( 'wp_ajax_ontap_clear_debug_logs', array( $this, 'handle_clear_debug_logs' ) );
		add_action( 'wp_ajax_ontap_update_availability', array( $this, 'handle_update_availability' ) );
		add_action( 'wp_ajax_ontap_update_tap_number', array( $this, 'handle_update_tap_number' ) );
		add_action( 'wp_ajax_ontap_remove_from_taplist', array( $this, 'handle_remove_from_taplist' ) );
		add_action( 'wp_ajax_ontap_bulk_action', array( $this, 'handle_bulk_action' ) );
		add_action( 'wp_ajax_ontap_save_tap_order', array( $this, 'handle_save_tap_order' ) );
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

	/**
	 * Handle get debug logs AJAX request
	 *
	 * @return void
	 */
	public function handle_get_debug_logs() {
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_ontap_settings' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to view debug logs.', 'ontap' ) )
			);
		}

		$html = Debug_Logger::get_formatted_logs( 100 );

		wp_send_json_success(
			array( 'html' => $html )
		);
	}

	/**
	 * Handle clear debug logs AJAX request
	 *
	 * @return void
	 */
	public function handle_clear_debug_logs() {
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_ontap_settings' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to clear debug logs.', 'ontap' ) )
			);
		}

		Debug_Logger::clear_logs();

		wp_send_json_success(
			array( 'message' => __( 'Debug logs cleared successfully.', 'ontap' ) )
		);
	}

	/**
	 * Handle update availability AJAX request
	 *
	 * @return void
	 */
	public function handle_update_availability() {
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to update availability.', 'ontap' ) )
			);
		}

		$item_id      = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
		$is_available = isset( $_POST['is_available'] ) ? (bool) $_POST['is_available'] : false;

		if ( empty( $item_id ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid item ID.', 'ontap' ) )
			);
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ontap_taplist';

		// Update availability
		$updated = $wpdb->update(
			$table,
			array( 'is_available' => $is_available ? 1 : 0 ),
			array( 'id' => $item_id ),
			array( '%d' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			wp_send_json_error(
				array( 'message' => __( 'Failed to update availability.', 'ontap' ) )
			);
		}

		// Mark as manually overridden
		$item = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $item_id )
		);

		if ( $item ) {
			update_post_meta( $item->beer_id, '_ontap_manual_override_' . $item->taproom_id, current_time( 'mysql' ) );
		}

		wp_send_json_success(
			array( 'message' => __( 'Availability updated successfully.', 'ontap' ) )
		);
	}

	/**
	 * Handle update tap number AJAX request
	 *
	 * @return void
	 */
	public function handle_update_tap_number() {
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to update tap numbers.', 'ontap' ) )
			);
		}

		$item_id    = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
		$tap_number = isset( $_POST['tap_number'] ) ? absint( $_POST['tap_number'] ) : null;

		if ( empty( $item_id ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid item ID.', 'ontap' ) )
			);
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ontap_taplist';

		$updated = $wpdb->update(
			$table,
			array( 'tap_number' => $tap_number ),
			array( 'id' => $item_id ),
			array( '%d' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			wp_send_json_error(
				array( 'message' => __( 'Failed to update tap number.', 'ontap' ) )
			);
		}

		wp_send_json_success(
			array( 'message' => __( 'Tap number updated successfully.', 'ontap' ) )
		);
	}

	/**
	 * Handle remove from taplist AJAX request
	 *
	 * @return void
	 */
	public function handle_remove_from_taplist() {
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to remove items.', 'ontap' ) )
			);
		}

		$item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;

		if ( empty( $item_id ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid item ID.', 'ontap' ) )
			);
		}

		global $wpdb;
		$taplist_table   = $wpdb->prefix . 'ontap_taplist';
		$containers_table = $wpdb->prefix . 'ontap_containers';

		// Delete containers first
		$wpdb->delete(
			$containers_table,
			array( 'taplist_id' => $item_id ),
			array( '%d' )
		);

		// Delete taplist item
		$deleted = $wpdb->delete(
			$taplist_table,
			array( 'id' => $item_id ),
			array( '%d' )
		);

		if ( false === $deleted ) {
			wp_send_json_error(
				array( 'message' => __( 'Failed to remove item from taplist.', 'ontap' ) )
			);
		}

		wp_send_json_success(
			array( 'message' => __( 'Item removed from taplist successfully.', 'ontap' ) )
		);
	}

	/**
	 * Handle bulk action AJAX request
	 *
	 * @return void
	 */
	public function handle_bulk_action() {
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to perform bulk actions.', 'ontap' ) )
			);
		}

		$action   = isset( $_POST['bulk_action'] ) ? sanitize_text_field( $_POST['bulk_action'] ) : '';
		$item_ids = isset( $_POST['item_ids'] ) ? array_map( 'absint', $_POST['item_ids'] ) : array();

		if ( empty( $action ) || empty( $item_ids ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid bulk action or no items selected.', 'ontap' ) )
			);
		}

		global $wpdb;
		$taplist_table    = $wpdb->prefix . 'ontap_taplist';
		$containers_table = $wpdb->prefix . 'ontap_containers';

		$count = 0;

		switch ( $action ) {
			case 'enable':
				foreach ( $item_ids as $item_id ) {
					$wpdb->update(
						$taplist_table,
						array( 'is_available' => 1 ),
						array( 'id' => $item_id ),
						array( '%d' ),
						array( '%d' )
					);
					$count++;
				}
				$message = sprintf( __( '%d items made available.', 'ontap' ), $count );
				break;

			case 'disable':
				foreach ( $item_ids as $item_id ) {
					$wpdb->update(
						$taplist_table,
						array( 'is_available' => 0 ),
						array( 'id' => $item_id ),
						array( '%d' ),
						array( '%d' )
					);
					$count++;
				}
				$message = sprintf( __( '%d items made unavailable.', 'ontap' ), $count );
				break;

			case 'delete':
				foreach ( $item_ids as $item_id ) {
					// Delete containers
					$wpdb->delete(
						$containers_table,
						array( 'taplist_id' => $item_id ),
						array( '%d' )
					);
					// Delete taplist item
					$wpdb->delete(
						$taplist_table,
						array( 'id' => $item_id ),
						array( '%d' )
					);
					$count++;
				}
				$message = sprintf( __( '%d items removed from taplist.', 'ontap' ), $count );
				break;

			default:
				wp_send_json_error(
					array( 'message' => __( 'Invalid bulk action.', 'ontap' ) )
				);
		}

		wp_send_json_success(
			array( 'message' => $message )
		);
	}

	/**
	 * Handle save tap order AJAX request
	 *
	 * @return void
	 */
	public function handle_save_tap_order() {
		check_ajax_referer( 'ontap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to save tap order.', 'ontap' ) )
			);
		}

		$order = isset( $_POST['order'] ) ? json_decode( stripslashes( $_POST['order'] ), true ) : array();

		if ( empty( $order ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid order data.', 'ontap' ) )
			);
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ontap_taplist';

		foreach ( $order as $position => $item_id ) {
			$wpdb->update(
				$table,
				array( 'tap_number' => $position + 1 ),
				array( 'id' => absint( $item_id ) ),
				array( '%d' ),
				array( '%d' )
			);
		}

		wp_send_json_success(
			array( 'message' => __( 'Tap order saved successfully.', 'ontap' ) )
		);
	}
}
