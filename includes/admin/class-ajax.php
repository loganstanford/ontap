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

		// Frontend AJAX handlers
		add_action( 'wp_ajax_ontap_get_beer_details', array( $this, 'handle_get_beer_details' ) );
		add_action( 'wp_ajax_nopriv_ontap_get_beer_details', array( $this, 'handle_get_beer_details' ) );
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

	/**
	 * Handle get beer details AJAX request (frontend)
	 *
	 * @return void
	 */
	public function handle_get_beer_details() {
		// Verify nonce
		check_ajax_referer( 'ontap_frontend_nonce', 'nonce' );

		// Get beer ID
		$beer_id = isset( $_POST['beer_id'] ) ? absint( $_POST['beer_id'] ) : 0;

		if ( ! $beer_id ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid beer ID.', 'ontap' ) )
			);
		}

		// Get beer post
		$beer = get_post( $beer_id );

		if ( ! $beer || 'ontap_beer' !== $beer->post_type ) {
			wp_send_json_error(
				array( 'message' => __( 'Beer not found.', 'ontap' ) )
			);
		}

		// Get beer meta
		$abv         = get_post_meta( $beer_id, 'abv', true );
		$ibu         = get_post_meta( $beer_id, 'ibu', true );
		$description = get_post_meta( $beer_id, 'description', true );
		$label_url   = get_post_meta( $beer_id, 'label_url', true );
		$brewery     = get_post_meta( $beer_id, 'brewery_name', true );

		// Get styles
		$styles = wp_get_post_terms( $beer_id, 'beer_style' );
		$style_names = ! empty( $styles ) && ! is_wp_error( $styles ) ? wp_list_pluck( $styles, 'name' ) : array();

		// Get taproom
		$taprooms = wp_get_post_terms( $beer_id, 'taproom' );
		$taproom_names = ! empty( $taprooms ) && ! is_wp_error( $taprooms ) ? wp_list_pluck( $taprooms, 'name' ) : array();

		// Get containers from taplist
		global $wpdb;
		$taplist_table = $wpdb->prefix . 'ontap_taplist';

		$taplist_item = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$taplist_table} WHERE beer_id = %d AND is_available = 1 LIMIT 1",
				$beer_id
			)
		);

		$containers = array();
		if ( $taplist_item ) {
			$containers = \OnTap\Container::get_containers( $taplist_item->id, true );
		}

		// Build HTML
		ob_start();
		?>
		<div class="ontap-modal-beer-details">
			<?php if ( ! empty( $label_url ) ) : ?>
				<div class="ontap-modal-image">
					<img src="<?php echo esc_url( $label_url ); ?>" alt="<?php echo esc_attr( $beer->post_title ); ?>" />
				</div>
			<?php endif; ?>

			<div class="ontap-modal-info">
				<h2 class="ontap-modal-title"><?php echo esc_html( $beer->post_title ); ?></h2>

				<?php if ( ! empty( $brewery ) ) : ?>
					<p class="ontap-modal-brewery"><strong><?php esc_html_e( 'Brewery:', 'ontap' ); ?></strong> <?php echo esc_html( $brewery ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $style_names ) ) : ?>
					<p class="ontap-modal-style"><strong><?php esc_html_e( 'Style:', 'ontap' ); ?></strong> <?php echo esc_html( implode( ' > ', $style_names ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $taproom_names ) ) : ?>
					<p class="ontap-modal-taproom"><strong><?php esc_html_e( 'Taproom:', 'ontap' ); ?></strong> <?php echo esc_html( implode( ', ', $taproom_names ) ); ?></p>
				<?php endif; ?>

				<div class="ontap-modal-stats">
					<?php if ( ! empty( $abv ) ) : ?>
						<div class="ontap-modal-stat">
							<span class="ontap-stat-label"><?php esc_html_e( 'ABV', 'ontap' ); ?></span>
							<span class="ontap-stat-value"><?php echo esc_html( $abv ); ?>%</span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $ibu ) ) : ?>
						<div class="ontap-modal-stat">
							<span class="ontap-stat-label"><?php esc_html_e( 'IBU', 'ontap' ); ?></span>
							<span class="ontap-stat-value"><?php echo esc_html( $ibu ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $description ) ) : ?>
					<div class="ontap-modal-description">
						<h3><?php esc_html_e( 'Description', 'ontap' ); ?></h3>
						<?php echo wp_kses_post( wpautop( $description ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $containers ) ) : ?>
					<div class="ontap-modal-containers">
						<h3><?php esc_html_e( 'Available Sizes', 'ontap' ); ?></h3>
						<div class="ontap-container-list">
							<?php foreach ( $containers as $container ) : ?>
								<span class="ontap-container">
									<?php echo esc_html( \OnTap\Container::get_display_label( $container ) ); ?>
								</span>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
