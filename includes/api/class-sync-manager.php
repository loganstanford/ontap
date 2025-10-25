<?php
/**
 * Sync Manager - Handles syncing from Untappd to WordPress
 *
 * @package OnTap\API
 * @since   1.0.0
 */

namespace OnTap\API;

use OnTap\Taplist;
use OnTap\Container;
use OnTap\Debug_Logger;

/**
 * Sync Manager class
 */
class Sync_Manager {

	/**
	 * Untappd API client
	 *
	 * @var Untappd_Client
	 */
	private $client;

	/**
	 * Sync results
	 *
	 * @var array
	 */
	private $results = array();

	/**
	 * Constructor
	 *
	 * @param Untappd_Client $client API client instance.
	 */
	public function __construct( $client = null ) {
		$this->client = $client ?: new Untappd_Client();
		$this->reset_results();
	}

	/**
	 * Reset sync results
	 *
	 * @return void
	 */
	private function reset_results() {
		$this->results = array(
			'success'        => false,
			'beers_created'  => 0,
			'beers_updated'  => 0,
			'taplist_synced' => 0,
			'containers_synced' => 0,
			'errors'         => array(),
			'message'        => '',
		);
	}

	/**
	 * Sync all taprooms
	 *
	 * @return array Sync results
	 */
	public function sync_all() {
		$this->reset_results();

		Debug_Logger::log( 'Starting sync of all taprooms', 'info' );

		// Get all taproom terms
		$taprooms = get_terms(
			array(
				'taxonomy'   => 'ontap_taproom',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $taprooms ) ) {
			$this->results['errors'][] = $taprooms->get_error_message();
			$this->results['message']  = __( 'Failed to get taprooms', 'ontap' );
			return $this->results;
		}

		if ( empty( $taprooms ) ) {
			$this->results['message'] = __( 'No taprooms configured. Please add taprooms and assign menu IDs.', 'ontap' );
			return $this->results;
		}

		foreach ( $taprooms as $taproom ) {
			$result = $this->sync_taproom( $taproom->term_id );

			if ( is_wp_error( $result ) ) {
				$this->results['errors'][] = sprintf(
					/* translators: %1$s: taproom name, %2$s: error message */
					__( '%1$s: %2$s', 'ontap' ),
					$taproom->name,
					$result->get_error_message()
				);
			}
		}

		$this->results['success'] = empty( $this->results['errors'] );
		$this->results['message']  = $this->get_summary_message();

		Debug_Logger::log(
			'Sync completed',
			$this->results['success'] ? 'info' : 'error',
			array(
				'beers_created'      => $this->results['beers_created'],
				'beers_updated'      => $this->results['beers_updated'],
				'taplist_synced'     => $this->results['taplist_synced'],
				'containers_synced'  => $this->results['containers_synced'],
				'errors'             => $this->results['errors'],
			)
		);

		return $this->results;
	}

	/**
	 * Sync a specific taproom
	 *
	 * @param int $taproom_id Taproom term ID.
	 * @return bool|WP_Error True on success, error on failure
	 */
	public function sync_taproom( $taproom_id ) {
		// Get menu ID from term meta
		$menu_id = get_term_meta( $taproom_id, 'untappd_menu_id', true );

		if ( empty( $menu_id ) ) {
			return new \WP_Error(
				'no_menu_id',
				__( 'No Untappd menu ID configured for this taproom', 'ontap' )
			);
		}

		// Fetch menu from Untappd
		$menu = $this->client->get_menu( $menu_id );

		if ( is_wp_error( $menu ) ) {
			return $menu;
		}

		// Process each section's items
		foreach ( $menu['sections'] as $section ) {
			// Skip non-public sections or "On Deck"
			if ( ! $section['public'] || 'OnDeckSection' === $section['type'] ) {
				continue;
			}

			foreach ( $section['items'] as $item ) {
				// Only process beer items
				if ( 'beer' !== $item['type'] ) {
					continue;
				}

				$beer_result = $this->sync_beer( $item, $taproom_id );

				if ( is_wp_error( $beer_result ) ) {
					$this->results['errors'][] = $beer_result->get_error_message();
				}
			}
		}

		return true;
	}

