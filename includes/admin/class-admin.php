<?php
/**
 * Admin-specific functionality
 *
 * @package OnTap\Admin
 * @since   1.0.0
 */

namespace OnTap\Admin;

/**
 * Admin class
 */
class Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Additional admin hooks can be added here
	}

	/**
	 * Enqueue admin styles
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_styles( $hook ) {
		// Only load on OnTap admin pages and beer edit screens
		if ( $this->is_ontap_admin_page( $hook ) ) {
			wp_enqueue_style(
				'ontap-admin',
				ONTAP_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				ONTAP_VERSION,
				'all'
			);
		}
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on OnTap admin pages and beer edit screens
		if ( $this->is_ontap_admin_page( $hook ) ) {
			wp_enqueue_script(
				'ontap-admin',
				ONTAP_PLUGIN_URL . 'assets/js/admin.js',
				array( 'jquery' ),
				ONTAP_VERSION,
				true
			);

			// Localize script data
			$localized_data = array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ontap_admin_nonce' ),
				'strings' => array(
					'confirmSync'   => __( 'Are you sure you want to sync the taplist? This may take a few moments.', 'ontap' ),
					'syncSuccess'   => __( 'Taplist synced successfully!', 'ontap' ),
					'syncError'     => __( 'Error syncing taplist. Please try again.', 'ontap' ),
					'confirmDelete' => __( 'Are you sure you want to delete this item?', 'ontap' ),
				),
			);

			// Add to main admin script
			wp_localize_script( 'ontap-admin', 'ontapAdmin', $localized_data );

			// Enqueue taplist manager script on the manage taplist page
			if ( 'ontap_page_ontap-manage-taplist' === $hook ) {
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script(
					'ontap-taplist-manager',
					ONTAP_PLUGIN_URL . 'assets/js/taplist-manager.js',
					array( 'jquery', 'jquery-ui-sortable', 'ontap-admin' ),
					ONTAP_VERSION,
					true
				);

				// Also add to taplist manager script
				wp_localize_script( 'ontap-taplist-manager', 'ontapAdmin', $localized_data );
			}
		}
	}

	/**
	 * Check if current page is an OnTap admin page
	 *
	 * @param string $hook The current admin page hook.
	 * @return bool
	 */
	private function is_ontap_admin_page( $hook ) {
		global $post_type;

		// Check if we're on OnTap settings pages
		if ( strpos( $hook, 'ontap' ) !== false ) {
			return true;
		}

		// Check if we're editing a beer post type
		if ( 'ontap_beer' === $post_type ) {
			return true;
		}

		return false;
	}
}
