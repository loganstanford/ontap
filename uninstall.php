<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package OnTap
 * @since   1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete plugin data on uninstall
 *
 * NOTE: Only runs if user confirms deletion in the plugin settings.
 * By default, data is preserved to prevent accidental data loss.
 */

// Check if user opted to delete data on uninstall
$settings = get_option( 'ontap_settings', array() );
$delete_on_uninstall = isset( $settings['delete_on_uninstall'] ) ? $settings['delete_on_uninstall'] : false;

if ( ! $delete_on_uninstall ) {
	// User wants to preserve data, exit without deleting
	return;
}

global $wpdb;

// Delete custom post type posts
$beer_posts = get_posts(
	array(
		'post_type'      => 'ontap_beer',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	)
);

foreach ( $beer_posts as $post_id ) {
	wp_delete_post( $post_id, true );
}

// Delete taxonomies
$taxonomies = array( 'ontap_taproom', 'ontap_style' );

foreach ( $taxonomies as $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term_id ) {
			wp_delete_term( $term_id, $taxonomy );
		}
	}
}

// Delete custom tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ontap_containers" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ontap_taplist" );

// Delete options
delete_option( 'ontap_settings' );
delete_option( 'ontap_db_version' );

// Delete transients
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ontap_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ontap_%'" );

// Remove custom capabilities
$roles = array( 'administrator', 'editor' );

foreach ( $roles as $role_name ) {
	$role = get_role( $role_name );
	if ( $role ) {
		$role->remove_cap( 'manage_ontap_settings' );
		$role->remove_cap( 'sync_ontap_taplist' );
	}
}
