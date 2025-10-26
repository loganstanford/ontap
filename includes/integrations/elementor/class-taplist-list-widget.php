<?php
/**
 * Elementor Taplist List Widget
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
 * Taplist List Widget class
 */
class Taplist_List_Widget extends Widget_Base {

	/**
	 * Get widget name
	 *
	 * @return string Widget name
	 */
	public function get_name() {
		return 'ontap_taplist_list';
	}

	/**
	 * Get widget title
	 *
	 * @return string Widget title
	 */
	public function get_title() {
		return __( 'Taplist List', 'ontap' );
	}

	/**
	 * Get widget icon
	 *
	 * @return string Widget icon
	 */
	public function get_icon() {
		return 'eicon-bullet-list';
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

		// Style Tab - List Item Section
		$this->start_controls_section(
			'section_style_item',
			array(
				'label' => __( 'List Item', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'item_background',
			array(
				'label'     => __( 'Background Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-beer-list-item' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} .ontap-beer-list-item',
			)
		);

		$this->add_responsive_control(
			'item_border_radius',
			array(
				'label'      => __( 'Border Radius', 'ontap' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-beer-list-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .ontap-beer-list-item',
			)
		);

		$this->add_responsive_control(
			'item_padding',
			array(
				'label'      => __( 'Padding', 'ontap' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-beer-list-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'item_spacing',
			array(
				'label'      => __( 'Spacing Between Items', 'ontap' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-beer-list-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Tab - Image Section
		$this->start_controls_section(
			'section_style_image',
			array(
				'label' => __( 'Image', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'image_width',
			array(
				'label'      => __( 'Width', 'ontap' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 50,
						'max' => 300,
					),
					'%'  => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-beer-list-item .ontap-beer-image' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;',
				),
			)
		);

		$this->add_responsive_control(
			'image_border_radius',
			array(
				'label'      => __( 'Border Radius', 'ontap' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ontap-beer-list-item .ontap-beer-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

		// Style Tab - Tap Number Section
		$this->start_controls_section(
			'section_style_tap_number',
			array(
				'label' => __( 'Tap Number', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'tap_number_color',
			array(
				'label'     => __( 'Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-tap-number' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tap_number_background',
			array(
				'label'     => __( 'Background Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-tap-number' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tap_number_typography',
				'selector' => '{{WRAPPER}} .ontap-tap-number',
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

		// Style Tab - Containers Section
		$this->start_controls_section(
			'section_style_containers',
			array(
				'label' => __( 'Containers/Pricing', 'ontap' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'containers_color',
			array(
				'label'     => __( 'Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-beer-containers' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'container_background',
			array(
				'label'     => __( 'Background Color', 'ontap' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ontap-container' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'containers_typography',
				'selector' => '{{WRAPPER}} .ontap-beer-containers',
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
			'layout'           => 'list',
			'columns'          => '1',
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
