<?php

namespace Elementor;

abstract class Thim_Ekit_Products_Base extends Widget_Base {

	protected function register_controls() {
		//		$this->register_style_products_controls();
		$this->register_style_product_controls();
		$this->register_style_pagination_controls();
		$this->register_style_sale_controls();
	}

	protected function register_layout_content() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Content', 'thim-elementor-kit' ),
			)
		);
		$this->register_content_image_thumbnail();

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'key',
			array(
				'label'   => esc_html__( 'Type', 'thim-elementor-kit' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'title',
				'options' => array(
					'title'     => esc_html__( 'Title', 'thim-elementor-kit' ),
					'meta_data' => esc_html__( 'Meta Data', 'thim-elementor-kit' ),
					'content'   => esc_html__( 'Content', 'thim-elementor-kit' ),
					'price'     => esc_html__( 'Price', 'thim-elementor-kit' ),
					'cart'      => esc_html__( 'Add To Cart', 'thim-elementor-kit' ),
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
			'meta_data_pr',
			array(
				'label'       => esc_html__( 'Meta Data', 'thim-elementor-kit' ),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT2,
				'default'     => array( 'attributes' ),
				'multiple'    => true,
				'sortable'    => true,
				'options'     => apply_filters( 'thim-kits/meta-data-item/product', array(
						'rating'     => esc_html__( 'Rating', 'thim-elementor-kit' ),
//						'compare'    => esc_html__( 'Compare', 'thim-elementor-kit' ),
//						'wishlist'   => esc_html__( 'Wishlist', 'thim-elementor-kit' ),
//						'quick_view' => esc_html__( 'Qucik View', 'thim-elementor-kit' ),
						'attributes' => esc_html__( 'Attributes', 'thim-elementor-kit' ),
						'cart'       => esc_html__( 'Icon Cart', 'thim-elementor-kit' ),
//						'countdown'  => esc_html__( 'Countdown', 'thim-elementor-kit' ),
						'price'      => esc_html__( 'Price', 'thim-elementor-kit' ),
					)
				),
				'condition'   => array(
					'key' => 'meta_data',
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
						'key' => 'price',
					),
					array(
						'key' => 'meta_data',
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

//		$this->start_controls_section(
//			'style_item_thumbnail',
//			[
//				'label' => __( 'Meta Data', 'thim-elementor-kit' ),
//				'tab'   => Controls_Manager::TAB_STYLE,
//			]
//		);
//		$this->register_style_countdown();

//		$this->register_style_meta_item_thumbnail( esc_html__( 'Quick View', 'thim-elementor-kit' ), 'quick-view' );
//		$this->register_style_meta_item_thumbnail( esc_html__( 'Compare', 'thim-elementor-kit' ), 'compare' );
//		$this->register_style_meta_item_thumbnail( esc_html__( 'WishList', 'thim-elementor-kit' ), 'wishlist' );

//		$this->end_controls_section();
	}

	protected function register_content_image_thumbnail() {
		$this->add_control(
			'show_image',
			array(
				'label'     => esc_html__( 'Show Image', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'show_onsale_flash',
			array(
				'label'        => esc_html__( 'Sale Flash', 'thim-elementor-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => esc_html__( 'Hide', 'thim-elementor-kit' ),
				'label_on'     => esc_html__( 'Show', 'thim-elementor-kit' ),
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array(
					'show_image' => 'yes',
				),
			)
		);
		$this->add_responsive_control(
			'thumbnail_position',
			array(
				'label'       => esc_html__( 'Image', 'thim-elementor-kit' ),
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'top',
				'options'     => array(
					'top'   => array(
						'title' => esc_html__( 'Top', 'thim-elementor-kit' ),
						'icon'  => 'eicon-v-align-top',
					),
					'left'  => array(
						'title' => esc_html__( 'Left', 'thim-elementor-kit' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right' => array(
						'title' => esc_html__( 'Right', 'thim-elementor-kit' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'render_type' => 'ui',
				//				'prefix_class' => 'list-product-img-layout-',
				'condition'   => array(
					'show_image' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'thumbnail_size',
				'default'   => 'full',
				'condition' => array(
					'show_image' => 'yes',
				),
			)
		);

		$meta_thumbnail = new \Elementor\Repeater();

		$meta_thumbnail->add_control(
			'meta_data_img',
			array(
				'label'       => esc_html__( 'Select Item', 'thim-elementor-kit' ),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT2,
				'default'     => array( 'cart' ),
				'multiple'    => true,
				'sortable'    => true,
				'options'     =>
					apply_filters( 'thim-kits/meta-data-thumb/product', array(
//							'compare'    => esc_html__( 'Compare', 'thim-elementor-kit' ),
//							'wishlist'   => esc_html__( 'Wishlist', 'thim-elementor-kit' ),
//							'quick_view' => esc_html__( 'Quick View', 'thim-elementor-kit' ),
							'cart'       => esc_html__( 'Cart', 'thim-elementor-kit' ),
//							'countdown'  => esc_html__( 'Countdown', 'thim-elementor-kit' ),
						)
					),
			)
		);
		$meta_thumbnail->add_control(
			'style_cart_thumb',
			array(
				'label'     => esc_html__( 'Style Cart', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'only-text',
				'options'   => array(
					'only-text' => esc_html__( 'Only Text', 'thim-elementor-kit' ),
					'only-icon' => esc_html__( 'Only Icon', 'thim-elementor-kit' ),
					'both'      => esc_html__( 'Both', 'thim-elementor-kit' ),
				),
				'condition' => array(
					'meta_data_img' => 'cart',
				),
			)
		);

		$meta_thumbnail->add_control(
			'meta_thumb_toggle',
			array(
				'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label'        => esc_html__( 'Options Style', 'thim-elementor-kit' ),
				'return_value' => 'yes',
			)
		);

		$meta_thumbnail->start_popover();
		$meta_thumbnail->add_control(
			'always_show',
			array(
				'label'     => esc_html__( 'Show & Hidden when hover', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'separator' => 'before',
			)
		);
		$meta_thumbnail->add_control(
			'display',
			array(
				'label'     => esc_html__( 'Display Item', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'column',
				'options'   => array(
					'row'    => esc_html__( 'Block', 'thim-elementor-kit' ),
					'column' => esc_html__( 'Inline', 'thim-elementor-kit' )
				),
				'selectors' => array(
					'{{WRAPPER}}  .product-image {{CURRENT_ITEM}}' => 'flex-direction: {{VALUE}};',
				),
			)
		);
		$meta_thumbnail->add_control(
			'item_spacing',
			array(
				'label'       => esc_html__( 'Item Spacing', 'thim-elementor-kit' ),
				'type'        => Controls_Manager::NUMBER,
				'label_block' => false,
				'min'         => 0,
				'step'        => 1,
				'default'     => 7,
				'condition'   => array(
					'meta_thumb_toggle' => 'yes',
				),
				'selectors'   => array(
					'{{WRAPPER}}  .product-image {{CURRENT_ITEM}}' => '--thim-item-meta-overlay-spacing: {{VALUE}}px;',
				),
			)
		);
		$meta_thumbnail->add_control(
			'item_position',
			array(
				'label'       => esc_html__( 'Position Vertical', 'thim-elementor-kit' ),
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'top',
				'options'     => array(
					'top'    => array(
						'title' => esc_html__( 'Top', 'thim-elementor-kit' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'thim-elementor-kit' ),
						'icon'  => ' eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => esc_html__( 'Bottom', 'thim-elementor-kit' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'condition'   => array(
					'meta_thumb_toggle' => 'yes',
				),
				'render_type' => 'ui',
				'selectors' => array(
					'{{WRAPPER}}  .product-image {{CURRENT_ITEM}}' => '{{VALUE}}',
				),
				'selectors_dictionary' => [
					'top' => 'top: 10px;',
					'center' => 'top: 50%;transform: translateY(-50%);',
					'bottom' => 'bottom: 10px; top:auto;',
				],
			)
		);
		$meta_thumbnail->add_control(
			'item_align',
			array(
				'label'     => esc_html__( 'Alignment', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left' => array(
						'title' => esc_html__( 'Left', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => esc_html__( 'Right', 'thim-elementor-kit' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'flex-start',
				'toggle'    => true,
				'condition' => array(
					'meta_thumb_toggle' => 'yes',
				),
				'selectors' => array(
					'{{WRAPPER}}  .product-image {{CURRENT_ITEM}}' => '{{VALUE}}',
				),
				'selectors_dictionary' => [
					'left' => 'left:0px;',
					'center' => 'left:50%; transform: translateX(-50%);',
					'right' => 'right:0px; left: auto;',
				],

			)
		);
		$meta_thumbnail->add_responsive_control(
			'offset_padding',
			array(
				'label'      => esc_html__( 'Padding', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}}  .product-image {{CURRENT_ITEM}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$meta_thumbnail->end_popover();

		$this->add_control(
			'repeater_meta_inner',
			array(
				'label'       => esc_html__( 'Item In Thumbnail', 'thim-elementor-kit' ),
				'label_block' => true,
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $meta_thumbnail->get_controls(),
				'default'     => array(
					array(
						'meta_data_img' => 'cart',
					),
				),
				'separator'   => 'after',
				'condition'   => array(
					'show_image' => 'yes',
				),
			)
		);
	}

	protected function register_style_products_controls() {
		$this->start_controls_section(
			'section_style_archive_product',
			array(
				'label' => esc_html__( 'Products', 'thim-elementor-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns_gap',
			array(
				'label'     => esc_html__( 'Columns Gap', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'rows_gap',
			array(
				'label'     => esc_html__( 'Rows Gap', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid' => 'row-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		//		$this->add_responsive_control(
		//			'align',
		//			array(
		//				'label'     => esc_html__( 'Alignment', 'thim-elementor-kit' ),
		//				'type'      => Controls_Manager::CHOOSE,
		//				'options'   => array(
		//					'left'   => array(
		//						'title' => esc_html__( 'Left', 'thim-elementor-kit' ),
		//						'icon'  => 'eicon-text-align-left',
		//					),
		//					'center' => array(
		//						'title' => esc_html__( 'Center', 'thim-elementor-kit' ),
		//						'icon'  => 'eicon-text-align-center',
		//					),
		//					'right'  => array(
		//						'title' => esc_html__( 'Right', 'thim-elementor-kit' ),
		//						'icon'  => 'eicon-text-align-right',
		//					),
		//				),
		//				'selectors' => array(
		//					'body {{WRAPPER}} .thim-ekits-product .product' => 'text-align: {{VALUE}}',
		//				),
		//			)
		//		);

		//		$this->add_control(
		//			'heading_image_style',
		//			array(
		//				'label'     => esc_html__( 'Image', 'thim-elementor-kit' ),
		//				'type'      => Controls_Manager::HEADING,
		//				'separator' => 'before',
		//			)
		//		);
		//
		//		$this->add_group_control(
		//			Group_Control_Border::get_type(),
		//			array(
		//				'name'     => 'image_border',
		//				'selector' => 'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .attachment-woocommerce_thumbnail',
		//			)
		//		);
		//
		//		$this->add_responsive_control(
		//			'image_border_radius',
		//			array(
		//				'label'      => esc_html__( 'Border Radius', 'thim-elementor-kit' ),
		//				'type'       => Controls_Manager::DIMENSIONS,
		//				'size_units' => array( 'px', '%' ),
		//				'selectors'  => array(
		//					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .attachment-woocommerce_thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		//				),
		//			)
		//		);
		//
		//		$this->add_group_control(
		//			Group_Control_Box_Shadow::get_type(),
		//			array(
		//				'name'     => 'image_box_shadow',
		//				'exclude'  => array(
		//					'box_shadow_position',
		//				),
		//				'selector' => 'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .attachment-woocommerce_thumbnail',
		//			)
		//		);
		//
		//		$this->add_responsive_control(
		//			'image_spacing',
		//			array(
		//				'label'      => esc_html__( 'Spacing', 'thim-elementor-kit' ),
		//				'type'       => Controls_Manager::SLIDER,
		//				'size_units' => array( 'px', 'em' ),
		//				'selectors'  => array(
		//					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .attachment-woocommerce_thumbnail' => 'margin-bottom: {{SIZE}}{{UNIT}}',
		//				),
		//			)
		//		);

		//		$this->add_control(
		//			'heading_title_style',
		//			array(
		//				'label'     => esc_html__( 'Product Title', 'thim-elementor-kit' ),
		//				'type'      => Controls_Manager::HEADING,
		//				'separator' => 'before',
		//			)
		//		);
		//
		//		$this->add_control(
		//			'title_color',
		//			array(
		//				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
		//				'type'      => Controls_Manager::COLOR,
		//				'selectors' => array(
		//					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .woocommerce-loop-product__title' => 'color: {{VALUE}}',
		//				),
		//			)
		//		);
		//
		//		$this->add_group_control(
		//			Group_Control_Typography::get_type(),
		//			array(
		//				'name'     => 'title_typography',
		//				'selector' => 'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .woocommerce-loop-product__title',
		//			)
		//		);
		//
		//		$this->add_responsive_control(
		//			'title_spacing',
		//			array(
		//				'label'     => esc_html__( 'Spacing', 'thim-elementor-kit' ),
		//				'type'      => Controls_Manager::SLIDER,
		//				'selectors' => array(
		//					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .woocommerce-loop-product__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
		//				),
		//			)
		//		);

		//		$this->add_control(
		//			'heading_rating_style',
		//			array(
		//				'label'     => esc_html__( 'Rating', 'thim-elementor-kit' ),
		//				'type'      => Controls_Manager::HEADING,
		//				'separator' => 'before',
		//			)
		//		);
		//
		//		$this->add_control(
		//			'star_color',
		//			array(
		//				'label'     => esc_html__( 'Star Color', 'thim-elementor-kit' ),
		//				'type'      => Controls_Manager::COLOR,
		//				'selectors' => array(
		//					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .star-rating' => 'color: {{VALUE}}',
		//				),
		//			)
		//		);
		//
		//		$this->add_control(
		//			'empty_star_color',
		//			array(
		//				'label'     => esc_html__( 'Empty Star Color', 'thim-elementor-kit' ),
		//				'type'      => Controls_Manager::COLOR,
		//				'selectors' => array(
		//					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .star-rating::before' => 'color: {{VALUE}}',
		//				),
		//			)
		//		);
		//
		//		$this->add_control(
		//			'star_size',
		//			array(
		//				'label'      => esc_html__( 'Star Size', 'thim-elementor-kit' ),
		//				'type'       => Controls_Manager::SLIDER,
		//				'size_units' => array( 'px', 'em' ),
		//				'default'    => array(
		//					'unit' => 'em',
		//				),
		//				'range'      => array(
		//					'em' => array(
		//						'min'  => 0,
		//						'max'  => 4,
		//						'step' => 0.1,
		//					),
		//				),
		//				'selectors'  => array(
		//					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .star-rating' => 'font-size: {{SIZE}}{{UNIT}}',
		//				),
		//			)
		//		);
		//
		//		$this->add_responsive_control(
		//			'rating_spacing',
		//			array(
		//				'label'      => esc_html__( 'Spacing', 'thim-elementor-kit' ),
		//				'type'       => Controls_Manager::SLIDER,
		//				'size_units' => array( 'px', 'em' ),
		//				'range'      => array(
		//					'em' => array(
		//						'min'  => 0,
		//						'max'  => 5,
		//						'step' => 0.1,
		//					),
		//				),
		//				'selectors'  => array(
		//					'body {{WRAPPER}} ul.products.thim-ekit-archive-product__grid .star-rating' => 'margin-bottom: {{SIZE}}{{UNIT}}',
		//				),
		//			)
		//		);

		$this->add_control(
			'heading_price_style',
			array(
				'label'     => esc_html__( 'Price', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .price'             => 'color: {{VALUE}}',
					'{{WRAPPER}} .thim-ekits-product .product .price ins'         => 'color: {{VALUE}}',
					'{{WRAPPER}} .thim-ekits-product .product .price ins .amount' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'price_typography',
				'selector' => '{{WRAPPER}} .thim-ekits-product .product .price',
			)
		);

		$this->add_control(
			'heading_regular_price_style',
			array(
				'label'     => esc_html__( 'Regular Price', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'regular_price_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .price del'         => 'color: {{VALUE}}',
					'{{WRAPPER}} .thim-ekits-product .product .price del .amount' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'regular_price_typography',
				'selector' => '{{WRAPPER}} .thim-ekits-product .product .price del .amount ',
				'selector' => '{{WRAPPER}} .thim-ekits-product .product .price del',
			)
		);

		$this->add_control(
			'heading_button_style',
			array(
				'label'     => esc_html__( 'Button', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => esc_html__( 'Normal', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					 {{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .thim-ekits-product .product .button,
							   {{WRAPPER}} .thim-ekits-product .product .added_to_cart',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => esc_html__( 'Hover', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button:hover,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button:hover,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button:hover,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'exclude'  => array( 'color' ),
				'selector' => '{{WRAPPER}} .thim-ekits-product .product .button,
				{{WRAPPER}} .thim-ekits-product .product .added_to_cart',
			)
		);

		$this->add_control(
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'em' => array(
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'body {{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'margin-bottom: {{SIZE}}{{UNIT}}',
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
					'{{WRAPPER}} .thim-ekits-product .product-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .thumbnail-position-left .product-image'  => 'margin-left: {{SIZE}}{{UNIT}};margin-bottom: 0px;',
					'{{WRAPPER}} .thumbnail-position-right .product-image' => 'margin-right: {{SIZE}}{{UNIT}};margin-bottom: 0px;',
					'{{WRAPPER}} .thumbnail-position-top .product-image'   => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
				'default'   => array(
					'size' => 20,
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .thim-ekits-product .product-image',
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
				'selector' => '{{WRAPPER}} .thim-ekits-product .product-image',
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
			'align',
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
					'{{WRAPPER}} .thim-ekits-product .wrapper-content-item'       => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .thim-ekits-product .wrapper-content-item .star-rating' => 'justify-content: {{VALUE}};float:unset;display: inline-block;',
				),
			)
		);
		$this->add_responsive_control(
			'content_padding',
			array(
				'label'      => esc_html__( 'Padding', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product .wrapper-content-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .thim-ekits-product .woocommerce-loop-product_title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .thim-ekits-product .woocommerce-loop-product_title a',
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
					'{{WRAPPER}} .thim-ekits-product .woocommerce-loop-product_title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .product-item-actions > div > svg > path' => 'fill: {{VALUE}};stroke:{{VALUE}}',
				),
			)
		);

		$this->add_control(
			'meta_separator_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-item-actions > div ' => 'background-color: {{VALUE}};',
				),
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
					'{{WRAPPER}} .product-item-actions > div' => 'margin-bottom: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .product-excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .product-excerpt',
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
					'{{WRAPPER}} .product-excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'heading_price_style',
			array(
				'label'     => esc_html__( 'Price', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control(
			'price_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .price'             => 'color: {{VALUE}}',
					'{{WRAPPER}} .thim-ekits-product .product .price ins'         => 'color: {{VALUE}}',
					'{{WRAPPER}} .thim-ekits-product .product .price .amount' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'price_typography',
				'selector' => '{{WRAPPER}} .thim-ekits-product .woocommerce-Price-amount',
			)
		);

		$this->add_control(
			'price_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .price' => 'margin-bottom: {{SIZE}}{{UNIT}};margin-bottom: 44px !important;
					display: inline-block;
					width: 100%;',
				),
			)
		);
		$this->add_control(
			'heading_regular_price_style',
			array(
				'label'     => esc_html__( 'Regular Price', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'regular_price_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .price del'         => 'color: {{VALUE}}',
					'{{WRAPPER}} .thim-ekits-product .price del .woocommerce-Price-amount.amount' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'regular_price_typography',
				'selector' => '{{WRAPPER}} .thim-ekits-product .price del .amount.woocommerce-Price-amount ,{{WRAPPER}} .thim-ekits-product .price del',
			)
		);

		$this->add_control(
			'heading_rating_style',
			array(
				'label'     => esc_html__( 'Rating', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'star_color',
			array(
				'label'     => esc_html__( 'Star Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'body {{WRAPPER}} .thim-ekits-product .star-rating span::before' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'empty_star_color',
			array(
				'label'     => esc_html__( 'Empty Star Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'body {{WRAPPER}} .thim-ekits-product .star-rating::before' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'star_size',
			array(
				'label'      => esc_html__( 'Star Size', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'unit' => 'em',
				),
				'range'      => array(
					'em' => array(
						'min'  => 0,
						'max'  => 4,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'body {{WRAPPER}} .thim-ekits-product .star-rating' => 'font-size: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'rating_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'em' => array(
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'body {{WRAPPER}} .thim-ekits-product .star-rating' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);
		$this->add_control(
			'heading_button_style',
			array(
				'label'     => esc_html__( 'Button', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),

				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'exclude'  => array( 'color' ),
				'selector' => '{{WRAPPER}} .thim-ekits-product .product .button,
				{{WRAPPER}} .thim-ekits-product .product .added_to_cart',
			)
		);

		$this->add_control(
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'button_ưidth',
			array(
				'label'      => esc_html__( 'Width', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => [
						'min' => 0,
						'max' => 250,
						'step' => 5,
					],
				),
				'selectors'  => array(
					'body {{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);
		$this->add_responsive_control(
			'button_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'em' => array(
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'body {{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);
		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => esc_html__( 'Normal', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					 {{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'button_border_border!' => [ 'none', '' ],
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .thim-ekits-product .product .button,
							   {{WRAPPER}} .thim-ekits-product .product .added_to_cart',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => esc_html__( 'Hover', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button:hover,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button:hover,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'button_border_border!' => [ 'none', '' ],
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product .button:hover,
					{{WRAPPER}} .thim-ekits-product .product .added_to_cart:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_product_controls() {
		$this->start_controls_section(
			'section_style_product',
			array(
				'label' => esc_html__( 'Product', 'thim-elementor-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'product_border',
				'exclude'  => array( 'color' ),
				'selector' => '{{WRAPPER}} .thim-ekits-product .product',
			)
		);

		$this->add_control(
			'product_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'product_padding',
			array(
				'label'      => esc_html__( 'Padding', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'product_style_tabs' );

		$this->start_controls_tab(
			'product_style_normal',
			array(
				'label' => esc_html__( 'Normal', 'thim-elementor-kit' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'product_shadow',
				'selector' => '{{WRAPPER}} .thim-ekits-product .product',
			)
		);

		$this->add_control(
			'product_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'product_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'product_border_border!' => [ 'none', '' ],
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'product_style_hover',
			array(
				'label' => esc_html__( 'Hover', 'thim-elementor-kit' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'product_shadow_hover',
				'selector' => '{{WRAPPER}} .thim-ekits-product .product:hover',
			)
		);

		$this->add_control(
			'product_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'product_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'product_border_border!' => [ 'none', '' ],
				),
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_pagination_controls() {
		$this->start_controls_section(
			'section_pagination_style',
			array(
				'label'     => esc_html__( 'Pagination', 'thim-elementor-kit' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'paginate' => 'yes',
				),
			)
		);

		$this->add_control(
			'logo_align',
			array(
				'label'        => esc_html__( 'Alignment', 'thim-elementor-kit' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
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
				'prefix_class' => 'thim-ekit-archive-product--pagination--align--',
				'selectors'    => array(
					'{{WRAPPER}} nav.woocommerce-pagination' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_gap',
			array(
				'label'          => esc_html__( 'Columns Gap', 'thim-elementor-kit' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => array(
					'unit' => 'px',
				),
				'tablet_default' => array(
					'unit' => 'px',
				),
				'mobile_default' => array(
					'unit' => 'px',
				),
				'size_units'     => array( 'px', 'em' ),
				'range'          => array(
					'px' => array(
						'min' => 1,
						'max' => 100,
					),
					'em' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'selectors'      => array(
					'{{WRAPPER}}.thim-ekit-archive-product--pagination--align--left nav.woocommerce-pagination ul li'   => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.thim-ekit-archive-product--pagination--align--right nav.woocommerce-pagination ul li'  => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.thim-ekit-archive-product--pagination--align--center nav.woocommerce-pagination ul li' => 'margin-left: calc( {{SIZE}}{{UNIT}} / 2 ); margin-right: calc( {{SIZE}}{{UNIT}} / 2 );',
				),
			)
		);

		$this->add_control(
			'pagination_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} nav.woocommerce-pagination' => 'margin-top: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pagination_border',
				'exclude'  => array( 'color' ),
				'selector' => '{{WRAPPER}} nav.woocommerce-pagination ul li a, {{WRAPPER}} nav.woocommerce-pagination ul li span',
			)
		);

		$this->add_responsive_control(
			'pagination_padding',
			array(
				'label'      => esc_html__( 'Padding', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} nav.woocommerce-pagination ul li a, {{WRAPPER}} nav.woocommerce-pagination ul li span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} nav.woocommerce-pagination',
			)
		);

		$this->start_controls_tabs( 'pagination_style_tabs' );

		$this->start_controls_tab(
			'pagination_style_normal',
			array(
				'label' => esc_html__( 'Normal', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'pagination_link_color',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} nav.woocommerce-pagination ul li a' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'pagination_link_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} nav.woocommerce-pagination ul li a' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_style_hover',
			array(
				'label' => esc_html__( 'Hover', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'pagination_link_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} nav.woocommerce-pagination ul li a:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'pagination_link_bg_color_hover',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} nav.woocommerce-pagination ul li a:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_style_active',
			array(
				'label' => esc_html__( 'Active', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'pagination_link_color_active',
			array(
				'label'     => esc_html__( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} nav.woocommerce-pagination ul li span.current' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'pagination_link_bg_color_active',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} nav.woocommerce-pagination ul li span.current' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_style_sale_controls() {
		$this->start_controls_section(
			'section_sale_style',
			array(
				'label'     => esc_html__( 'Sale Badge', 'thim-elementor-kit' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_onsale_flash' => 'yes',
					'show_image'        => 'yes',
				),
			)
		);

		$this->add_control(
			'onsale_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product span.onsale' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'onsale_text_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .thim-ekits-product .product span.onsale' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'onsale_typography',
				'selector' => '{{WRAPPER}} .thim-ekits-product .product span.onsale',
			)
		);

		$this->add_control(
			'onsale_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product span.onsale' => 'border-radius: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'onsale_width',
			array(
				'label'      => esc_html__( 'Width', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product span.onsale' => 'min-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'onsale_height',
			array(
				'label'      => esc_html__( 'Height', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product span.onsale' => 'min-height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'onsale_horizontal_position',
			array(
				'label'                => esc_html__( 'Position', 'thim-elementor-kit' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'  => array(
						'title' => esc_html__( 'Left', 'thim-elementor-kit' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right' => array(
						'title' => esc_html__( 'Right', 'thim-elementor-kit' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors'            => array(
					'{{WRAPPER}} .thim-ekits-product .product span.onsale' => '{{VALUE}}',
				),
				'selectors_dictionary' => array(
					'left'  => 'right: auto; left: 0',
					'right' => 'left: auto; right: 0',
				),
			)
		);

		$this->add_control(
			'onsale_distance',
			array(
				'label'      => esc_html__( 'Distance', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => - 20,
						'max' => 20,
					),
					'em' => array(
						'min' => - 2,
						'max' => 2,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .thim-ekits-product .product span.onsale' => 'margin: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_meta_item_thumbnail( $label, $class ) {

		$this->add_control(
			"heading_{$class}_style",
			array(
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			$class . '_font_size',
			array(
				'label'      => esc_html__( 'Font Size', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],

				'range'     => array(
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				),
				'selectors' => array(
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class . ' svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			$class . '_width',
			array(
				'label'      => esc_html__( 'Width', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => array(
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'px' => [
						'min'  => 0,
						'max'  => 150,
						'step' => 1,
					],
				),
				'selectors'  => array(
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			$class . '_height',
			array(
				'label'      => esc_html__( 'Height', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => array(
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'px' => [
						'min'  => 0,
						'max'  => 150,
						'step' => 1,
					],
				),
				'selectors'  => array(
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => $class . '_settings_border',
				'exclude'  => array( 'color' ),
				'selector' => '{{WRAPPER}} .inner-item-product .wcbt-product-' . $class,
			)
		);
		$this->add_responsive_control(
			$class . '_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( $class . '_tabs_color_settings_style' );
		$this->start_controls_tab(
			$class . '_tab_color_normal',
			[
				'label' => esc_html__( 'Normal', 'thim-elementor-kit' ),
			]
		);

		$this->add_control(
			$class . '_color',
			[
				'label'     => __( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class . ' svg path' => 'stroke: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$class . '_border_color',
			[
				'label'     => __( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					$class . '_settings_border_border!' => [ 'none', '' ]
				],
				'selectors' => [
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$class . '_bg_color',
			[
				'label'     => __( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			$class . '_tab_color_hover',
			[
				'label' => esc_html__( 'Hover', 'thim-elementor-kit' ),
			]
		);
		$this->add_control(
			$class . '_icon_hover',
			[
				'label'     => __( 'Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class . ':hover svg path' => 'stroke: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$class . '_border_color_hover',
			[
				'label'     => __( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class . ':hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					$class . '_settings_border_border!' => [ 'none', '' ]
				],
			]
		);
		$this->add_control(
			$class . '_bg_color_hover',
			[
				'label'     => __( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .inner-item-product .wcbt-product-' . $class . ':hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();


	}

	protected function register_style_countdown() {
		$this->add_control(
			"heading_countdown_style",
			array(
				'label' => __( 'Countdown', 'thim-elementor-kit' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'cd_number_font_size',
			array(
				'label'      => esc_html__( 'Number Size', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => array(
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				),
				'selectors'  => array(
					'{{WRAPPER}} .inner-item-product countdown-timer p' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'cd_text_typography',
				'label'    => esc_html__( 'Typography Text', 'thim-elementor-kit' ),
				'selector' => '{{WRAPPER}} .inner-item-product countdown-timer span',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'cd_border',
				'exclude'  => array( 'color' ),
				'selector' => '{{WRAPPER}} .inner-item-product countdown-timer > div',
			)
		);
		$this->add_responsive_control(
			'cd_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .inner-item-product countdown-timer > div' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'cd_padding',
			[
				'label'      => esc_html__( 'Padding', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .inner-item-product countdown-timer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'cd_item_spacing',
			array(
				'label'      => esc_html__( 'Spacing item', 'thim-elementor-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => array(
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				),
				'selectors'  => array(
					'{{WRAPPER}} .inner-item-product countdown-timer > div' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'cd_number_color',
			[
				'label'     => __( 'Number Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .inner-item-product countdown-timer p' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'cd_color',
			[
				'label'     => __( 'Text Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .inner-item-product countdown-timer span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cd_border_color',
			[
				'label'     => __( 'Border Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'cd_border_border!' => [ 'none', '' ]
				],
				'selectors' => [
					'{{WRAPPER}} .inner-item-product countdown-timer > div' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cd_bg_color',
			[
				'label'     => __( 'Background Color', 'thim-elementor-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .inner-item-product countdown-timer > div' => 'background-color: {{VALUE}};',
				]
			]
		);
	}

	protected function render_item_product( $settings ) {
		$class_item = $settings['thumbnail_position'] ? ' thumbnail-position-' . $settings['thumbnail_position'] : '';
		?>
		<div class="inner-item-product<?php echo $class_item; ?>">
			<?php $this->render_image_product( $settings ); ?>

			<?php
			if ( $settings['repeater'] ) {
				echo '<div class="wrapper-content-item">';
				foreach ( $settings['repeater'] as $item ) {
					switch ( $item['key'] ) {
						case 'title':
							$this->render_title( $settings );
							break;
						case 'content':
							$this->render_excerpt( $item );
							break;
						case 'price':
							$this->render_price();
							break;
						case 'cart' :
							$this->render_cart();
							break;
						case 'meta_data':
							$this->render_meta_data( $item );
							break;
					}
				}
				echo '</div>';
			}
			?>
		</div>
		<?php
	}

	protected function render_image_product( $settings ) {
		if ( ! $settings['show_image'] ) {
			return;
		}
		$attributes_html = ( isset( $settings['open_new_tab'] ) && $settings['open_new_tab'] == 'yes' ) ? ' target="_blank" rel="noopener noreferrer"' : '';
		?>
		<div class="product-image">
			<?php do_action( 'thim-ekit/before-product-image' ); ?>

			<a href="<?php echo esc_url( the_permalink() ) ?>" title="<?php the_title(); ?>"<?php echo
			$attributes_html; ?> ><?php echo woocommerce_get_product_thumbnail( $settings['thumbnail_size_size'] ); ?></a>

			<?php
			if ( $settings['show_onsale_flash'] == 'yes' ) {
				woocommerce_show_product_loop_sale_flash();
			}

			if ( $settings['repeater_meta_inner'] ) {
				foreach ( $settings['repeater_meta_inner'] as $item ) {
					$this->render_meta_data_inner_image( $item );
				}
			}
			do_action( 'thim-ekit/after-product-image' );
			?>
		</div>
		<?php
	}

	protected function render_meta_data_inner_image( $item ) {
		$meta_data = $item['meta_data_img'];
		if ( ! $meta_data ) {
			return;
		}
		$class = ' product-item-actions';
		//$class .= ' item-actions-ps-' . $item['item_position'];
		if ( $item['always_show'] == 'yes' ) {
			$class .= ' always-show';
		}
		echo '<div class="elementor-repeater-item-' . esc_attr( $item['_id'] . $class ) . '">';
		if(is_array($meta_data)){
			foreach ( $meta_data as $data ) {
				switch ( $data ) {

					case 'compare':
						$this->render_compare();
						break;
					case 'wishlist':
						$this->render_wishlist();
						break;
					case 'quick_view':
						$this->render_quick_view();
						break;
					case 'attributes':
						$this->render_attributes();
						break;
					case 'cart':
						$this->render_cart();
					case 'countdown':
						$this->render_countdown_timer();
						break;
				}
			}
		}

		echo '</div>';
	}

	protected function render_meta_data( $item ) {
		$meta_data = $item['meta_data_pr'];
		?>
		<div class="product-item-meta elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">
			<?php
			foreach ( $meta_data as $key => $data ) {
				switch ( $data ) {
					case 'price':
						$this->render_price();
						break;
					case'compare':
						$this->render_compare();
						break;
					case'wishlist':
						$this->render_wishlist();
						break;
					case'quick_view':
						$this->render_quick_view();
						break;
					case'attributes':
						$this->render_attributes();
						break;
					case'cart':
						$this->render_cart();
						break;
					case'rating':
						$this->render_rating();
						break;
				}
			}
			echo wp_kses_post( apply_filters( 'thim-kits/render-meta-data/product', '', $meta_data ) );
			?>
		</div>
		<?php
	}

	protected function render_countdown_timer() {

	}

	protected function render_title( $settings ) {
		$attributes_html = ( isset( $settings['open_new_tab'] ) && $settings['open_new_tab'] == 'yes' ) ? 'target="_blank" rel="noopener noreferrer"' : '';
		?>
		<h2 class="woocommerce-loop-product_title">
			<a href="<?php echo esc_url( the_permalink() ) ?>"
			   title="<?php the_title(); ?>"<?php echo $attributes_html; ?> ><?php the_title(); ?></a>
		</h2>
	<?php }

	protected function render_excerpt( $item ) { ?>
		<div class="product-excerpt">
			<?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), absint( $item['excerpt_lenght'] ), esc_html( $item['excerpt_more'] ) ) ); ?>
		</div>
		<?php
	}

	protected function render_price() {
		woocommerce_template_loop_price();
	}

	protected function render_compare() {
		if ( shortcode_exists( 'wcbt_compare_btn' ) ) {
			echo do_shortcode( '[wcbt_compare_btn ' . get_the_ID() . ']' );
		}
	}

	protected function render_quick_view() {
		if ( shortcode_exists( 'wcbt_quick_view_btn' ) ) {
			echo do_shortcode( '[wcbt_quick_view_btn ' . get_the_ID() . ']' );
		}
	}

	protected function render_wishlist() {
		if ( shortcode_exists( 'wcbt_wishlist_btn' ) ) {
			echo do_shortcode( '[wcbt_wishlist_btn ' . get_the_ID() . ']' );
		}
	}

	protected function render_attributes() {
		//		global $product;
		//		if ($product->is_type('variable')) :
		//			// Enqueue variation scripts.
		//			wp_enqueue_script( 'wc-add-to-cart-variation' );
		//			// Load the template.
		//			thim_layout_attribute($product->get_available_variations(),$product->get_variation_attributes(),$product->get_default_attributes());
		//		endif;
	}

	protected function render_cart() {
		woocommerce_template_loop_add_to_cart();
	}

	protected function render_rating() {
		woocommerce_template_loop_rating();
	}
}
