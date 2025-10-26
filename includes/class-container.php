<?php
/**
 * Container Management
 *
 * Handles serving sizes and pricing for beers (Untappd "containers")
 *
 * @package OnTap
 * @since   1.0.0
 */

namespace OnTap;

/**
 * Container class
 */
class Container {

	/**
	 * Get the containers table name
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ontap_containers';
	}

	/**
	 * Get containers for a taplist item
	 *
	 * @param int $taplist_id The taplist ID.
	 * @param bool $available_only Only return available containers.
	 * @return array Array of container objects
	 */
	public static function get_containers( $taplist_id, $available_only = true ) {
		global $wpdb;
		$table = self::get_table_name();

		$where = $wpdb->prepare( 'WHERE taplist_id = %d', $taplist_id );

		if ( $available_only ) {
			$where .= ' AND is_available = 1';
		}

		$results = $wpdb->get_results(
			"SELECT * FROM {$table} {$where} ORDER BY sort_order ASC, price ASC"
		);

		return $results ? $results : array();
	}

	/**
	 * Add or update a container
	 *
	 * @param int   $taplist_id       The taplist ID.
	 * @param array $container_data   Container data.
	 * @return int|false Container ID on success, false on failure
	 */
	public static function save_container( $taplist_id, $container_data ) {
		global $wpdb;
		$table = self::get_table_name();

		$defaults = array(
			'taplist_id'          => $taplist_id,
			'container_type'      => null,
			'size'                => '',
			'price'               => null,
			'is_available'        => 1,
			'sort_order'          => 0,
			'untappd_container_id' => null,
		);

		$data = wp_parse_args( $container_data, $defaults );

		// Check if container already exists by untappd_container_id
		if ( ! empty( $data['untappd_container_id'] ) ) {
			$existing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE untappd_container_id = %s",
					$data['untappd_container_id']
				)
			);

			if ( $existing ) {
				// Update existing container
				$wpdb->update(
					$table,
					array(
						'container_type' => $data['container_type'],
						'size'           => $data['size'],
						'price'          => $data['price'],
						'is_available'   => $data['is_available'],
						'sort_order'     => $data['sort_order'],
					),
					array( 'id' => $existing ),
					array( '%s', '%s', '%f', '%d', '%d' ),
					array( '%d' )
				);

				return $existing;
			}
		}

		// Insert new container
		$inserted = $wpdb->insert(
			$table,
			array(
				'taplist_id'           => $data['taplist_id'],
				'container_type'       => $data['container_type'],
				'size'                 => $data['size'],
				'price'                => $data['price'],
				'is_available'         => $data['is_available'],
				'sort_order'           => $data['sort_order'],
				'untappd_container_id' => $data['untappd_container_id'],
			),
			array( '%d', '%s', '%s', '%f', '%d', '%d', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Delete containers for a taplist item
	 *
	 * @param int $taplist_id The taplist ID.
	 * @return bool True on success
	 */
	public static function delete_containers( $taplist_id ) {
		global $wpdb;
		$table = self::get_table_name();

		return $wpdb->delete(
			$table,
			array( 'taplist_id' => $taplist_id ),
			array( '%d' )
		);
	}

	/**
	 * Delete a specific container
	 *
	 * @param int $container_id The container ID.
	 * @return bool True on success
	 */
	public static function delete_container( $container_id ) {
		global $wpdb;
		$table = self::get_table_name();

		return $wpdb->delete(
			$table,
			array( 'id' => $container_id ),
			array( '%d' )
		);
	}

	/**
	 * Get the cheapest container price for a taplist item
	 *
	 * @param int $taplist_id The taplist ID.
	 * @return float|null The minimum price or null
	 */
	public static function get_min_price( $taplist_id ) {
		global $wpdb;
		$table = self::get_table_name();

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MIN(price) FROM {$table} WHERE taplist_id = %d AND is_available = 1",
				$taplist_id
			)
		);
	}

	/**
	 * Get the most expensive container price for a taplist item
	 *
	 * @param int $taplist_id The taplist ID.
	 * @return float|null The maximum price or null
	 */
	public static function get_max_price( $taplist_id ) {
		global $wpdb;
		$table = self::get_table_name();

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(price) FROM {$table} WHERE taplist_id = %d AND is_available = 1",
				$taplist_id
			)
		);
	}

	/**
	 * Format price for display
	 *
	 * @param float  $price   The price.
	 * @param string $currency Currency symbol (default: $).
	 * @return string Formatted price
	 */
	public static function format_price( $price, $currency = '$' ) {
		if ( is_null( $price ) || $price === '' ) {
			return '';
		}

		return $currency . number_format( (float) $price, 2 );
	}

	/**
	 * Get container display label
	 *
	 * @param object $container The container object.
	 * @return string Display label (e.g., "16oz - $7.00")
	 */
	public static function get_display_label( $container ) {
		$parts = array();

		if ( ! empty( $container->size ) ) {
			$parts[] = $container->size;
		}

		if ( ! empty( $container->container_type ) && $container->container_type !== 'Draft' && $container->container_type !== $container->size ) {
			$parts[] = $container->container_type;
		}

		$label = implode( ' ', $parts );

		if ( ! is_null( $container->price ) ) {
			$label .= ' - ' . self::format_price( $container->price );
		}

		return $label;
	}

	/**
	 * Sync containers from Untappd data
	 *
	 * @param int   $taplist_id      The taplist ID.
	 * @param array $untappd_containers Array of Untappd container data.
	 * @return int Number of containers synced
	 */
	public static function sync_from_untappd( $taplist_id, $untappd_containers ) {
		if ( empty( $untappd_containers ) || ! is_array( $untappd_containers ) ) {
			return 0;
		}

		$synced = 0;

		foreach ( $untappd_containers as $index => $container ) {
			$container_data = array(
				'container_type'       => isset( $container['type'] ) ? $container['type'] : null,
				'size'                 => isset( $container['size'] ) ? $container['size'] : '',
				'price'                => isset( $container['price'] ) ? $container['price'] : null,
				'is_available'         => isset( $container['available'] ) ? (int) $container['available'] : 1,
				'sort_order'           => $index,
				'untappd_container_id' => isset( $container['id'] ) ? $container['id'] : null,
			);

			$result = self::save_container( $taplist_id, $container_data );

			if ( $result ) {
				$synced++;
			}
		}

		return $synced;
	}
}
