<?php
/**
 * Register all custom post types and taxonomies
 *
 * @package OnTap
 * @since   1.0.0
 */

namespace OnTap;

/**
 * Post Types class
 */
class Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_beer_post_type' ) );
		add_action( 'init', array( $this, 'register_taproom_taxonomy' ) );
	}

	/**
	 * Register the Beer custom post type
	 *
	 * @return void
	 */
	public function register_beer_post_type() {
		$labels = array(
			'name'                  => _x( 'Beers', 'Post Type General Name', 'ontap' ),
			'singular_name'         => _x( 'Beer', 'Post Type Singular Name', 'ontap' ),
			'menu_name'             => __( 'Beers', 'ontap' ),
			'name_admin_bar'        => __( 'Beer', 'ontap' ),
			'archives'              => __( 'Beer Archives', 'ontap' ),
			'attributes'            => __( 'Beer Attributes', 'ontap' ),
			'parent_item_colon'     => __( 'Parent Beer:', 'ontap' ),
			'all_items'             => __( 'All Beers', 'ontap' ),
			'add_new_item'          => __( 'Add New Beer', 'ontap' ),
			'add_new'               => __( 'Add New', 'ontap' ),
			'new_item'              => __( 'New Beer', 'ontap' ),
			'edit_item'             => __( 'Edit Beer', 'ontap' ),
			'update_item'           => __( 'Update Beer', 'ontap' ),
			'view_item'             => __( 'View Beer', 'ontap' ),
			'view_items'            => __( 'View Beers', 'ontap' ),
			'search_items'          => __( 'Search Beer', 'ontap' ),
			'not_found'             => __( 'Not found', 'ontap' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'ontap' ),
			'featured_image'        => __( 'Beer Label', 'ontap' ),
			'set_featured_image'    => __( 'Set beer label', 'ontap' ),
			'remove_featured_image' => __( 'Remove beer label', 'ontap' ),
			'use_featured_image'    => __( 'Use as beer label', 'ontap' ),
			'insert_into_item'      => __( 'Insert into beer', 'ontap' ),
			'uploaded_to_this_item' => __( 'Uploaded to this beer', 'ontap' ),
			'items_list'            => __( 'Beers list', 'ontap' ),
			'items_list_navigation' => __( 'Beers list navigation', 'ontap' ),
			'filter_items_list'     => __( 'Filter beers list', 'ontap' ),
		);

		$args = array(
			'label'               => __( 'Beer', 'ontap' ),
			'description'         => __( 'Brewery beers from Untappd', 'ontap' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'taxonomies'          => array( 'ontap_taproom', 'ontap_style' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-beer',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rest_base'           => 'beers',
		);

		register_post_type( 'ontap_beer', $args );
	}

	/**
	 * Register the Taproom taxonomy
	 *
	 * @return void
	 */
	public function register_taproom_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Taprooms', 'Taxonomy General Name', 'ontap' ),
			'singular_name'              => _x( 'Taproom', 'Taxonomy Singular Name', 'ontap' ),
			'menu_name'                  => __( 'Taprooms', 'ontap' ),
			'all_items'                  => __( 'All Taprooms', 'ontap' ),
			'parent_item'                => __( 'Parent Taproom', 'ontap' ),
			'parent_item_colon'          => __( 'Parent Taproom:', 'ontap' ),
			'new_item_name'              => __( 'New Taproom Name', 'ontap' ),
			'add_new_item'               => __( 'Add New Taproom', 'ontap' ),
			'edit_item'                  => __( 'Edit Taproom', 'ontap' ),
			'update_item'                => __( 'Update Taproom', 'ontap' ),
			'view_item'                  => __( 'View Taproom', 'ontap' ),
			'separate_items_with_commas' => __( 'Separate taprooms with commas', 'ontap' ),
			'add_or_remove_items'        => __( 'Add or remove taprooms', 'ontap' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'ontap' ),
			'popular_items'              => __( 'Popular Taprooms', 'ontap' ),
			'search_items'               => __( 'Search Taprooms', 'ontap' ),
			'not_found'                  => __( 'Not Found', 'ontap' ),
			'no_terms'                   => __( 'No taprooms', 'ontap' ),
			'items_list'                 => __( 'Taprooms list', 'ontap' ),
			'items_list_navigation'      => __( 'Taprooms list navigation', 'ontap' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rest_base'         => 'taprooms',
		);

		register_taxonomy( 'ontap_taproom', array( 'ontap_beer' ), $args );

		// Register beer style taxonomy
		$this->register_style_taxonomy();
	}

	/**
	 * Register the Beer Style taxonomy
	 *
	 * @return void
	 */
	public function register_style_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Beer Styles', 'Taxonomy General Name', 'ontap' ),
			'singular_name'              => _x( 'Beer Style', 'Taxonomy Singular Name', 'ontap' ),
			'menu_name'                  => __( 'Beer Styles', 'ontap' ),
			'all_items'                  => __( 'All Styles', 'ontap' ),
			'parent_item'                => __( 'Parent Style', 'ontap' ),
			'parent_item_colon'          => __( 'Parent Style:', 'ontap' ),
			'new_item_name'              => __( 'New Style Name', 'ontap' ),
			'add_new_item'               => __( 'Add New Style', 'ontap' ),
			'edit_item'                  => __( 'Edit Style', 'ontap' ),
			'update_item'                => __( 'Update Style', 'ontap' ),
			'view_item'                  => __( 'View Style', 'ontap' ),
			'separate_items_with_commas' => __( 'Separate styles with commas', 'ontap' ),
			'add_or_remove_items'        => __( 'Add or remove styles', 'ontap' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'ontap' ),
			'popular_items'              => __( 'Popular Styles', 'ontap' ),
			'search_items'               => __( 'Search Styles', 'ontap' ),
			'not_found'                  => __( 'Not Found', 'ontap' ),
			'no_terms'                   => __( 'No styles', 'ontap' ),
			'items_list'                 => __( 'Styles list', 'ontap' ),
			'items_list_navigation'      => __( 'Styles list navigation', 'ontap' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'show_in_rest'      => true,
			'rest_base'         => 'beer-styles',
		);

		register_taxonomy( 'ontap_style', array( 'ontap_beer' ), $args );
	}
}
