<?php
/**
 * Shortcode Handler
 *
 * @package OnTap\Frontend
 * @since   1.0.0
 */

namespace OnTap\Frontend;

use OnTap\Container;

/**
 * Shortcode class
 */
class Shortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'ontap_taplist', array( $this, 'render_taplist' ) );
	}

	/**
	 * Render taplist shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output
	 */
	public function render_taplist( $atts ) {
		$atts = shortcode_atts(
			array(
				'taproom'            => '',
				'taprooms'           => '',
				'layout'             => 'grid',
				'columns'            => '3',
				'show_filters'       => 'yes',
				'show_search'        => 'yes',
				'show_sort'          => 'yes',
				'show_image'         => 'yes',
				'show_style'         => 'yes',
				'show_abv'           => 'yes',
				'show_ibu'           => 'yes',
				'show_description'   => 'yes',
				'show_tap_number'    => 'yes',
				'show_containers'    => 'yes',
				'show_availability'  => 'yes',
				'show_parent_styles' => 'yes',
				'show_child_styles'  => 'yes',
				'posts_per_page'     => '-1',
				'pagination'         => 'no',
				'order_by'           => 'tap_number',
				'order'              => 'ASC',
			),
			$atts,
			'ontap_taplist'
		);

		// Convert yes/no to boolean
		foreach ( $atts as $key => $value ) {
			if ( 'yes' === $value || 'no' === $value ) {
				$atts[ $key ] = 'yes' === $value;
			}
		}

		// Get taproom IDs (support both taproom and taprooms parameters)
		$taproom_param = ! empty( $atts['taprooms'] ) ? $atts['taprooms'] : $atts['taproom'];
		$taproom_ids   = $this->get_taproom_ids( $taproom_param );

		// Get beers on tap
		$beers = $this->get_beers_on_tap( $taproom_ids, $atts );

		// Start output buffering
		ob_start();

		// Render wrapper
		echo '<div class="ontap-taplist-wrapper" data-layout="' . esc_attr( $atts['layout'] ) . '">';

		// Render filters/search
		if ( $atts['show_filters'] || $atts['show_search'] || $atts['show_sort'] ) {
			$this->render_controls( $atts, $beers );
		}

		// Render taplist
		if ( ! empty( $beers ) ) {
			$this->render_layout( $beers, $atts );
		} else {
			echo '<p class="ontap-no-beers">' . esc_html__( 'No beers currently on tap.', 'ontap' ) . '</p>';
		}

		// Render pagination
		if ( $atts['pagination'] && ! empty( $beers ) ) {
			$this->render_pagination( $beers, $atts );
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Get taproom IDs from slug(s), name(s), or ID(s)
	 *
	 * @param string $taprooms Comma-separated taproom slugs, names, or IDs.
	 * @return array Array of taproom term IDs
	 */
	private function get_taproom_ids( $taprooms ) {
		if ( empty( $taprooms ) ) {
			// Return empty array to show all taprooms
			return array();
		}

		// Split by comma and trim
		$taproom_array = array_map( 'trim', explode( ',', $taprooms ) );
		$taproom_ids   = array();

		foreach ( $taproom_array as $taproom ) {
			// Try to get by slug first
			$term = get_term_by( 'slug', $taproom, 'taproom' );

			if ( ! $term ) {
				// Try by name
				$term = get_term_by( 'name', $taproom, 'taproom' );
			}

			if ( ! $term && is_numeric( $taproom ) ) {
				// Try by ID
				$term = get_term_by( 'id', $taproom, 'taproom' );
			}

			if ( $term && ! is_wp_error( $term ) ) {
				$taproom_ids[] = $term->term_id;
			}
		}

		return $taproom_ids;
	}

	/**
	 * Get beers currently on tap
	 *
	 * @param array $taproom_ids Array of taproom IDs (empty for all).
	 * @param array $atts        Shortcode attributes.
	 * @return array Array of beer objects with taplist data
	 */
	private function get_beers_on_tap( $taproom_ids, $atts ) {
		global $wpdb;

		$taplist_table = $wpdb->prefix . 'ontap_taplist';
		$posts_table   = $wpdb->posts;

		// Build ORDER BY clause
		$order_by = $this->get_order_by_clause( $atts['order_by'], $atts['order'] );

		// Pagination - default to showing all beers (-1 means no limit)
		$limit_clause = '';
		if ( $atts['pagination'] && intval( $atts['posts_per_page'] ) > 0 ) {
			$paged    = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$per_page = intval( $atts['posts_per_page'] );
			$offset   = ( $paged - 1 ) * $per_page;
			$limit_clause = "LIMIT {$per_page} OFFSET {$offset}";
		}

		// Build WHERE clause for taprooms
		$taproom_where = '';
		if ( ! empty( $taproom_ids ) ) {
			$placeholders  = implode( ',', array_fill( 0, count( $taproom_ids ), '%d' ) );
			$taproom_where = $wpdb->prepare( "AND t.taproom_id IN ({$placeholders})", $taproom_ids );
		}

		// Query
		$query = "SELECT t.*, p.*
			FROM {$taplist_table} t
			LEFT JOIN {$posts_table} p ON t.beer_id = p.ID
			WHERE t.is_available = 1
			AND p.post_status = 'publish'
			{$taproom_where}
			ORDER BY {$order_by}
			{$limit_clause}";

		$results = $wpdb->get_results( $query );

		// Enhance with additional data
		if ( ! empty( $results ) ) {
			foreach ( $results as $beer ) {
				// Get containers
				$beer->containers = Container::get_containers( $beer->id, true );

				// Get meta
				$beer->abv = get_post_meta( $beer->beer_id, 'abv', true );
				$beer->ibu = get_post_meta( $beer->beer_id, 'ibu', true );

				// Description is stored in post_content (already available from query)
				// No need to fetch separately - it's already in $beer->post_content

				// Get featured image (post thumbnail) - fallback to label_url meta if no thumbnail
				$thumbnail_id = get_post_thumbnail_id( $beer->beer_id );
				if ( $thumbnail_id ) {
					$beer->image_url = get_the_post_thumbnail_url( $beer->beer_id, 'medium' );
				} else {
					// Fallback to Untappd label URL
					$beer->image_url = get_post_meta( $beer->beer_id, 'label_image', true );
				}

				// Get styles
				$beer->styles = wp_get_post_terms( $beer->beer_id, 'ontap_style' );
			}
		}

		return $results;
	}

	/**
	 * Get ORDER BY clause for SQL query
	 *
	 * @param string $order_by Order by field.
	 * @param string $order    Order direction.
	 * @return string SQL ORDER BY clause
	 */
	private function get_order_by_clause( $order_by, $order ) {
		$order = strtoupper( $order ) === 'DESC' ? 'DESC' : 'ASC';

		switch ( $order_by ) {
			case 'name':
				return "p.post_title {$order}";
			case 'tap_number':
				return "t.tap_number {$order}";
			case 'date_added':
				return "t.created_at {$order}";
			default:
				return "t.tap_number {$order}";
		}
	}

	/**
	 * Render control panel (filters, search, sort)
	 *
	 * @param array $atts  Shortcode attributes.
	 * @param array $beers Beer objects (to get styles from).
	 * @return void
	 */
	private function render_controls( $atts, $beers = array() ) {
		echo '<div class="ontap-controls">';

		// Search
		if ( $atts['show_search'] ) {
			echo '<div class="ontap-search">';
			echo '<input type="text" class="ontap-search-input" placeholder="' . esc_attr__( 'Search beers...', 'ontap' ) . '" />';
			echo '</div>';
		}

		// Style filters
		if ( $atts['show_filters'] ) {
			// Get unique styles from the current beer list
			$style_ids = array();
			foreach ( $beers as $beer ) {
				if ( ! empty( $beer->styles ) ) {
					foreach ( $beer->styles as $style ) {
						$style_ids[ $style->term_id ] = $style;
					}
				}
			}

			if ( ! empty( $style_ids ) ) {
				// Filter by parent or child styles based on settings
				$filter_styles = array();
				$show_parent   = ! isset( $atts['show_parent_styles'] ) || $atts['show_parent_styles'];
				$show_child    = ! isset( $atts['show_child_styles'] ) || $atts['show_child_styles'];

				foreach ( $style_ids as $style ) {
					$is_parent = ( 0 === $style->parent );

					if ( ( $is_parent && $show_parent ) || ( ! $is_parent && $show_child ) ) {
						$filter_styles[] = $style;
					}
				}

				if ( ! empty( $filter_styles ) ) {
					echo '<div class="ontap-filters">';
					echo '<button class="ontap-filter-btn active" data-style="all">' . esc_html__( 'All Styles', 'ontap' ) . '</button>';

					foreach ( $filter_styles as $style ) {
						echo '<button class="ontap-filter-btn" data-style="' . esc_attr( $style->term_id ) . '">';
						echo esc_html( $style->name );
						echo '</button>';
					}

					echo '</div>';
				}
			}
		}

		// Sort
		if ( $atts['show_sort'] ) {
			echo '<div class="ontap-sort">';
			echo '<label for="ontap-sort-select">' . esc_html__( 'Sort by:', 'ontap' ) . '</label>';
			echo '<select id="ontap-sort-select" class="ontap-sort-select">';
			echo '<option value="tap_number">' . esc_html__( 'Tap Number', 'ontap' ) . '</option>';
			echo '<option value="name">' . esc_html__( 'Name (A-Z)', 'ontap' ) . '</option>';
			echo '<option value="abv_asc">' . esc_html__( 'ABV (Low to High)', 'ontap' ) . '</option>';
			echo '<option value="abv_desc">' . esc_html__( 'ABV (High to Low)', 'ontap' ) . '</option>';
			echo '<option value="style">' . esc_html__( 'Style', 'ontap' ) . '</option>';
			echo '</select>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render layout based on type
	 *
	 * @param array $beers Beer objects.
	 * @param array $atts  Shortcode attributes.
	 * @return void
	 */
	private function render_layout( $beers, $atts ) {
		$layout = $atts['layout'];

		echo '<div class="ontap-taplist ontap-layout-' . esc_attr( $layout ) . '" data-columns="' . esc_attr( $atts['columns'] ) . '">';

		switch ( $layout ) {
			case 'list':
				$this->render_list_layout( $beers, $atts );
				break;
			case 'table':
				$this->render_table_layout( $beers, $atts );
				break;
			case 'grid':
			default:
				$this->render_grid_layout( $beers, $atts );
				break;
		}

		echo '</div>';
	}

	/**
	 * Render grid layout
	 *
	 * @param array $beers Beer objects.
	 * @param array $atts  Shortcode attributes.
	 * @return void
	 */
	private function render_grid_layout( $beers, $atts ) {
		foreach ( $beers as $beer ) {
			$this->render_beer_card( $beer, $atts );
		}
	}

	/**
	 * Render list layout
	 *
	 * @param array $beers Beer objects.
	 * @param array $atts  Shortcode attributes.
	 * @return void
	 */
	private function render_list_layout( $beers, $atts ) {
		foreach ( $beers as $beer ) {
			$this->render_beer_list_item( $beer, $atts );
		}
	}

	/**
	 * Render table layout
	 *
	 * @param array $beers Beer objects.
	 * @param array $atts  Shortcode attributes.
	 * @return void
	 */
	private function render_table_layout( $beers, $atts ) {
		echo '<table class="ontap-table">';
		echo '<thead><tr>';

		if ( $atts['show_tap_number'] ) {
			echo '<th>' . esc_html__( 'Tap', 'ontap' ) . '</th>';
		}

		if ( $atts['show_image'] ) {
			echo '<th>' . esc_html__( 'Image', 'ontap' ) . '</th>';
		}

		echo '<th>' . esc_html__( 'Beer', 'ontap' ) . '</th>';

		if ( $atts['show_style'] ) {
			echo '<th>' . esc_html__( 'Style', 'ontap' ) . '</th>';
		}

		if ( $atts['show_abv'] ) {
			echo '<th>' . esc_html__( 'ABV', 'ontap' ) . '</th>';
		}

		if ( $atts['show_ibu'] ) {
			echo '<th>' . esc_html__( 'IBU', 'ontap' ) . '</th>';
		}

		if ( $atts['show_containers'] ) {
			echo '<th>' . esc_html__( 'Sizes', 'ontap' ) . '</th>';
		}

		echo '</tr></thead>';
		echo '<tbody>';

		foreach ( $beers as $beer ) {
			$this->render_beer_table_row( $beer, $atts );
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Render beer card (grid item)
	 *
	 * @param object $beer Beer object.
	 * @param array  $atts Shortcode attributes.
	 * @return void
	 */
	private function render_beer_card( $beer, $atts ) {
		$styles      = ! empty( $beer->styles ) ? wp_list_pluck( $beer->styles, 'name' ) : array();
		$style_ids   = ! empty( $beer->styles ) ? wp_list_pluck( $beer->styles, 'term_id' ) : array();
		$style_class = ! empty( $style_ids ) ? 'style-' . implode( ' style-', $style_ids ) : '';

		echo '<div class="ontap-beer-card ' . esc_attr( $style_class ) . '" data-beer-id="' . esc_attr( $beer->beer_id ) . '" data-style-ids="' . esc_attr( implode( ',', $style_ids ) ) . '">';

		// Tap number badge
		if ( $atts['show_tap_number'] && ! empty( $beer->tap_number ) ) {
			echo '<div class="ontap-tap-badge">' . esc_html( $beer->tap_number ) . '</div>';
		}

		// Image
		if ( $atts['show_image'] ) {
			echo '<div class="ontap-beer-image">';
			if ( ! empty( $beer->image_url ) ) {
				echo '<img src="' . esc_url( $beer->image_url ) . '" alt="' . esc_attr( $beer->post_title ) . '" />';
			} else {
				echo '<div class="ontap-beer-placeholder"></div>';
			}
			echo '</div>';
		}

		echo '<div class="ontap-beer-content">';

		// Title
		echo '<h3 class="ontap-beer-title">' . esc_html( $beer->post_title ) . '</h3>';

		// Style
		if ( $atts['show_style'] && ! empty( $styles ) ) {
			echo '<div class="ontap-beer-style">' . esc_html( implode( ' > ', $styles ) ) . '</div>';
		}

		// ABV / IBU
		if ( $atts['show_abv'] || $atts['show_ibu'] ) {
			echo '<div class="ontap-beer-stats">';

			if ( $atts['show_abv'] && ! empty( $beer->abv ) ) {
				echo '<span class="ontap-stat-abv">' . esc_html( $beer->abv ) . '% ABV</span>';
			}

			if ( $atts['show_ibu'] && ! empty( $beer->ibu ) ) {
				echo '<span class="ontap-stat-ibu">' . esc_html( $beer->ibu ) . ' IBU</span>';
			}

			echo '</div>';
		}

		// Description
		if ( $atts['show_description'] && ! empty( $beer->post_content ) ) {
			echo '<div class="ontap-beer-description">' . wp_kses_post( wpautop( $beer->post_content ) ) . '</div>';
		}

		// Containers
		if ( $atts['show_containers'] && ! empty( $beer->containers ) ) {
			echo '<div class="ontap-beer-containers">';
			foreach ( $beer->containers as $container ) {
				echo '<span class="ontap-container">' . esc_html( Container::get_display_label( $container ) ) . '</span>';
			}
			echo '</div>';
		}

		echo '</div>'; // .ontap-beer-content

		// View details button
		echo '<button class="ontap-view-details" data-beer-id="' . esc_attr( $beer->beer_id ) . '">' . esc_html__( 'View Details', 'ontap' ) . '</button>';

		echo '</div>'; // .ontap-beer-card
	}

	/**
	 * Render beer list item
	 *
	 * @param object $beer Beer object.
	 * @param array  $atts Shortcode attributes.
	 * @return void
	 */
	private function render_beer_list_item( $beer, $atts ) {
		$styles      = ! empty( $beer->styles ) ? wp_list_pluck( $beer->styles, 'name' ) : array();
		$style_ids   = ! empty( $beer->styles ) ? wp_list_pluck( $beer->styles, 'term_id' ) : array();
		$style_class = ! empty( $style_ids ) ? 'style-' . implode( ' style-', $style_ids ) : '';

		echo '<div class="ontap-beer-list-item ' . esc_attr( $style_class ) . '" data-beer-id="' . esc_attr( $beer->beer_id ) . '" data-style-ids="' . esc_attr( implode( ',', $style_ids ) ) . '">';

		// Image
		if ( $atts['show_image'] ) {
			echo '<div class="ontap-beer-image">';
			if ( ! empty( $beer->image_url ) ) {
				echo '<img src="' . esc_url( $beer->image_url ) . '" alt="' . esc_attr( $beer->post_title ) . '" />';
			} else {
				echo '<div class="ontap-beer-placeholder"></div>';
			}
			echo '</div>';
		}

		echo '<div class="ontap-beer-info">';

		// Tap number + Title
		echo '<div class="ontap-beer-header">';
		if ( $atts['show_tap_number'] && ! empty( $beer->tap_number ) ) {
			echo '<span class="ontap-tap-number">#' . esc_html( $beer->tap_number ) . '</span>';
		}
		echo '<h3 class="ontap-beer-title">' . esc_html( $beer->post_title ) . '</h3>';
		echo '</div>';

		// Style
		if ( $atts['show_style'] && ! empty( $styles ) ) {
			echo '<div class="ontap-beer-style">' . esc_html( implode( ' > ', $styles ) ) . '</div>';
		}

		// Description
		if ( $atts['show_description'] && ! empty( $beer->post_content ) ) {
			echo '<div class="ontap-beer-description">' . wp_kses_post( wpautop( $beer->post_content ) ) . '</div>';
		}

		echo '</div>'; // .ontap-beer-info

		echo '<div class="ontap-beer-meta">';

		// ABV / IBU
		if ( $atts['show_abv'] || $atts['show_ibu'] ) {
			echo '<div class="ontap-beer-stats">';

			if ( $atts['show_abv'] && ! empty( $beer->abv ) ) {
				echo '<span class="ontap-stat-abv">' . esc_html( $beer->abv ) . '% ABV</span>';
			}

			if ( $atts['show_ibu'] && ! empty( $beer->ibu ) ) {
				echo '<span class="ontap-stat-ibu">' . esc_html( $beer->ibu ) . ' IBU</span>';
			}

			echo '</div>';
		}

		// Containers
		if ( $atts['show_containers'] && ! empty( $beer->containers ) ) {
			echo '<div class="ontap-beer-containers">';
			foreach ( $beer->containers as $container ) {
				echo '<span class="ontap-container">' . esc_html( Container::get_display_label( $container ) ) . '</span>';
			}
			echo '</div>';
		}

		echo '</div>'; // .ontap-beer-meta

		echo '</div>'; // .ontap-beer-list-item
	}

	/**
	 * Render beer table row
	 *
	 * @param object $beer Beer object.
	 * @param array  $atts Shortcode attributes.
	 * @return void
	 */
	private function render_beer_table_row( $beer, $atts ) {
		$styles      = ! empty( $beer->styles ) ? wp_list_pluck( $beer->styles, 'name' ) : array();
		$style_ids   = ! empty( $beer->styles ) ? wp_list_pluck( $beer->styles, 'term_id' ) : array();
		$style_class = ! empty( $style_ids ) ? 'style-' . implode( ' style-', $style_ids ) : '';

		echo '<tr class="ontap-beer-row ' . esc_attr( $style_class ) . '" data-beer-id="' . esc_attr( $beer->beer_id ) . '" data-style-ids="' . esc_attr( implode( ',', $style_ids ) ) . '">';

		// Tap number
		if ( $atts['show_tap_number'] ) {
			echo '<td class="ontap-tap-number">' . esc_html( $beer->tap_number ) . '</td>';
		}

		// Image
		if ( $atts['show_image'] ) {
			echo '<td class="ontap-beer-image">';
			if ( ! empty( $beer->image_url ) ) {
				echo '<img src="' . esc_url( $beer->image_url ) . '" alt="' . esc_attr( $beer->post_title ) . '" width="50" />';
			}
			echo '</td>';
		}

		// Beer name
		echo '<td class="ontap-beer-name">' . esc_html( $beer->post_title ) . '</td>';

		// Style
		if ( $atts['show_style'] ) {
			echo '<td class="ontap-beer-style">' . esc_html( implode( ' > ', $styles ) ) . '</td>';
		}

		// ABV
		if ( $atts['show_abv'] ) {
			echo '<td class="ontap-abv">' . esc_html( $beer->abv ) . '%</td>';
		}

		// IBU
		if ( $atts['show_ibu'] ) {
			echo '<td class="ontap-ibu">' . esc_html( $beer->ibu ) . '</td>';
		}

		// Containers
		if ( $atts['show_containers'] ) {
			echo '<td class="ontap-containers">';
			if ( ! empty( $beer->containers ) ) {
				$container_labels = array();
				foreach ( $beer->containers as $container ) {
					$container_labels[] = Container::get_display_label( $container );
				}
				echo esc_html( implode( ', ', $container_labels ) );
			}
			echo '</td>';
		}

		echo '</tr>';
	}

	/**
	 * Render pagination
	 *
	 * @param array $beers Beer objects.
	 * @param array $atts  Shortcode attributes.
	 * @return void
	 */
	private function render_pagination( $beers, $atts ) {
		global $wpdb;

		// Get total count
		$taplist_table = $wpdb->prefix . 'ontap_taplist';
		$taproom_param = ! empty( $atts['taprooms'] ) ? $atts['taprooms'] : $atts['taproom'];
		$taproom_ids   = $this->get_taproom_ids( $taproom_param );

		// Build WHERE clause for taprooms
		$taproom_where = '';
		if ( ! empty( $taproom_ids ) ) {
			$placeholders  = implode( ',', array_fill( 0, count( $taproom_ids ), '%d' ) );
			$taproom_where = $wpdb->prepare( "AND taproom_id IN ({$placeholders})", $taproom_ids );
		}

		$total = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$taplist_table} WHERE is_available = 1 {$taproom_where}"
		);

		$paged     = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		$per_page  = intval( $atts['posts_per_page'] );
		$max_pages = ceil( $total / $per_page );

		if ( $max_pages > 1 ) {
			echo '<div class="ontap-pagination">';
			echo paginate_links(
				array(
					'current'   => $paged,
					'total'     => $max_pages,
					'prev_text' => __( '&laquo; Previous', 'ontap' ),
					'next_text' => __( 'Next &raquo;', 'ontap' ),
				)
			);
			echo '</div>';
		}
	}
}
