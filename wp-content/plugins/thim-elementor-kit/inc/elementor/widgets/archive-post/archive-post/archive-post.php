<?php
namespace Elementor;

use Elementor\Plugin;
use Elementor\Group_Control_Image_Size;
use Elementor\Core\Kits\Controls\Repeater as Global_Style_Repeater;
use Elementor\Utils;

class Thim_Ekit_Widget_Archive_Post extends Widget_Base {

	protected $current_permalink;

	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_name() {
		return 'thim-ekits-archive-post';
	}

	public function get_title() {
		return esc_html__( 'Archive Post', 'thim-elementor-kit' );
	}

	public function get_icon() {
		return 'thim-eicon eicon-archive-posts';
	}

	public function get_categories() {
		return array( \Thim_EL_Kit\Elementor::CATEGORY_ARCHIVE_POST );
	}

	public function get_help_url() {
		return '';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Content', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'menu_id',
			array(
				'label'   => esc_html__( 'Skin', 'thim-elementor-kit' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => array(
					'classic' => esc_html__( 'Classic', 'thim-elementor-kit' ),
					'cards'   => esc_html__( 'Cards ( Coming soon )', 'thim-elementor-kit' ),
					'full'    => esc_html__( 'Full Content ( Coming soon )', 'thim-elementor-kit' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'              => esc_html__( 'Columns', 'thim-elementor-kit' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '3',
				'tablet_default'     => '2',
				'mobile_default'     => '1',
				'options'            => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'selectors'          => array(
					'{{WRAPPER}}' => '--thim-ekits-archive-post-columns: repeat({{VALUE}}, 1fr)',
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'thumbnail_enable',
			array(
				'label'   => esc_html__( 'Show Image', 'thim-elementor-kit' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_responsive_control(
			'thumbnail_position',
			array(
				'label'               => esc_html__( 'Image Position', 'thim-elementor-kit' ),
				'type'                => Controls_Manager::SELECT,
				'default'             => 'top',
				'options'             => array(
					'top'   => esc_html__( 'Top', 'thim-elementor-kit' ),
					'left'  => esc_html__( 'Left', 'thim-elementor-kit' ),
					'right' => esc_html__( 'Right', 'thim-elementor-kit' ),
					'none'  => esc_html__( 'None', 'thim-elementor-kit' ),
				),
				'devices'             => array( 'desktop', 'tablet' ),
				'prefix_class'        => 'thim-ekit-archive-post--thumbnail-position-%s',
				'prefix_class_tablet' => 'thim-ekits-pos-%s',
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'         => 'thumbnail_size',
				'default'      => 'medium',
				'prefix_class' => 'thim-ekits-posts--thumbnail-size-',
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'key',
			array(
				'label'   => esc_html__( 'Type', 'thim-elementor-kit' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'title',
				'options' => array(
					'title'     => 'Title',
					'meta_data' => 'Meta Data',
					'content'   => 'Content',
					'read_more' => 'Read more',
				),
			)
		);

		$repeater->add_control(
			'title_tag',
			array(
				'label'     => __( 'Title HTML Tag', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
				'default'   => 'h3',
				'condition' => array(
					'key' => 'title',
				),
			)
		);

		$repeater->add_control(
			'meta_data',
			array(
				'label'       => esc_html__( 'Meta Data', 'thim-elementor-kit' ),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT2,
				'default'     => array( 'date', 'comments' ),
				'multiple'    => true,
				'options'     => array(
					'author'   => esc_html__( 'Author', 'thim-elementor-kit' ),
					'date'     => esc_html__( 'Date', 'thim-elementor-kit' ),
					'comments' => esc_html__( 'Comments', 'thim-elementor-kit' ),
				),
				'condition'   => array(
					'key' => 'meta_data',
				),
			)
		);

		$repeater->add_control(
			'separator',
			array(
				'label'     => esc_html__( 'Separator Between', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '|',
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__meta span + span:before' => 'content: "{{VALUE}}"',
				),
				'condition' => array(
					'key' => 'meta_data',
				),
			)
		);

		$repeater->add_control(
			'excerpt_lenght',
			array(
				'label'     => esc_html__( 'Excerpt Lenght', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 25,
				'condition' => array(
					'key' => 'content',
				),
			)
		);

		$repeater->add_control(
			'excerpt_more',
			array(
				'label'     => esc_html__( 'Excerpt More', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '...',
				'condition' => array(
					'key' => 'content',
				),
			)
		);

		$repeater->add_control(
			'read_more_text',
			array(
				'label'     => esc_html__( 'Read More Text', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read More »', 'thim-elementor-kit' ),
				'condition' => array(
					'key' => 'read_more',
				),
			)
		);

		$this->add_control(
			'repeater',
			array(
				'label'       => esc_html__( 'Post Data', 'thim-elementor-kit' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'key' => 'title',
					),
					array(
						'key' => 'meta_data',
					),
					array(
						'key' => 'content',
					),
					array(
						'key' => 'read_more',
					),
				),
				'title_field' => '<span style="text-transform: capitalize;">{{{ key.replace("_", " ") }}}</span>',
			)
		);

		$this->add_control(
			'open_new_tab',
			array(
				'label'     => esc_html__( 'Open in new window', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'thim-elementor-kit' ),
				'label_off' => esc_html__( 'No', 'thim-elementor-kit' ),
				'default'   => 'no',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pagination',
			array(
				'label' => esc_html__( 'Pagination', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'pagination_type',
			array(
				'label'              => esc_html__( 'Pagination', 'thim-elementor-kit' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '',
				'options'            => array(
					''                          => esc_html__( 'None', 'thim-elementor-kit' ),
					'numbers'                   => esc_html__( 'Numbers', 'thim-elementor-kit' ),
					'prev_next'                 => esc_html__( 'Previous/Next', 'thim-elementor-kit' ),
					'numbers_and_prev_next'     => esc_html__( 'Numbers', 'thim-elementor-kit' ) . ' + ' . esc_html__( 'Previous/Next', 'thim-elementor-kit' ),
					'load_more_on_click'        => esc_html__( 'Load on Click (Comming Soon)', 'thim-elementor-kit' ),
					'load_more_infinite_scroll' => esc_html__( 'Infinite Scroll (Comming Soon)', 'thim-elementor-kit' ),
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'pagination_page_limit',
			array(
				'label'     => esc_html__( 'Page Limit', 'thim-elementor-kit' ),
				'default'   => '5',
				'condition' => array(
					'pagination_type!' => array(
						'load_more_on_click',
						'load_more_infinite_scroll',
						'',
					),
				),
			)
		);

		$this->add_control(
			'pagination_numbers_shorten',
			array(
				'label'     => esc_html__( 'Shorten', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => array(
					'pagination_type' => array(
						'numbers',
						'numbers_and_prev_next',
					),
				),
			)
		);

		$this->add_control(
			'pagination_prev_label',
			array(
				'label'     => __( 'Previous Label', 'thim-elementor-kit' ),
				'default'   => __( '&laquo; Previous', 'thim-elementor-kit' ),
				'condition' => array(
					'pagination_type' => array(
						'prev_next',
						'numbers_and_prev_next',
					),
				),
			)
		);

		$this->add_control(
			'pagination_next_label',
			array(
				'label'     => __( 'Next Label', 'thim-elementor-kit' ),
				'default'   => __( 'Next &raquo;', 'thim-elementor-kit' ),
				'condition' => array(
					'pagination_type' => array(
						'prev_next',
						'numbers_and_prev_next',
					),
				),
			)
		);

		$this->add_control(
			'pagination_align',
			array(
				'label'     => __( 'Alignment', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__pagination' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'pagination_type!' => array(
						'load_more_on_click',
						'load_more_infinite_scroll',
						'',
					),
				),
			)
		);

		$this->end_controls_section();

		$this->register_style_layout();
		$this->register_style_image();
		$this->register_style_content();
		$this->register_style_pagination();
	}

	protected function register_style_layout() {
		$this->start_controls_section(
			'section_design_layout',
			array(
				'label' => esc_html__( 'Layout', 'thim-elementor-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'column_gap',
			array(
				'label'     => esc_html__( 'Columns Gap', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 30,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--thim-ekits-archive-post-column-gap: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'row_gap',
			array(
				'label'              => esc_html__( 'Rows Gap', 'thim-elementor-kit' ),
				'type'               => Controls_Manager::SLIDER,
				'default'            => array(
					'size' => 35,
				),
				'range'              => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'frontend_available' => true,
				'selectors'          => array(
					'{{WRAPPER}}' => '--thim-ekits-archive-post-row-gap: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_image() {
		$this->start_controls_section(
			'section_style_image',
			array(
				'label' => esc_html__( 'Image', 'thim-elementor-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'img_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekit-archive-post__thumbnail > img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'thumbnail_enable' => 'yes',
				),
			)
		);

		$this->add_control(
			'image_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}.thim-ekit-archive-post--thumbnail-position-left .thim-ekit-archive-post__thumbnail' => 'margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.thim-ekit-archive-post--thumbnail-position-right .thim-ekit-archive-post__thumbnail' => 'margin-left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.thim-ekit-archive-post--thumbnail-position-top .thim-ekit-archive-post__thumbnail' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
				'default'   => array(
					'size' => 20,
				),
				'condition' => array(
					'thumbnail_enable' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .thim-ekit-archive-post__thumbnail > img',
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_box_shadow',
				'exclude'  => array(
					'box_shadow_position',
				),
				'selector' => '{{WRAPPER}} .thim-ekit-archive-post__thumbnail > img',
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_content() {
		$this->start_controls_section(
			'section_style_content',
			array(
				'label' => esc_html__( 'Content', 'thim-elementor-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'content_align',
			array(
				'label'     => esc_html__( 'Alignment', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'left',
				'toggle'    => true,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__content' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_post_padding',
			array(
				'label'      => esc_html__( 'Padding', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekit-archive-post__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'content_post_margin',
			array(
				'label'      => esc_html__( 'Margin', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekit-archive-post__content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'heading_title_style',
			array(
				'label' => esc_html__( 'Title', 'thim-elementor-kit' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .thim-ekit-archive-post__title a',
			)
		);

		$this->add_control(
			'title_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'heading_meta_style',
			array(
				'label'     => esc_html__( 'Meta', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'meta_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__meta' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'meta_separator_color',
			array(
				'label'     => esc_html__( 'Separator Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__meta span:before' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .thim-ekit-archive-post__meta',
			)
		);

		$this->add_control(
			'meta_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__meta' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'heading_excerpt_style',
			array(
				'label'     => esc_html__( 'Excerpt', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .thim-ekit-archive-post__excerpt',
			)
		);

		$this->add_control(
			'excerpt_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'heading_readmore_style',
			array(
				'label'     => esc_html__( 'Read More', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'read_more_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__read-more' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'read_more_typography',
				'selector' => '{{WRAPPER}} .thim-ekit-archive-post__read-more',
			)
		);

		$this->add_control(
			'read_more_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__read-more' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_pagination() {
		$this->start_controls_section(
			'section_style_pagination',
			array(
				'label' => esc_html__( 'Pagination', 'thim-elementor-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} .thim-ekit-archive-post__pagination',
			)
		);

		$this->add_control(
			'pagination_color_heading',
			array(
				'label'     => esc_html__( 'Colors', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->start_controls_tabs( 'pagination_colors' );

		$this->start_controls_tab(
			'pagination_color_normal',
			array(
				'label' => esc_html__( 'Normal', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'pagination_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__pagination .page-numbers:not(.dots)' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_color_hover',
			array(
				'label' => esc_html__( 'Hover', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'pagination_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__pagination a.page-numbers:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_color_active',
			array(
				'label' => esc_html__( 'Active', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'pagination_active_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekit-archive-post__pagination .page-numbers.current' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'pagination_spacing',
			array(
				'label'     => esc_html__( 'Space Between', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'separator' => 'before',
				'default'   => array(
					'size' => 10,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'body:not(.rtl) {{WRAPPER}} .thim-ekit-archive-post__pagination .page-numbers:not(:first-child)' => 'margin-left: calc( {{SIZE}}{{UNIT}}/2 );',
					'body:not(.rtl) {{WRAPPER}} .thim-ekit-archive-post__pagination .page-numbers:not(:last-child)' => 'margin-right: calc( {{SIZE}}{{UNIT}}/2 );',
					'body.rtl {{WRAPPER}} .thim-ekit-archive-post__pagination .page-numbers:not(:first-child)' => 'margin-right: calc( {{SIZE}}{{UNIT}}/2 );',
					'body.rtl {{WRAPPER}} .thim-ekit-archive-post__pagination .page-numbers:not(:last-child)' => 'margin-left: calc( {{SIZE}}{{UNIT}}/2 );',
				),
			)
		);

		$this->end_controls_section();
	}

	public function render() {
		global $wp_query;

		$query_vars = $wp_query->query_vars;

		$query_vars = apply_filters( 'thim_ekit/elementor/archive_post/query_posts/query_vars', $query_vars );

		if ( $query_vars !== $wp_query->query_vars ) {
			$query = new \WP_Query( $query_vars );
		} else {
			$query = $wp_query;
		}

		if ( ! $query->found_posts ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		?>

		<div class="thim-ekit-archive-post">
			<div class="thim-ekit-archive-post__inner">
				<?php
				if ( $query->in_the_loop ) { // It's the global `wp_query` it self. and the loop was started from the theme.
					$this->current_permalink = get_permalink();
					$this->render_post( $settings );
				} else {
					while ( $query->have_posts() ) {
						$query->the_post();

						$this->current_permalink = get_permalink();
						$this->render_post( $settings );
					}
				}

				wp_reset_postdata();
				?>
			</div>

			<?php $this->render_loop_footer( $query, $settings ); ?>
		</div>

		<?php
	}

	protected function render_loop_footer( $query, $settings ) {
		$ajax_pagination = in_array( $settings['pagination_type'], array( 'load_more_on_click', 'load_more_infinite_scroll' ), true );

		if ( '' === $settings['pagination_type'] ) {
			return;
		}

		$page_limit = $query->max_num_pages;

		if ( '' !== $settings['pagination_page_limit'] ) {
			$page_limit = min( $settings['pagination_page_limit'], $page_limit );
		}

		if ( 2 > $page_limit ) {
			return;
		}

		$has_numbers   = in_array( $settings['pagination_type'], array( 'numbers', 'numbers_and_prev_next' ) );
		$has_prev_next = in_array( $settings['pagination_type'], array( 'prev_next', 'numbers_and_prev_next' ) );

		$load_more_type = $settings['pagination_type'];

		if ( $settings['pagination_type'] === '' ) {
			$paged = 1;
		} else {
			$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
		}

		$links = array();

		if ( $has_numbers ) {
			$paginate_args = array(
				'type'               => 'array',
				'current'            => $paged,
				'total'              => $page_limit,
				'prev_next'          => false,
				'show_all'           => 'yes' !== $settings['pagination_numbers_shorten'],
				'before_page_number' => '<span class="elementor-screen-only">' . esc_html__( 'Page', 'thim-elementor-kit' ) . '</span>',
			);

			if ( is_singular() && ! is_front_page() ) {
				global $wp_rewrite;

				if ( $wp_rewrite->using_permalinks() ) {
					$paginate_args['base']   = trailingslashit( get_permalink() ) . '%_%';
					$paginate_args['format'] = user_trailingslashit( '%#%', 'single_paged' );
				} else {
					$paginate_args['format'] = '?page=%#%';
				}
			}

			$links = paginate_links( $paginate_args );
		}

		if ( $has_prev_next ) {
			$prev_next = $this->get_posts_nav_link( $query, $paged, $page_limit, $settings );
			array_unshift( $links, $prev_next['prev'] );
			$links[] = $prev_next['next'];
		}
		?>
		<nav class="thim-ekit-archive-post__pagination" aria-label="<?php esc_attr_e( 'Pagination', 'thim-elementor-kit' ); ?>">
			<?php echo wp_kses_post( implode( PHP_EOL, $links ) ); ?>
		</nav>
		<?php
	}

	public function get_posts_nav_link( $query, $paged, $page_limit = null, $settings = array() ) {
		if ( ! $page_limit ) {
			$page_limit = $query->max_num_pages;
		}

		$return = array();

		$link_template     = '<a class="page-numbers %s" href="%s">%s</a>';
		$disabled_template = '<span class="page-numbers %s">%s</span>';

		if ( $paged > 1 ) {
			$next_page = intval( $paged ) - 1;

			if ( $next_page < 1 ) {
				$next_page = 1;
			}

			$return['prev'] = sprintf( $link_template, 'prev', $this->get_wp_link_page( $next_page ), $settings['pagination_prev_label'] );
		} else {
			$return['prev'] = sprintf( $disabled_template, 'prev', $settings['pagination_prev_label'] );
		}

		$next_page = intval( $paged ) + 1;

		if ( $next_page <= $page_limit ) {
			$return['next'] = sprintf( $link_template, 'next', $this->get_wp_link_page( $next_page ), $settings['pagination_next_label'] );
		} else {
			$return['next'] = sprintf( $disabled_template, 'next', $settings['pagination_next_label'] );
		}

		return $return;
	}

	private function get_wp_link_page( $i ) {
		if ( ! is_singular() || is_front_page() ) {
			return get_pagenum_link( $i );
		}

		// Based on wp-includes/post-template.php:957 `_wp_link_page`.
		global $wp_rewrite;
		$post       = get_post();
		$query_args = array();
		$url        = get_permalink();

		if ( $i > 1 ) {
			if ( '' === get_option( 'permalink_structure' ) || in_array( $post->post_status, array( 'draft', 'pending' ) ) ) {
				$url = add_query_arg( 'page', $i, $url );
			} elseif ( get_option( 'show_on_front' ) === 'page' && (int) get_option( 'page_on_front' ) === $post->ID ) {
				$url = trailingslashit( $url ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
			} else {
				$url = trailingslashit( $url ) . user_trailingslashit( $i, 'single_paged' );
			}
		}

		if ( is_preview() ) {
			if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
				$query_args['preview_id']    = absint( wp_unslash( $_GET['preview_id'] ) );
				$query_args['preview_nonce'] = sanitize_text_field( wp_unslash( $_GET['preview_nonce'] ) );
			}

			$url = get_preview_post_link( $post, $query_args, $url );
		}

		return $url;

	}

	public function render_post( $settings ) {
		?>
		<article <?php post_class( array( 'thim-ekit-archive-post__article' ) ); ?>>
			<?php $this->render_thumbnail( $settings ); ?>

			<?php
			$this->render_text_header();

			if ( $settings['repeater'] ) {
				foreach ( $settings['repeater'] as $item ) {
					switch ( $item['key'] ) {
						case 'title':
							$this->render_title( $settings, $item );
							break;
						case 'content':
							$this->render_excerpt( $settings, $item );
							break;
						case 'meta_data':
							$this->render_meta_data( $settings, $item );
							break;
						case 'read_more':
							$this->render_read_more( $settings, $item );
							break;
					}
				}
			}

			$this->render_text_footer();
			?>
		</article>
		<?php
	}

	protected function render_text_header() {
		?>
		<div class="thim-ekit-archive-post__content">
		<?php
	}

	protected function render_text_footer() {
		?>
		</div>
		<?php
	}

	protected function render_thumbnail( $settings ) {
		if ( ! $settings['thumbnail_enable'] ) {
			return;
		}

		$settings['thumbnail_size'] = array(
			'id' => get_post_thumbnail_id(),
		);

		$thumbnail_html = Group_Control_Image_Size::get_attachment_image_html( $settings, 'thumbnail_size' );

		if ( empty( $thumbnail_html ) ) {
			return;
		}

		$attributes_html = $this->get_optional_link_attributes_html( $settings );
		?>
		<a class="thim-ekit-archive-post__thumbnail" href="<?php echo esc_url( $this->current_permalink ); ?>" <?php Utils::print_unescaped_internal_string( $attributes_html ); ?>>
			<?php echo wp_kses_post( $thumbnail_html ); ?>
		</a>
		<?php
	}

	protected function render_title( $settings, $item ) {
		$attributes_html = $this->get_optional_link_attributes_html( $settings );
		?>
		<<?php Utils::print_validated_html_tag( $item['title_tag'] ); ?> class="thim-ekit-archive-post__title">
			<a href="<?php echo esc_url( $this->current_permalink ); ?>" <?php Utils::print_unescaped_internal_string( $attributes_html ); ?>>
				<?php the_title(); ?>
			</a>
		</<?php Utils::print_validated_html_tag( $item['title_tag'] ); ?>>
		<?php
	}

	protected function get_optional_link_attributes_html( $settings ) {
		$attributes_html = 'yes' === $settings['open_new_tab'] ? 'target="_blank" rel="noopener noreferrer"' : '';

		return $attributes_html;
	}

	protected function render_excerpt( $settings, $item ) {
		?>

		<div class="thim-ekit-archive-post__excerpt">
			<?php wp_kses_post( wp_trim_words( get_the_excerpt(), absint( $item['excerpt_lenght'] ), esc_html( $item['excerpt_more'] ) ) ); ?>
		</div>

		<?php
	}

	protected function render_meta_data( $settings, $item ) {
		$meta_data = $item['meta_data'];
		?>

		<div class="thim-ekit-archive-post__meta">
			<?php
			if ( in_array( 'author', $meta_data ) ) {
				$this->render_author();
			}

			if ( in_array( 'date', $meta_data ) ) {
				$this->render_date_by_type();
			}
			if ( in_array( 'comments', $meta_data ) ) {
				$this->render_comments();
			}
			?>
		</div>

		<?php
	}

	protected function render_author() {
		?>
		<span class="thim-ekit-archive-post__author">
			<?php the_author(); ?>
		</span>
		<?php
	}

	protected function render_date_by_type() {
		$date = get_the_date();
		?>

		<span class="thim-ekit-archive-post__date">
			<?php echo esc_html( apply_filters( 'the_date', $date, get_option( 'date_format' ), '', '' ) ); ?>
		</span>

		<?php
	}

	protected function render_comments() {
		?>
		<span class="thim-ekit-archive-post__comments">
			<?php comments_number(); ?>
		</span>
		<?php
	}

	protected function render_read_more( $settings, $item ) {
		$attributes_html = $this->get_optional_link_attributes_html( $settings );
		?>
			<a class="thim-ekit-archive-post__read-more" href="<?php echo esc_url( $this->current_permalink ); ?>" <?php Utils::print_unescaped_internal_string( $attributes_html ); ?>>
				<?php echo esc_html( $item['read_more_text'] ); ?>
			</a>
		<?php
	}
}
