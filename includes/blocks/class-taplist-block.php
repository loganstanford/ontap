<?php
/**
 * Gutenberg Taplist Block
 *
 * @package OnTap\Blocks
 * @since   1.0.0
 */

namespace OnTap\Blocks;

use OnTap\Frontend\Shortcode;

/**
 * Taplist Block class
 */
class Taplist_Block {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the Gutenberg block
	 *
	 * @return void
	 */
	public function register_block() {
		// Register block only if Gutenberg is available
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'ontap/taplist',
			array(
				'attributes'      => $this->get_block_attributes(),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Get block attributes
	 *
	 * @return array Block attributes
	 */
	private function get_block_attributes() {
		return array(
			'taproom'          => array(
				'type'    => 'string',
				'default' => '',
			),
			'layout'           => array(
				'type'    => 'string',
				'default' => 'grid',
			),
			'columns'          => array(
				'type'    => 'number',
				'default' => 3,
			),
			'showFilters'      => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showSearch'       => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showSort'         => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showImage'        => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showTapNumber'    => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showStyle'        => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showAbv'          => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showIbu'          => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showDescription'  => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'showContainers'   => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'postsPerPage'     => array(
				'type'    => 'number',
				'default' => 12,
			),
			'pagination'       => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'orderBy'          => array(
				'type'    => 'string',
				'default' => 'tap_number',
			),
			'order'            => array(
				'type'    => 'string',
				'default' => 'ASC',
			),
		);
	}

	/**
	 * Render the block
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML
	 */
	public function render_block( $attributes ) {
		// Convert camelCase to snake_case and boolean to yes/no
		$atts = array(
			'taproom'          => $attributes['taproom'],
			'layout'           => $attributes['layout'],
			'columns'          => $attributes['columns'],
			'show_filters'     => $attributes['showFilters'] ? 'yes' : 'no',
			'show_search'      => $attributes['showSearch'] ? 'yes' : 'no',
			'show_sort'        => $attributes['showSort'] ? 'yes' : 'no',
			'show_image'       => $attributes['showImage'] ? 'yes' : 'no',
			'show_tap_number'  => $attributes['showTapNumber'] ? 'yes' : 'no',
			'show_style'       => $attributes['showStyle'] ? 'yes' : 'no',
			'show_abv'         => $attributes['showAbv'] ? 'yes' : 'no',
			'show_ibu'         => $attributes['showIbu'] ? 'yes' : 'no',
			'show_description' => $attributes['showDescription'] ? 'yes' : 'no',
			'show_containers'  => $attributes['showContainers'] ? 'yes' : 'no',
			'posts_per_page'   => $attributes['postsPerPage'],
			'pagination'       => $attributes['pagination'] ? 'yes' : 'no',
			'order_by'         => $attributes['orderBy'],
			'order'            => $attributes['order'],
		);

		// Use shortcode class to render
		$shortcode = new Shortcode();
		return $shortcode->render_taplist( $atts );
	}
}
