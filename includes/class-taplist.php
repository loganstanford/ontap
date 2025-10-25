<?php
/**
 * Taplist Management
 *
 * Manages the relationship between beers and taprooms
 *
 * @package OnTap
 * @since   1.0.0
 */

namespace OnTap;

/**
 * Taplist class
 */
class Taplist {

	/**
	 * Get the taplist table name
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ontap_taplist';
	}

	/**
	 * Get taplist items for a taproom
	 *
	 * @param int  $taproom_id     The taproom term ID.
	 * @param bool $available_only Only return available items.
	 * @return array Array of taplist items with beer data and containers
	 */
	public static function get_taplist( $taproom_id, $available_only = true ) {
		global $wpdb;
		$table = self::get_table_name();

		$where = $wpdb->prepare( 'WHERE taproom_id = %d', $taproom_id );

		if ( $available_only ) {
			$where .= ' AND is_available = 1';
		}

		$results = $wpdb->get_results(
			"SELECT * FROM {$table} {$where} ORDER BY tap_number ASC, date_added DESC"
		);

		if ( ! $results ) {
			return array();
		}

		// Enrich with beer post data and containers
		foreach ( $results as &$item ) {
			$item->beer = get_post( $item->beer_id );
			$item->containers = Container::get_containers( $item->id, $available_only );
		}

		return $results;
	}

	/**
	 * Get a single taplist item
	 *
	 * @param int $beer_id    The beer post ID.
	 * @param int $taproom_id The taproom term ID.
	 * @return object|null Taplist item or null
	 */
	public static function get_item( $beer_id, $taproom_id ) {
		global $wpdb;
		$table = self::get_table_name();

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE beer_id = %d AND taproom_id = %d",
				$beer_id,
				$taproom_id
			)
		);
	}

	/**
	 * Add or update a taplist item
	 *
	 * @param int   $beer_id      The beer post ID.
	 * @param int   $taproom_id   The taproom term ID.
	 * @param array $data         Additional data.
	 * @return int|false Taplist ID on success, false on failure
	 */
	public static function save_item( $beer_id, $taproom_id, $data = array() ) {
		global $wpdb;
		$table = self::get_table_name();

		$defaults = array(
			'tap_number'           => null,
			'is_available'         => 1,
			'untappd_menu_item_id' => null,
		);

		$item_data = wp_parse_args( $data, $defaults );

		// Check if item already exists
		$existing = self::get_item( $beer_id, $taproom_id );

		if ( $existing ) {
			// Update existing item
			$wpdb->update(
				$table,
				array(
					'tap_number'           => $item_data['tap_number'],
					'is_available'         => $item_data['is_available'],
					'untappd_menu_item_id' => $item_data['untappd_menu_item_id'],
				),
				array(
					'beer_id'    => $beer_id,
					'taproom_id' => $taproom_id,
				),
				array( '%d', '%d', '%s' ),
				array( '%d', '%d' )
			);

			return $existing->id;
		}

		// Insert new item
		$inserted = $wpdb->insert(
			$table,
			array(
				'beer_id'              => $beer_id,
				'taproom_id'           => $taproom_id,
				'tap_number'           => $item_data['tap_number'],
				'is_available'         => $item_data['is_available'],
				'untappd_menu_item_id' => $item_data['untappd_menu_item_id'],
			),
			array( '%d', '%d', '%d', '%d', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Delete a taplist item
	 *
	 * @param int $beer_id    The beer post ID.
	 * @param int $taproom_id The taproom term ID.
	 * @return bool True on success
	 */
	public static function delete_item( $beer_id, $taproom_id ) {
		global $wpdb;
		$table = self::get_table_name();

		// Get item to delete containers
		$item = self::get_item( $beer_id, $taproom_id );

		if ( $item ) {
			// Delete associated containers first
			Container::delete_containers( $item->id );
		}

		return $wpdb->delete(
			$table,
			array(
				'beer_id'    => $beer_id,
				'taproom_id' => $taproom_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * Mark an item as unavailable
	 *
	 * @param int $beer_id    The beer post ID.
	 * @param int $taproom_id The taproom term ID.
	 * @return bool True on success
	 */
	public static function mark_unavailable( $beer_id, $taproom_id ) {
		global $wpdb;
		$table = self::get_table_name();

		return $wpdb->update(
			$table,
			array( 'is_available' => 0 ),
			array(
				'beer_id'    => $beer_id,
				'taproom_id' => $taproom_id,
			),
			array( '%d' ),
			array( '%d', '%d' )
		);
	}

	/**
	 * Check if a beer is on tap at a taproom
	 *
	 * @param int $beer_id    The beer post ID.
	 * @param int $taproom_id The taproom term ID.
	 * @return bool True if on tap
	 */
	public static function is_on_tap( $beer_id, $taproom_id ) {
		$item = self::get_item( $beer_id, $taproom_id );
		return $item && $item->is_available;
	}

	/**
	 * Get all taprooms where a beer is available
	 *
	 * @param int $beer_id The beer post ID.
	 * @return array Array of taproom term IDs
	 */
	public static function get_beer_locations( $beer_id ) {
		global $wpdb;
		$table = self::get_table_name();

		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT taproom_id FROM {$table} WHERE beer_id = %d AND is_available = 1",
				$beer_id
			)
		);

		return $results ? $results : array();
	}

	/**
	 * Count beers on tap at a taproom
	 *
	 * @param int $taproom_id The taproom term ID.
	 * @return int Number of beers on tap
	 */
	public static function count_beers( $taproom_id ) {
		global $wpdb;
		$table = self::get_table_name();

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE taproom_id = %d AND is_available = 1",
				$taproom_id
			)
		);
	}
}
