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

		// Admin-only components
		if ( is_admin() ) {
			new Admin\Ajax();
			new Admin\Term_Meta();
		}
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
	}

	/**
	 * Register public-facing hooks
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		// Public hooks will be defined here
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
	}

	/**
	 * Enqueue public assets
	 *
	 * @return void
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style(
			'ontap-public',
			ONTAP_PLUGIN_URL . 'assets/css/public.css',
			array(),
			ONTAP_VERSION,
			'all'
		);

		wp_enqueue_script(
			'ontap-public',
			ONTAP_PLUGIN_URL . 'assets/js/public.js',
			array( 'jquery' ),
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
