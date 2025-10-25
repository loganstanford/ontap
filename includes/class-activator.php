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

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Table for tracking which beers are on tap at which locations
		$taplist_table = $wpdb->prefix . 'ontap_taplist';
		$sql_taplist = "CREATE TABLE $taplist_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			beer_id bigint(20) NOT NULL,
			taproom_id bigint(20) NOT NULL,
			tap_number int(11) DEFAULT NULL,
			is_available tinyint(1) DEFAULT 1,
			untappd_menu_item_id varchar(100) DEFAULT NULL,
			date_added datetime DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY beer_id (beer_id),
			KEY taproom_id (taproom_id),
			KEY is_available (is_available),
			UNIQUE KEY unique_tap (beer_id, taproom_id)
		) $charset_collate;";

		dbDelta( $sql_taplist );

		// Table for containers (serving sizes and prices)
		// Each taplist item can have multiple containers (e.g., 3oz, 6oz, 12oz, crowler, 6-pack)
		$containers_table = $wpdb->prefix . 'ontap_containers';
		$sql_containers = "CREATE TABLE $containers_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			taplist_id bigint(20) NOT NULL,
			container_type varchar(50) DEFAULT NULL,
			size varchar(50) NOT NULL,
			price decimal(10,2) DEFAULT NULL,
			is_available tinyint(1) DEFAULT 1,
			sort_order int(11) DEFAULT 0,
			untappd_container_id varchar(100) DEFAULT NULL,
			date_added datetime DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY taplist_id (taplist_id),
			KEY is_available (is_available),
			KEY sort_order (sort_order)
		) $charset_collate;";

		dbDelta( $sql_containers );

		// Table for sync history
		$sync_history_table = $wpdb->prefix . 'ontap_sync_history';
		$sql_sync_history = "CREATE TABLE $sync_history_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			sync_date datetime DEFAULT CURRENT_TIMESTAMP,
			status varchar(20) NOT NULL,
			beers_created int(11) DEFAULT 0,
			beers_updated int(11) DEFAULT 0,
			taplist_synced int(11) DEFAULT 0,
			containers_synced int(11) DEFAULT 0,
			error_count int(11) DEFAULT 0,
			error_messages text DEFAULT NULL,
			duration float DEFAULT NULL,
			triggered_by varchar(50) DEFAULT 'manual',
			PRIMARY KEY  (id),
			KEY status (status),
			KEY sync_date (sync_date)
		) $charset_collate;";

		dbDelta( $sql_sync_history );

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
			'untappd_email'         => '',
			'untappd_api_token'     => '',
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
