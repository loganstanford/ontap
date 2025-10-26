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
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_category' ) );
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
		require_once ONTAP_PLUGIN_DIR . 'includes/integrations/elementor/class-taplist-grid-widget.php';
		require_once ONTAP_PLUGIN_DIR . 'includes/integrations/elementor/class-taplist-list-widget.php';

		$widgets_manager->register( new \OnTap\Integrations\Elementor\Taplist_Grid_Widget() );
		$widgets_manager->register( new \OnTap\Integrations\Elementor\Taplist_List_Widget() );
	}
}
