<?php
/**
 * Term meta fields for taxonomies
 *
 * @package OnTap\Admin
 * @since   1.0.0
 */

namespace OnTap\Admin;

/**
 * Term Meta class
 */
class Term_Meta {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add term meta fields to taproom taxonomy
		add_action( 'ontap_taproom_add_form_fields', array( $this, 'add_taproom_fields' ), 10, 2 );
		add_action( 'ontap_taproom_edit_form_fields', array( $this, 'edit_taproom_fields' ), 10, 2 );
		add_action( 'created_ontap_taproom', array( $this, 'save_taproom_fields' ), 10, 2 );
		add_action( 'edited_ontap_taproom', array( $this, 'save_taproom_fields' ), 10, 2 );
		add_filter( 'manage_edit-ontap_taproom_columns', array( $this, 'add_taproom_columns' ) );
		add_filter( 'manage_ontap_taproom_custom_column', array( $this, 'render_taproom_column' ), 10, 3 );
	}

	/**
	 * Add fields to taproom create form
	 *
	 * @param string $taxonomy Current taxonomy slug.
	 * @return void
	 */
	public function add_taproom_fields( $taxonomy ) {
		?>
		<div class="form-field term-untappd-menu-id-wrap">
			<label for="untappd_menu_id"><?php esc_html_e( 'Untappd Menu ID', 'ontap' ); ?></label>
			<input type="text" name="untappd_menu_id" id="untappd_menu_id" value="" />
			<p class="description">
				<?php esc_html_e( 'Enter the Untappd menu ID for this taproom. Find this in the Untappd Business dashboard.', 'ontap' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add fields to taproom edit form
	 *
	 * @param WP_Term $term     Current taxonomy term object.
	 * @param string  $taxonomy Current taxonomy slug.
	 * @return void
	 */
	public function edit_taproom_fields( $term, $taxonomy ) {
		$menu_id = get_term_meta( $term->term_id, 'untappd_menu_id', true );
		?>
		<tr class="form-field term-untappd-menu-id-wrap">
			<th scope="row">
				<label for="untappd_menu_id"><?php esc_html_e( 'Untappd Menu ID', 'ontap' ); ?></label>
			</th>
			<td>
				<input type="text" name="untappd_menu_id" id="untappd_menu_id" value="<?php echo esc_attr( $menu_id ); ?>" class="regular-text" />
				<p class="description">
					<?php esc_html_e( 'Enter the Untappd menu ID for this taproom. Find this in the Untappd Business dashboard.', 'ontap' ); ?>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save taproom term meta
	 *
	 * @param int $term_id Term ID.
	 * @return void
	 */
	public function save_taproom_fields( $term_id ) {
		if ( isset( $_POST['untappd_menu_id'] ) ) {
			$menu_id = sanitize_text_field( $_POST['untappd_menu_id'] );
			update_term_meta( $term_id, 'untappd_menu_id', $menu_id );
		}
	}

	/**
	 * Add custom columns to taproom list
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns
	 */
	public function add_taproom_columns( $columns ) {
		$columns['untappd_menu_id'] = __( 'Menu ID', 'ontap' );
		$columns['beer_count']       = __( 'Beers on Tap', 'ontap' );
		return $columns;
	}

	/**
	 * Render custom column content
	 *
	 * @param string $content     Column content.
	 * @param string $column_name Column name.
	 * @param int    $term_id     Term ID.
	 * @return string Column content
	 */
	public function render_taproom_column( $content, $column_name, $term_id ) {
		if ( 'untappd_menu_id' === $column_name ) {
			$menu_id = get_term_meta( $term_id, 'untappd_menu_id', true );
			return $menu_id ? esc_html( $menu_id ) : '—';
		}

		if ( 'beer_count' === $column_name ) {
			$count = \OnTap\Taplist::count_beers( $term_id );
			return $count ? sprintf(
				'<strong>%d</strong>',
				$count
			) : '0';
		}

		return $content;
	}
}
