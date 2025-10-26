<?php
/**
 * Elementor Taplist Grid Widget
 *
 * @package OnTap\Integrations\Elementor
 * @since   1.0.0
 */

namespace OnTap\Integrations\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use OnTap\Frontend\Shortcode;

/**
 * Taplist Grid Widget class
 */
class Taplist_Grid_Widget extends Widget_Base {

	/**
	 * Get widget name
	 *
	 * @return string Widget name
	 */
	public function get_name() {
		return 'ontap_taplist_grid';
	}

	/**
	 * Get widget title
	 *
	 * @return string Widget title
	 */
	public function get_title() {
		return __( 'Taplist Grid', 'ontap' );
	}

	/**
	 * Get widget icon
	 *
	 * @return string Widget icon
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Get widget categories
	 *
	 * @return array Widget categories
	 */
	public function get_categories() {
		return array( 'ontap' );
	}

	/**
	 * Register widget controls
	 *
	 * @return void
	 */
	protected function register_controls() {
		// Content Tab - Layout Section
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'ontap' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// Taproom selection
		$taprooms = get_terms(
			array(
				'taxonomy'   => 'taproom',
				'hide_empty' => false,
			)
		);

		$taproom_options = array();
		if ( ! empty( $taprooms ) && ! is_wp_error( $taprooms ) ) {
			foreach ( $taprooms as $taproom ) {
				$taproom_options[ $taproom->slug ] = $taproom->name;
			}
		}

		$this->add_control(
			'taprooms',
			array(
				'label'       => __( 'Taprooms', 'ontap' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $taproom_options,
				'multiple'    => true,
				'label_block' => true,
				'default'     => array(),
				'description' => __( 'Leave empty to show all taprooms', 'ontap' ),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'ontap' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => __( 'Beers Per Page', 'ontap' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 12,
				'min'     => 1,
				'max'     => 100,
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'        => __( 'Pagination', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'order_by',
			array(
				'label'   => __( 'Order By', 'ontap' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tap_number',
				'options' => array(
					'tap_number' => __( 'Tap Number', 'ontap' ),
					'name'       => __( 'Name', 'ontap' ),
					'date_added' => __( 'Date Added', 'ontap' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'ontap' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => array(
					'ASC'  => __( 'Ascending', 'ontap' ),
					'DESC' => __( 'Descending', 'ontap' ),
				),
			)
		);

		$this->end_controls_section();

		// Content Tab - Controls Section
		$this->start_controls_section(
			'section_controls',
			array(
				'label' => __( 'Controls', 'ontap' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_filters',
			array(
				'label'        => __( 'Show Style Filters', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'        => __( 'Show Search', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_sort',
			array(
				'label'        => __( 'Show Sort', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		// Content Tab - Display Options Section
		$this->start_controls_section(
			'section_display',
			array(
				'label' => __( 'Display Options', 'ontap' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_image',
			array(
				'label'        => __( 'Show Image', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_tap_number',
			array(
				'label'        => __( 'Show Tap Number', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_style',
			array(
				'label'        => __( 'Show Style', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_abv',
			array(
				'label'        => __( 'Show ABV', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_ibu',
			array(
				'label'        => __( 'Show IBU', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_description',
			array(
				'label'        => __( 'Show Description', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_containers',
			array(
				'label'        => __( 'Show Containers/Pricing', 'ontap' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ontap' ),
				'label_off'    => __( 'Hide', 'ontap' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		// Style Tab - Card Section
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => __( 'Card', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_background',
			array(
				'label'     => __( 'Background Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-beer-card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .ontap-beer-card',
			)
		);

		$this->add_responsive_control(
			'card_border_radius',
			array(
				'label'      => __( 'Border Radius', 'ontap' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-beer-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .ontap-beer-card',
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'ontap' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-beer-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_gap',
			array(
				'label'      => __( 'Gap Between Cards', 'ontap' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-layout-grid' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Tab - Title Section
		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => __( 'Title', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-beer-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .ontap-beer-title',
			)
		);

		$this->add_responsive_control(
			'title_spacing',
			array(
				'label'      => __( 'Spacing', 'ontap' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-beer-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Tab - Style Section
		$this->start_controls_section(
			'section_style_style',
			array(
				'label' => __( 'Beer Style', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'style_color',
			array(
				'label'     => __( 'Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-beer-style' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'style_typography',
				'selector' => '{{WRAPPER}} .ontap-beer-style',
			)
		);

		$this->end_controls_section();

		// Style Tab - Stats Section
		$this->start_controls_section(
			'section_style_stats',
			array(
				'label' => __( 'Stats (ABV/IBU)', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'stats_color',
			array(
				'label'     => __( 'Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-beer-stats' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'stats_typography',
				'selector' => '{{WRAPPER}} .ontap-beer-stats',
			)
		);

		$this->end_controls_section();

		// Style Tab - Description Section
		$this->start_controls_section(
			'section_style_description',
			array(
				'label' => __( 'Description', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'description_color',
			array(
				'label'     => __( 'Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-beer-description' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .ontap-beer-description',
			)
		);

		$this->end_controls_section();

		// Style Tab - Tap Badge Section
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label' => __( 'Tap Number Badge', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'badge_color',
			array(
				'label'     => __( 'Text Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-tap-badge' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_background',
			array(
				'label'     => __( 'Background Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-tap-badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .ontap-tap-badge',
			)
		);

		$this->end_controls_section();

		// Style Tab - Button Section
		$this->start_controls_section(
			'section_style_button',
			array(
				'label' => __( 'View Details Button', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'     => __( 'Text Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-view-details' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background',
			array(
				'label'     => __( 'Background Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-view-details' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => __( 'Hover Text Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-view-details:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_background',
			array(
				'label'     => __( 'Hover Background Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-view-details:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .ontap-view-details',
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => __( 'Padding', 'ontap' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-view-details' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'ontap' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-view-details' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Convert taprooms array to comma-separated string
		$taprooms = '';
		if ( ! empty( $settings['taprooms'] ) && is_array( $settings['taprooms'] ) ) {
			$taprooms = implode( ',', $settings['taprooms'] );
		}

		// Build shortcode attributes
		$atts = array(
			'taprooms'         => $taprooms,
			'layout'           => 'grid',
			'columns'          => $settings['columns'],
			'show_filters'     => $settings['show_filters'],
			'show_search'      => $settings['show_search'],
			'show_sort'        => $settings['show_sort'],
			'show_image'       => $settings['show_image'],
			'show_tap_number'  => $settings['show_tap_number'],
			'show_style'       => $settings['show_style'],
			'show_abv'         => $settings['show_abv'],
			'show_ibu'         => $settings['show_ibu'],
			'show_description' => $settings['show_description'],
			'show_containers'  => $settings['show_containers'],
			'posts_per_page'   => $settings['posts_per_page'],
			'pagination'       => $settings['pagination'],
			'order_by'         => $settings['order_by'],
			'order'            => $settings['order'],
		);

		// Instantiate shortcode class and render
		$shortcode = new Shortcode();
		echo $shortcode->render_taplist( $atts );
	}
}