	/**
	 * Sync a beer from Untappd item data
	 *
	 * @param array $item       Untappd menu item data.
	 * @param int   $taproom_id Taproom term ID.
	 * @return int|WP_Error Beer post ID on success, error on failure
	 */
	private function sync_beer( $item, $taproom_id ) {
		Debug_Logger::log(
			sprintf( 'Syncing beer: %s (Untappd ID: %s)', $item['name'], $item['untappd_id'] ),
			'info',
			array(
				'beer_name'    => $item['name'],
				'untappd_id'   => $item['untappd_id'],
				'style_field'  => isset( $item['style'] ) ? $item['style'] : 'NOT SET',
				'brewery'      => isset( $item['brewery'] ) ? $item['brewery'] : 'NOT SET',
				'abv'          => isset( $item['abv'] ) ? $item['abv'] : 'NOT SET',
			)
		);

		// Check if beer already exists by Untappd ID
		$existing = $this->get_beer_by_untappd_id( $item['untappd_id'] );

		$beer_data = array(
			'post_title'   => $item['name'],
			'post_content' => $item['description'] ?: '',
			'post_status'  => 'publish',
			'post_type'    => 'ontap_beer',
		);

		if ( $existing ) {
			// Update existing beer
			$beer_data['ID'] = $existing;
			$beer_id = wp_update_post( $beer_data );
			$this->results['beers_updated']++;
		} else {
			// Create new beer
			$beer_id = wp_insert_post( $beer_data );
			$this->results['beers_created']++;
		}

		if ( is_wp_error( $beer_id ) ) {
			return $beer_id;
		}

		// Update beer metadata
		$this->update_beer_meta( $beer_id, $item );

		// Assign to taproom taxonomy
		wp_set_object_terms( $beer_id, array( $taproom_id ), 'ontap_taproom', false );

		// Assign beer style taxonomy
		if ( ! empty( $item['style'] ) ) {
			$this->assign_beer_style( $beer_id, $item['style'] );
		}

		// Download and set featured image
		if ( ! empty( $item['label_image_hd'] ) ) {
			$this->set_beer_image( $beer_id, $item['label_image_hd'], $item['name'] );
		}

		// Sync taplist item and containers
		$this->sync_taplist_item( $beer_id, $taproom_id, $item );

		return $beer_id;
	}

