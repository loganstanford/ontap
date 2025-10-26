<?php
/**
 * Elementor Integration
 *
 * @package OnTap\Integrations
 * @since   1.0.0
 */

namespace OnTap\Integrations;

/**
 * Elementor class
 */
class Elementor {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Check minimum Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, '3.0.0', '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_elementor_version' ) );
			return;
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_category' ) );
	}

	/**
	 * Admin notice for minimum Elementor version
	 *
	 * @return void
	 */
	public function admin_notice_minimum_elementor_version() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ontap' ),
			'<strong>' . esc_html__( 'OnTap Elementor Widgets', 'ontap' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'ontap' ) . '</strong>',
			'3.0.0'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Add OnTap category to Elementor
	 *
	 * @param object $elements_manager Elementor elements manager.
	 * @return void
	 */
	public function add_category( $elements_manager ) {
		$elements_manager->add_category(
			'ontap',
			array(
				'title' => __( 'OnTap', 'ontap' ),
				'icon'  => 'fa fa-beer',
			)
		);
	}

	/**
	 * Register Elementor widgets
	 *
	 * @param object $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		// Include widget files
		$grid_widget_file = ONTAP_PLUGIN_DIR . 'includes/integrations/elementor/class-taplist-grid-widget.php';
		$list_widget_file = ONTAP_PLUGIN_DIR . 'includes/integrations/elementor/class-taplist-list-widget.php';

		if ( ! file_exists( $grid_widget_file ) || ! file_exists( $list_widget_file ) ) {
			return;
		}

		require_once $grid_widget_file;
		require_once $list_widget_file;

		// Create widget instances
		$grid_widget = new \OnTap\Integrations\Elementor\Taplist_Grid_Widget();
		$list_widget = new \OnTap\Integrations\Elementor\Taplist_List_Widget();

		// Register widgets
		$widgets_manager->register( $grid_widget );
		$widgets_manager->register( $list_widget );
	}
}
