<?php
/**
 * Plugin settings and admin pages
 *
 * @package OnTap\Admin
 * @since   1.0.0
 */

namespace OnTap\Admin;

/**
 * Settings class
 */
class Settings {

	/**
	 * Settings option name
	 *
	 * @var string
	 */
	private $option_name = 'ontap_settings';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Constructor hooks are registered in Plugin class
	}

	/**
	 * Add menu pages to WordPress admin
	 *
	 * @return void
	 */
	public function add_menu_pages() {
		// Main settings page
		add_menu_page(
			__( 'OnTap Settings', 'ontap' ),
			__( 'OnTap', 'ontap' ),
			'manage_ontap_settings',
			'ontap-settings',
			array( $this, 'render_settings_page' ),
			'dashicons-beer',
			58
		);

		// Settings submenu (same as main page)
		add_submenu_page(
			'ontap-settings',
			__( 'Settings', 'ontap' ),
			__( 'Settings', 'ontap' ),
			'manage_ontap_settings',
			'ontap-settings',
			array( $this, 'render_settings_page' )
		);

		// Taproom management submenu
		add_submenu_page(
			'ontap-settings',
			__( 'Taprooms', 'ontap' ),
			__( 'Taprooms', 'ontap' ),
			'manage_categories',
			'edit-tags.php?taxonomy=ontap_taproom&post_type=ontap_beer'
		);

		// Beer styles submenu
		add_submenu_page(
			'ontap-settings',
			__( 'Beer Styles', 'ontap' ),
			__( 'Beer Styles', 'ontap' ),
			'manage_categories',
			'edit-tags.php?taxonomy=ontap_style&post_type=ontap_beer'
		);
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'ontap_settings_group',
			$this->option_name,
			array( $this, 'sanitize_settings' )
		);

		// API Settings Section
		add_settings_section(
			'ontap_api_settings',
			__( 'Untappd API Settings', 'ontap' ),
			array( $this, 'render_api_section_description' ),
			'ontap-settings'
		);

		add_settings_field(
			'untappd_client_id',
			__( 'Untappd Client ID', 'ontap' ),
			array( $this, 'render_text_field' ),
			'ontap-settings',
			'ontap_api_settings',
			array(
				'field_id'    => 'untappd_client_id',
				'field_type'  => 'text',
				'description' => __( 'Enter your Untappd API Client ID', 'ontap' ),
			)
		);

		add_settings_field(
			'untappd_client_secret',
			__( 'Untappd Client Secret', 'ontap' ),
			array( $this, 'render_text_field' ),
			'ontap-settings',
			'ontap_api_settings',
			array(
				'field_id'    => 'untappd_client_secret',
				'field_type'  => 'password',
				'description' => __( 'Enter your Untappd API Client Secret', 'ontap' ),
			)
		);

		// Sync Settings Section
		add_settings_section(
			'ontap_sync_settings',
			__( 'Sync Settings', 'ontap' ),
			array( $this, 'render_sync_section_description' ),
			'ontap-settings'
		);

		add_settings_field(
			'sync_frequency',
			__( 'Sync Frequency', 'ontap' ),
			array( $this, 'render_select_field' ),
			'ontap-settings',
			'ontap_sync_settings',
			array(
				'field_id'    => 'sync_frequency',
				'options'     => array(
					'hourly'     => __( 'Every Hour', 'ontap' ),
					'twicedaily' => __( 'Twice Daily', 'ontap' ),
					'daily'      => __( 'Daily', 'ontap' ),
					'manual'     => __( 'Manual Only', 'ontap' ),
				),
				'description' => __( 'How often should the taplist sync with Untappd?', 'ontap' ),
			)
		);

		add_settings_field(
			'cache_duration',
			__( 'Cache Duration (seconds)', 'ontap' ),
			array( $this, 'render_number_field' ),
			'ontap-settings',
			'ontap_sync_settings',
			array(
				'field_id'    => 'cache_duration',
				'min'         => 300,
				'max'         => 86400,
				'step'        => 300,
				'description' => __( 'How long to cache API responses (300-86400 seconds)', 'ontap' ),
			)
		);

		// Display Settings Section
		add_settings_section(
			'ontap_display_settings',
			__( 'Display Settings', 'ontap' ),
			array( $this, 'render_display_section_description' ),
			'ontap-settings'
		);

		add_settings_field(
			'display_out_of_stock',
			__( 'Show Out of Stock Beers', 'ontap' ),
			array( $this, 'render_checkbox_field' ),
			'ontap-settings',
			'ontap_display_settings',
			array(
				'field_id'    => 'display_out_of_stock',
				'description' => __( 'Display beers that are marked as out of stock', 'ontap' ),
			)
		);

		add_settings_field(
			'default_layout',
			__( 'Default Layout', 'ontap' ),
			array( $this, 'render_select_field' ),
			'ontap-settings',
			'ontap_display_settings',
			array(
				'field_id' => 'default_layout',
				'options'  => array(
					'grid' => __( 'Grid', 'ontap' ),
					'list' => __( 'List', 'ontap' ),
					'card' => __( 'Card', 'ontap' ),
				),
			)
		);

		// Advanced Settings Section
		add_settings_section(
			'ontap_advanced_settings',
			__( 'Advanced Settings', 'ontap' ),
			array( $this, 'render_advanced_section_description' ),
			'ontap-settings'
		);

		add_settings_field(
			'delete_on_uninstall',
			__( 'Delete Data on Uninstall', 'ontap' ),
			array( $this, 'render_checkbox_field' ),
			'ontap-settings',
			'ontap_advanced_settings',
			array(
				'field_id'    => 'delete_on_uninstall',
				'description' => __( 'WARNING: This will permanently delete all plugin data when uninstalling', 'ontap' ),
			)
		);
	}

	/**
	 * Render the main settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_ontap_settings' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors( $this->option_name ); ?>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'ontap_settings_group' );
				do_settings_sections( 'ontap-settings' );
				submit_button( __( 'Save Settings', 'ontap' ) );
				?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Manual Sync', 'ontap' ); ?></h2>
			<p><?php esc_html_e( 'Click the button below to manually sync your taplist with Untappd.', 'ontap' ); ?></p>
			<button type="button" class="button button-primary" id="ontap-manual-sync">
				<?php esc_html_e( 'Sync Now', 'ontap' ); ?>
			</button>
			<span class="spinner" id="ontap-sync-spinner"></span>
			<div id="ontap-sync-result"></div>
		</div>
		<?php
	}

	/**
	 * Section descriptions
	 */
	public function render_api_section_description() {
		echo '<p>' . esc_html__( 'Configure your Untappd API credentials. You can obtain these from the Untappd API website.', 'ontap' ) . '</p>';
		echo '<p><a href="https://untappd.com/api/register" target="_blank">' . esc_html__( 'Register for Untappd API Access', 'ontap' ) . '</a></p>';
	}

	public function render_sync_section_description() {
		echo '<p>' . esc_html__( 'Control how often your taplist syncs with Untappd.', 'ontap' ) . '</p>';
	}

	public function render_display_section_description() {
		echo '<p>' . esc_html__( 'Customize how your taplist is displayed on the frontend.', 'ontap' ) . '</p>';
	}

	public function render_advanced_section_description() {
		echo '<p>' . esc_html__( 'Advanced options for plugin management.', 'ontap' ) . '</p>';
	}

	/**
	 * Render text/password field
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_text_field( $args ) {
		$settings  = get_option( $this->option_name, array() );
		$value     = isset( $settings[ $args['field_id'] ] ) ? $settings[ $args['field_id'] ] : '';
		$type      = isset( $args['field_type'] ) ? $args['field_type'] : 'text';
		$field_id  = esc_attr( $this->option_name . '[' . $args['field_id'] . ']' );

		?>
		<input type="<?php echo esc_attr( $type ); ?>"
			   id="<?php echo esc_attr( $args['field_id'] ); ?>"
			   name="<?php echo $field_id; ?>"
			   value="<?php echo esc_attr( $value ); ?>"
			   class="regular-text">
		<?php
		if ( isset( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Render select field
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_select_field( $args ) {
		$settings = get_option( $this->option_name, array() );
		$value    = isset( $settings[ $args['field_id'] ] ) ? $settings[ $args['field_id'] ] : '';
		$field_id = esc_attr( $this->option_name . '[' . $args['field_id'] . ']' );

		?>
		<select id="<?php echo esc_attr( $args['field_id'] ); ?>"
				name="<?php echo $field_id; ?>">
			<?php foreach ( $args['options'] as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_value ); ?>"
						<?php selected( $value, $option_value ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
		if ( isset( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Render number field
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_number_field( $args ) {
		$settings = get_option( $this->option_name, array() );
		$value    = isset( $settings[ $args['field_id'] ] ) ? $settings[ $args['field_id'] ] : '';
		$field_id = esc_attr( $this->option_name . '[' . $args['field_id'] . ']' );
		$min      = isset( $args['min'] ) ? $args['min'] : '';
		$max      = isset( $args['max'] ) ? $args['max'] : '';
		$step     = isset( $args['step'] ) ? $args['step'] : 1;

		?>
		<input type="number"
			   id="<?php echo esc_attr( $args['field_id'] ); ?>"
			   name="<?php echo $field_id; ?>"
			   value="<?php echo esc_attr( $value ); ?>"
			   min="<?php echo esc_attr( $min ); ?>"
			   max="<?php echo esc_attr( $max ); ?>"
			   step="<?php echo esc_attr( $step ); ?>">
		<?php
		if ( isset( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Render checkbox field
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_checkbox_field( $args ) {
		$settings = get_option( $this->option_name, array() );
		$value    = isset( $settings[ $args['field_id'] ] ) ? $settings[ $args['field_id'] ] : false;
		$field_id = esc_attr( $this->option_name . '[' . $args['field_id'] . ']' );

		?>
		<label>
			<input type="checkbox"
				   id="<?php echo esc_attr( $args['field_id'] ); ?>"
				   name="<?php echo $field_id; ?>"
				   value="1"
				   <?php checked( $value, 1 ); ?>>
			<?php if ( isset( $args['description'] ) ) : ?>
				<?php echo esc_html( $args['description'] ); ?>
			<?php endif; ?>
		</label>
		<?php
	}

	/**
	 * Sanitize settings before saving
	 *
	 * @param array $input The input settings array.
	 * @return array Sanitized settings
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['untappd_client_id'] ) ) {
			$sanitized['untappd_client_id'] = sanitize_text_field( $input['untappd_client_id'] );
		}

		if ( isset( $input['untappd_client_secret'] ) ) {
			$sanitized['untappd_client_secret'] = sanitize_text_field( $input['untappd_client_secret'] );
		}

		if ( isset( $input['sync_frequency'] ) ) {
			$allowed = array( 'hourly', 'twicedaily', 'daily', 'manual' );
			$sanitized['sync_frequency'] = in_array( $input['sync_frequency'], $allowed, true )
				? $input['sync_frequency']
				: 'hourly';
		}

		if ( isset( $input['cache_duration'] ) ) {
			$sanitized['cache_duration'] = absint( $input['cache_duration'] );
			$sanitized['cache_duration'] = max( 300, min( 86400, $sanitized['cache_duration'] ) );
		}

		$sanitized['display_out_of_stock'] = isset( $input['display_out_of_stock'] ) ? true : false;

		if ( isset( $input['default_layout'] ) ) {
			$allowed = array( 'grid', 'list', 'card' );
			$sanitized['default_layout'] = in_array( $input['default_layout'], $allowed, true )
				? $input['default_layout']
				: 'grid';
		}

		$sanitized['delete_on_uninstall'] = isset( $input['delete_on_uninstall'] ) ? true : false;

		return $sanitized;
	}
}
