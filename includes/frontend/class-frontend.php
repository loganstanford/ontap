<?php
/**
 * Frontend Display Functionality
 *
 * @package OnTap\Frontend
 * @since   1.0.0
 */

namespace OnTap\Frontend;

/**
 * Frontend class
 */
class Frontend {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue frontend styles
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'ontap-frontend',
			ONTAP_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			ONTAP_VERSION,
			'all'
		);
	}

	/**
	 * Enqueue frontend scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'ontap-frontend',
			ONTAP_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			ONTAP_VERSION,
			true
		);

		// Localize script data
		wp_localize_script(
			'ontap-frontend',
			'ontapFrontend',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ontap_frontend_nonce' ),
			)
		);
	}
}