	/**
	 * Get beer post ID by Untappd ID
	 *
	 * @param int $untappd_id Untappd beer ID.
	 * @return int|null Post ID or null if not found
	 */
	private function get_beer_by_untappd_id( $untappd_id ) {
		global $wpdb;

		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = 'untappd_id'
				AND meta_value = %d
				LIMIT 1",
				$untappd_id
			)
		);

		return $post_id ? (int) $post_id : null;
	}

	/**
	 * Update beer metadata
	 *
	 * @param int   $beer_id Post ID.
	 * @param array $item    Untappd item data.
	 * @return void
	 */
	private function update_beer_meta( $beer_id, $item ) {
		$meta_map = array(
			'untappd_id'          => 'untappd_id',
			'untappd_beer_slug'   => 'untappd_beer_slug',
			'brewery'             => 'brewery',
			'brewery_location'    => 'brewery_location',
			'style'               => 'style',
			'abv'                 => 'abv',
			'ibu'                 => 'ibu',
			'calories'            => 'calories',
			'rating'              => 'rating',
			'rating_count'        => 'rating_count',
			'label_image'         => 'label_image',
			'label_image_hd'      => 'label_image_hd',
		);

		foreach ( $meta_map as $meta_key => $item_key ) {
			if ( isset( $item[ $item_key ] ) ) {
				update_post_meta( $beer_id, $meta_key, $item[ $item_key ] );
			}
		}

		// Store last sync time
		update_post_meta( $beer_id, 'last_synced', current_time( 'mysql' ) );
	}

	/**
	 * Assign beer style taxonomy
	 *
	 * Handles Untappd's "Parent - Child" style format by creating hierarchical terms.
	 * Examples:
	 * - "IPA - New England / Hazy" → Parent: "IPA", Child: "New England / Hazy"
	 * - "Stout - Imperial / Double Milk" → Parent: "Stout", Child: "Imperial / Double Milk"
	 * - "Pilsner - German" → Parent: "Pilsner", Child: "German"
	 *
	 * @param int    $beer_id Post ID.
	 * @param string $style   Beer style name from Untappd.
	 * @return void
	 */
	private function assign_beer_style( $beer_id, $style ) {
		if ( empty( $style ) ) {
			Debug_Logger::log(
				sprintf( 'Empty style for beer ID %d', $beer_id ),
				'warning'
			);
			return;
		}

		Debug_Logger::log(
			sprintf( 'Assigning style to beer ID %d', $beer_id ),
			'info',
			array(
				'beer_id'      => $beer_id,
				'style_value'  => $style,
				'style_type'   => gettype( $style ),
			)
		);

		// Split style by hyphen to get parent and child
		$parts = array_map( 'trim', explode( ' - ', $style, 2 ) );

		$parent_name = $parts[0];
		$child_name  = isset( $parts[1] ) ? $parts[1] : null;

		Debug_Logger::log(
			sprintf( 'Parsed style into parent/child for beer ID %d', $beer_id ),
			'info',
			array(
				'beer_id'     => $beer_id,
				'original'    => $style,
				'parent_name' => $parent_name,
				'child_name'  => $child_name,
			)
		);

		// Get or create parent term
		$parent_term = term_exists( $parent_name, 'ontap_style' );

		if ( ! $parent_term ) {
			$parent_term = wp_insert_term(
				$parent_name,
				'ontap_style',
				array( 'parent' => 0 )
			);
		}

		if ( is_wp_error( $parent_term ) ) {
			return;
		}

		$parent_id = $parent_term['term_id'];
		$terms_to_assign = array( $parent_id );

		// If there's a child style, create it under the parent
		if ( $child_name ) {
			$child_term = term_exists( $child_name, 'ontap_style', $parent_id );

			if ( ! $child_term ) {
				$child_term = wp_insert_term(
					$child_name,
					'ontap_style',
					array( 'parent' => $parent_id )
				);
			}

			if ( ! is_wp_error( $child_term ) ) {
				// Assign both parent and child
				$terms_to_assign[] = $child_term['term_id'];
			}
		}

		// Assign terms to beer
		wp_set_object_terms( $beer_id, $terms_to_assign, 'ontap_style', false );
	}

	/**
	 * Set beer featured image from URL
	 *
	 * @param int    $beer_id   Post ID.
	 * @param string $image_url Image URL.
	 * @param string $beer_name Beer name for alt text.
	 * @return void
	 */
	private function set_beer_image( $beer_id, $image_url, $beer_name ) {
		// Check if image already set
		if ( get_post_thumbnail_id( $beer_id ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Download image
		$tmp = download_url( $image_url );

		if ( is_wp_error( $tmp ) ) {
			return;
		}

		$file_array = array(
			'name'     => basename( $image_url ) . '.jpg',
			'tmp_name' => $tmp,
		);

		// Upload to media library
		$attachment_id = media_handle_sideload( $file_array, $beer_id );

		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $file_array['tmp_name'] );
			return;
		}

		// Set as featured image
		set_post_thumbnail( $beer_id, $attachment_id );

		// Set alt text
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $beer_name );
	}

	/**
	 * Sync taplist item and containers
	 *
	 * @param int   $beer_id    Beer post ID.
	 * @param int   $taproom_id Taproom term ID.
	 * @param array $item       Untappd item data.
	 * @return void
	 */
	private function sync_taplist_item( $beer_id, $taproom_id, $item ) {
		// Save or update taplist item
		$taplist_id = Taplist::save_item(
			$beer_id,
			$taproom_id,
			array(
				'tap_number'           => $item['tap_number'] ?? null,
				'is_available'         => ! $item['hidden'],
				'untappd_menu_item_id' => $item['id'],
			)
		);

		if ( ! $taplist_id ) {
			return;
		}

		$this->results['taplist_synced']++;

		// Sync containers if available
		if ( ! empty( $item['containers'] ) ) {
			$containers_data = array();

			foreach ( $item['containers'] as $container ) {
				$containers_data[] = array(
					'id'       => $container['id'],
					'type'     => $container['container_size']['name'] ?? null,
					'size'     => $container['name'],
					'price'    => $container['price'] ?? null,
					'available' => true,
				);
			}

			$synced = Container::sync_from_untappd( $taplist_id, $containers_data );
			$this->results['containers_synced'] += $synced;
		}
	}

	/**
	 * Get summary message
	 *
	 * @return string Summary message
	 */
	private function get_summary_message() {
		if ( ! empty( $this->results['errors'] ) ) {
			return sprintf(
				/* translators: %d: number of errors */
				__( 'Sync completed with %d error(s)', 'ontap' ),
				count( $this->results['errors'] )
			);
		}

		return sprintf(
			/* translators: %1$d: beers created, %2$d: beers updated, %3$d: taplist items */
			__( 'Success! Created %1$d beers, updated %2$d beers, synced %3$d taplist items', 'ontap' ),
			$this->results['beers_created'],
			$this->results['beers_updated'],
			$this->results['taplist_synced']
		);
	}

	/**
	 * Get sync results
	 *
	 * @return array Results array
	 */
	public function get_results() {
		return $this->results;
	}
}
