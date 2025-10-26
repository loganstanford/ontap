<?php
/**
 * The core plugin class
 *
 * @package OnTap
 * @since   1.0.0
 */

namespace OnTap;

/**
 * Main Plugin Class
 */
class Plugin {

	/**
	 * The single instance of the class
	 *
	 * @var Plugin
	 */
	protected static $instance = null;

	/**
	 * Post type handler
	 *
	 * @var Post_Types
	 */
	public $post_types;

	/**
	 * Admin handler
	 *
	 * @var Admin\Admin
	 */
	public $admin;

	/**
	 * Settings handler
	 *
	 * @var Admin\Settings
	 */
	public $settings;

	/**
	 * Taplist Manager handler
	 *
	 * @var Admin\Taplist_Manager
	 */
	public $taplist_manager;

	/**
	 * API handler
	 *
	 * @var API\Untappd_Client
	 */
	public $api_client;

	/**
	 * Frontend handler
	 *
	 * @var Frontend\Frontend
	 */
	public $frontend;

	/**
	 * Shortcode handler
	 *
	 * @var Frontend\Shortcode
	 */
	public $shortcode;

	/**
	 * Get the singleton instance
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies
	 *
	 * @return void
	 */
	private function load_dependencies() {
		// Core components will be autoloaded
		$this->post_types      = new Post_Types();
		$this->admin           = new Admin\Admin();
		$this->settings        = new Admin\Settings();
		$this->taplist_manager = new Admin\Taplist_Manager();

		// Frontend components
		$this->frontend  = new Frontend\Frontend();
		$this->shortcode = new Frontend\Shortcode();

		// Blocks
		new Blocks\Taplist_Block();

		// Admin-only components
		if ( is_admin() ) {
			new Admin\Ajax();
			new Admin\Term_Meta();
		}

		// Elementor integration (if Elementor is active)
		add_action( 'plugins_loaded', array( $this, 'init_integrations' ) );
	}

	/**
	 * Define the locale for internationalization
	 *
	 * @return void
	 */
	private function set_locale() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin textdomain
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ontap',
			false,
			dirname( ONTAP_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Register admin-specific hooks
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		// Admin hooks will be defined here
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this->settings, 'add_menu_pages' ) );
		add_action( 'admin_menu', array( $this->taplist_manager, 'add_menu_page' ), 15 );
		add_action( 'admin_init', array( $this->settings, 'register_settings' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register public-facing hooks
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		// Public hooks will be defined here
		add_action( 'wp_enqueue_scripts', array( $this->frontend, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this->frontend, 'enqueue_scripts' ) );
	}

	/**
	 * Initialize integrations with third-party plugins
	 *
	 * @return void
	 */
	public function init_integrations() {
		// Elementor integration
		if ( did_action( 'elementor/loaded' ) ) {
			new Integrations\Elementor();
		}
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'ontap-taplist-block',
			ONTAP_PLUGIN_URL . 'assets/js/blocks/taplist-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			ONTAP_VERSION,
			true
		);
	}

	/**
	 * Run the plugin
	 *
	 * @return void
	 */
	public function run() {
		// Plugin is running and hooks are registered
	}
}
