<?php
/**
 * Taplist Manager - Admin UI for managing taproom taplists
 *
 * @package OnTap\Admin
 * @since   1.0.0
 */

namespace OnTap\Admin;

use OnTap\Taplist;
use OnTap\Container;

/**
 * Taplist Manager class
 */
class Taplist_Manager {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Constructor hooks are registered in Plugin class
	}

	/**
	 * Add taplist management submenu page
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_submenu_page(
			'ontap-settings',
			__( 'Manage Taplist', 'ontap' ),
			__( 'Manage Taplist', 'ontap' ),
			'edit_posts',
			'ontap-manage-taplist',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the taplist management page
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Get selected taproom (default to first taproom)
		$selected_taproom = isset( $_GET['taproom'] ) ? absint( $_GET['taproom'] ) : 0;

		// Get all taprooms
		$taprooms = get_terms(
			array(
				'taxonomy'   => 'ontap_taproom',
				'hide_empty' => false,
			)
		);

		if ( empty( $taprooms ) || is_wp_error( $taprooms ) ) {
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Manage Taplist', 'ontap' ); ?></h1>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'No taprooms found. Please add a taproom and assign it an Untappd menu ID first.', 'ontap' ); ?></p>
				</div>
			</div>
			<?php
			return;
		}

		// Set default taproom if none selected
		if ( empty( $selected_taproom ) ) {
			$selected_taproom = $taprooms[0]->term_id;
		}

		// Get taplist items for selected taproom
		$taplist_items = $this->get_taplist_items( $selected_taproom );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Manage Taplist', 'ontap' ); ?></h1>

			<!-- Taproom Selector -->
			<div class="ontap-taproom-selector" style="margin: 20px 0;">
				<label for="taproom-select" style="font-weight: 600; margin-right: 10px;">
					<?php esc_html_e( 'Select Taproom:', 'ontap' ); ?>
				</label>
				<select id="taproom-select" name="taproom" onchange="window.location.href='?page=ontap-manage-taplist&taproom=' + this.value">
					<?php foreach ( $taprooms as $taproom ) : ?>
						<option value="<?php echo esc_attr( $taproom->term_id ); ?>" <?php selected( $selected_taproom, $taproom->term_id ); ?>>
							<?php echo esc_html( $taproom->name ); ?>
							(<?php echo esc_html( Taplist::count_beers( $taproom->term_id ) ); ?> beers)
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Bulk Actions and Sorting -->
			<div class="tablenav top">
				<div class="alignleft actions bulkactions">
					<select id="bulk-action-selector-top">
						<option value="-1"><?php esc_html_e( 'Bulk Actions', 'ontap' ); ?></option>
						<option value="enable"><?php esc_html_e( 'Make Available', 'ontap' ); ?></option>
						<option value="disable"><?php esc_html_e( 'Make Unavailable', 'ontap' ); ?></option>
						<option value="delete"><?php esc_html_e( 'Remove from Taplist', 'ontap' ); ?></option>
					</select>
					<button type="button" id="doaction" class="button action">
						<?php esc_html_e( 'Apply', 'ontap' ); ?>
					</button>
				</div>
				<div class="alignleft actions" style="margin-left: 10px;">
					<label for="sort-by" style="margin-right: 5px;">
						<?php esc_html_e( 'Sort by:', 'ontap' ); ?>
					</label>
					<select id="sort-by" name="sort_by">
						<option value="tap_number" <?php selected( isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : 'tap_number', 'tap_number' ); ?>>
							<?php esc_html_e( 'Tap Number', 'ontap' ); ?>
						</option>
						<option value="name" <?php selected( isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : '', 'name' ); ?>>
							<?php esc_html_e( 'Beer Name (A-Z)', 'ontap' ); ?>
						</option>
						<option value="name_desc" <?php selected( isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : '', 'name_desc' ); ?>>
							<?php esc_html_e( 'Beer Name (Z-A)', 'ontap' ); ?>
						</option>
						<option value="style" <?php selected( isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : '', 'style' ); ?>>
							<?php esc_html_e( 'Style', 'ontap' ); ?>
						</option>
						<option value="abv_asc" <?php selected( isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : '', 'abv_asc' ); ?>>
							<?php esc_html_e( 'ABV (Low to High)', 'ontap' ); ?>
						</option>
						<option value="abv_desc" <?php selected( isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : '', 'abv_desc' ); ?>>
							<?php esc_html_e( 'ABV (High to Low)', 'ontap' ); ?>
						</option>
						<option value="date_added" <?php selected( isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : '', 'date_added' ); ?>>
							<?php esc_html_e( 'Date Added (Newest)', 'ontap' ); ?>
						</option>
						<option value="date_added_asc" <?php selected( isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : '', 'date_added_asc' ); ?>>
							<?php esc_html_e( 'Date Added (Oldest)', 'ontap' ); ?>
						</option>
					</select>
				</div>
				<div class="alignright actions">
					<button type="button" id="save-tap-order" class="button button-primary">
						<?php esc_html_e( 'Save Order', 'ontap' ); ?>
					</button>
				</div>
			</div>

			<!-- Taplist Table -->
			<table class="wp-list-table widefat fixed striped" id="taplist-table">
				<thead>
					<tr>
						<th class="check-column">
							<input type="checkbox" id="select-all-beers">
						</th>
						<th style="width: 40px;"><?php esc_html_e( 'Order', 'ontap' ); ?></th>
						<th><?php esc_html_e( 'Beer', 'ontap' ); ?></th>
						<th><?php esc_html_e( 'Style', 'ontap' ); ?></th>
						<th><?php esc_html_e( 'ABV', 'ontap' ); ?></th>
						<th><?php esc_html_e( 'Containers', 'ontap' ); ?></th>
						<th><?php esc_html_e( 'Tap #', 'ontap' ); ?></th>
						<th><?php esc_html_e( 'Status', 'ontap' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'ontap' ); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php if ( empty( $taplist_items ) ) : ?>
						<tr>
							<td colspan="9" style="text-align: center; padding: 40px;">
								<?php esc_html_e( 'No beers on tap. Run a sync to populate the taplist.', 'ontap' ); ?>
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $taplist_items as $item ) : ?>
							<?php $this->render_taplist_row( $item ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<!-- Statistics -->
			<?php if ( ! empty( $taplist_items ) ) : ?>
				<div class="ontap-stats" style="margin-top: 20px; padding: 15px; background: #f5f5f5; border: 1px solid #ddd;">
					<strong><?php esc_html_e( 'Statistics:', 'ontap' ); ?></strong>
					<?php
					$available_count = count( array_filter( $taplist_items, function( $item ) {
						return $item->is_available;
					} ) );
					?>
					<?php echo esc_html( sprintf( __( '%d beers total, %d available, %d unavailable', 'ontap' ), count( $taplist_items ), $available_count, count( $taplist_items ) - $available_count ) ); ?>
				</div>

				<?php
				$current_sort = isset( $_GET['sort_by'] ) ? $_GET['sort_by'] : 'tap_number';
				if ( 'tap_number' !== $current_sort ) :
					?>
					<div class="notice notice-info inline" style="margin-top: 15px;">
						<p>
							<strong><?php esc_html_e( 'Note:', 'ontap' ); ?></strong>
							<?php esc_html_e( 'Drag-and-drop reordering is only available when sorting by "Tap Number". Change the sort option to "Tap Number" to reorder beers manually.', 'ontap' ); ?>
						</p>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a single taplist row
	 *
	 * @param object $item Taplist item.
	 * @return void
	 */
	private function render_taplist_row( $item ) {
		$beer_post = get_post( $item->beer_id );
		$beer_meta = get_post_meta( $item->beer_id );
		$styles    = wp_get_post_terms( $item->beer_id, 'ontap_style' );
		$containers = Container::get_containers( $item->id );

		// Build style display (parent > child hierarchy)
		$style_display = '';
		if ( ! empty( $styles ) && ! is_wp_error( $styles ) ) {
			// Separate parent and child terms
			$parent_terms = array();
			$child_terms  = array();

			foreach ( $styles as $term ) {
				if ( 0 === $term->parent ) {
					$parent_terms[] = $term;
				} else {
					$child_terms[] = $term;
				}
			}

			// Build hierarchy: parent > child
			$style_parts = array();

			// Add parent terms first
			foreach ( $parent_terms as $parent ) {
				$style_parts[] = $parent->name;
			}

			// Add child terms
			foreach ( $child_terms as $child ) {
				$style_parts[] = $child->name;
			}

			$style_display = implode( ' > ', $style_parts );
		}

		// Build containers display
		$containers_display = '';
		if ( ! empty( $containers ) ) {
			$container_strings = array();
			foreach ( $containers as $container ) {
				$container_strings[] = sprintf(
					'%s (%s)',
					esc_html( $container->size ),
					Container::format_price( $container->price )
				);
			}
			$containers_display = implode( ', ', $container_strings );
		}

		$is_available      = (bool) $item->is_available;
		$manual_override   = get_post_meta( $item->beer_id, '_ontap_manual_override_' . $item->taproom_id, true );
		$has_manual_override = ! empty( $manual_override );
		?>
		<tr class="taplist-item <?php echo $is_available ? '' : 'unavailable'; ?>"
			data-item-id="<?php echo esc_attr( $item->id ); ?>"
			data-beer-id="<?php echo esc_attr( $item->beer_id ); ?>"
			data-taproom-id="<?php echo esc_attr( $item->taproom_id ); ?>">

			<th class="check-column">
				<input type="checkbox" class="beer-checkbox" value="<?php echo esc_attr( $item->id ); ?>">
			</th>

			<td class="handle" style="cursor: move; text-align: center;">
				<span class="dashicons dashicons-menu"></span>
			</td>

			<td class="beer-name">
				<strong>
					<a href="<?php echo esc_url( get_edit_post_link( $item->beer_id ) ); ?>">
						<?php echo esc_html( $beer_post->post_title ); ?>
					</a>
				</strong>
				<div class="row-actions">
					<span class="edit">
						<a href="<?php echo esc_url( get_edit_post_link( $item->beer_id ) ); ?>">
							<?php esc_html_e( 'Edit', 'ontap' ); ?>
						</a>
					</span>
				</div>
			</td>

			<td><?php echo esc_html( $style_display ); ?></td>

			<td><?php echo esc_html( isset( $beer_meta['abv'][0] ) ? $beer_meta['abv'][0] . '%' : '-' ); ?></td>

			<td><?php echo esc_html( $containers_display ?: '-' ); ?></td>

			<td>
				<input type="number"
					   class="tap-number-input"
					   value="<?php echo esc_attr( $item->tap_number ?: '' ); ?>"
					   min="0"
					   style="width: 60px;"
					   data-item-id="<?php echo esc_attr( $item->id ); ?>">
			</td>

			<td>
				<label class="ontap-toggle">
					<input type="checkbox"
						   class="availability-toggle"
						   <?php checked( $is_available ); ?>
						   data-item-id="<?php echo esc_attr( $item->id ); ?>">
					<span class="toggle-slider"></span>
				</label>
				<?php if ( $has_manual_override ) : ?>
					<span class="dashicons dashicons-admin-generic" title="<?php esc_attr_e( 'Manual override active', 'ontap' ); ?>" style="color: #f0b849;"></span>
				<?php endif; ?>
			</td>

			<td>
				<button type="button"
						class="button button-small remove-from-tap"
						data-item-id="<?php echo esc_attr( $item->id ); ?>">
					<?php esc_html_e( 'Remove', 'ontap' ); ?>
				</button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get taplist items for a taproom
	 *
	 * @param int $taproom_id Taproom term ID.
	 * @return array Taplist items
	 */
	private function get_taplist_items( $taproom_id ) {
		global $wpdb;

		$table      = $wpdb->prefix . 'ontap_taplist';
		$posts_table = $wpdb->posts;
		$postmeta_table = $wpdb->postmeta;

		// Get sort parameter
		$sort_by = isset( $_GET['sort_by'] ) ? sanitize_text_field( $_GET['sort_by'] ) : 'tap_number';

		// Build ORDER BY clause based on sort option
		$order_by = 'tap_number ASC, date_added DESC';

		switch ( $sort_by ) {
			case 'name':
				$order_by = 'post_title ASC';
				break;

			case 'name_desc':
				$order_by = 'post_title DESC';
				break;

			case 'style':
				// For style sorting, we'll need to get the term name
				// This will be handled in PHP after the query
				$order_by = 'date_added DESC';
				break;

			case 'abv_asc':
				$order_by = 'CAST(abv_value AS DECIMAL(5,2)) ASC';
				break;

			case 'abv_desc':
				$order_by = 'CAST(abv_value AS DECIMAL(5,2)) DESC';
				break;

			case 'date_added':
				$order_by = 't.date_added DESC';
				break;

			case 'date_added_asc':
				$order_by = 't.date_added ASC';
				break;

			case 'tap_number':
			default:
				$order_by = 't.tap_number ASC, t.date_added DESC';
				break;
		}

		// Build query with joins for sorting by beer properties
		$query = "
			SELECT t.*, p.post_title, pm_abv.meta_value as abv_value
			FROM {$table} t
			LEFT JOIN {$posts_table} p ON t.beer_id = p.ID
			LEFT JOIN {$postmeta_table} pm_abv ON t.beer_id = pm_abv.post_id AND pm_abv.meta_key = 'abv'
			WHERE t.taproom_id = %d
			ORDER BY {$order_by}
		";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$items = $wpdb->get_results(
			$wpdb->prepare( $query, $taproom_id )
		);

		// Handle style sorting in PHP (more complex due to taxonomy relationship)
		if ( 'style' === $sort_by && ! empty( $items ) ) {
			usort( $items, function( $a, $b ) {
				$style_a = $this->get_beer_style_name( $a->beer_id );
				$style_b = $this->get_beer_style_name( $b->beer_id );
				return strcmp( $style_a, $style_b );
			} );
		}

		return $items ?: array();
	}

	/**
	 * Get beer style name for sorting
	 *
	 * @param int $beer_id Beer post ID.
	 * @return string Style name
	 */
	private function get_beer_style_name( $beer_id ) {
		$styles = wp_get_post_terms( $beer_id, 'ontap_style' );

		if ( empty( $styles ) || is_wp_error( $styles ) ) {
			return '';
		}

		// Get parent term name
		foreach ( $styles as $term ) {
			if ( 0 === $term->parent ) {
				return $term->name;
			}
		}

		// Fallback to first term
		return $styles[0]->name;
	}
}
