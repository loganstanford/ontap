<?php
/**
 * Fired during plugin activation
 *
 * @package OnTap
 * @since   1.0.0
 */

namespace OnTap;

/**
 * Activator class
 */
class Activator {

	/**
	 * Run activation tasks
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();
		self::create_capabilities();

		// Flush rewrite rules after registering post types
		$post_types = new Post_Types();
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables
	 *
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'ontap_taplist';

		// Table for tracking which beers are on tap at which locations
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			beer_id bigint(20) NOT NULL,
			taproom_id bigint(20) NOT NULL,
			tap_number int(11) DEFAULT NULL,
			is_available tinyint(1) DEFAULT 1,
			pour_size varchar(50) DEFAULT NULL,
			price decimal(10,2) DEFAULT NULL,
			untappd_menu_item_id varchar(100) DEFAULT NULL,
			date_added datetime DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY beer_id (beer_id),
			KEY taproom_id (taproom_id),
			KEY is_available (is_available),
			UNIQUE KEY unique_tap (beer_id, taproom_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Store database version for future migrations
		update_option( 'ontap_db_version', ONTAP_VERSION );
	}

	/**
	 * Set default plugin options
	 *
	 * @return void
	 */
	private static function set_default_options() {
		$defaults = array(
			'untappd_client_id'     => '',
			'untappd_client_secret' => '',
			'sync_frequency'        => 'hourly',
			'cache_duration'        => 3600,
			'display_out_of_stock'  => false,
			'default_layout'        => 'grid',
		);

		add_option( 'ontap_settings', $defaults );
	}

	/**
	 * Create custom capabilities
	 *
	 * @return void
	 */
	private static function create_capabilities() {
		$admin_role = get_role( 'administrator' );

		if ( $admin_role ) {
			$admin_role->add_cap( 'manage_ontap_settings' );
			$admin_role->add_cap( 'sync_ontap_taplist' );
		}

		$editor_role = get_role( 'editor' );

		if ( $editor_role ) {
			$editor_role->add_cap( 'sync_ontap_taplist' );
		}
	}
}
