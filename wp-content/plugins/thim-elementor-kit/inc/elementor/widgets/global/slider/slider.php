<?php

namespace Elementor;

use Elementor\Plugin;
use Thim_EL_Kit\GroupControlTrait;

class Thim_Ekit_Widget_Slider extends Widget_Base {
	use GroupControlTrait;

	public function get_name() {
		return 'thim-ekits-slider';
	}

	public function get_title() {
		return esc_html__( 'Slider', 'thim-elementor-kit' );
	}

	public function get_icon() {
		return 'thim-eicon eicon-slider-3d';
	}

	public function get_categories() {
		return array( \Thim_EL_Kit\Elementor::CATEGORY );
	}

	public function get_keywords() {
		return [
			'thim',
			'tab',
			'tabs',
		];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'setting',
			[
				'label' => esc_html__( 'General', 'thim-elementor-kit' ),
			]
		);

		$this->add_control(
			'slider_id',
			[
				'label'       => __( 'Slider', 'thim-elementor-kit' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => false,
				'options'     => \Thim_EL_Kit\Elementor::get_cat_taxonomy( 'thim_ekits_slider' ),
				'default'     => 'choose',
				'label_block' => true,

			]
		);

		$this->end_controls_section();

		$this->_register_setting_slider();

		$this->_register_setting_slider_dot_style();

		$this->_register_setting_slider_nav_style();

	}

	protected function _register_setting_slider() {
		// setting slider section

		$this->start_controls_section(
			'skin_slider_settings',
			array(
				'label' => esc_html__( 'Settings Slider', 'thim-elementor-kit' ),
			)
		);

		$this->add_control(
			'slider_speed',
			array(
				'label'   => esc_html__( 'Speed', 'thim-elementor-kit' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 10000,
				'step'    => 1,
				'default' => 1000,
				//'frontend_available' => true,
			)
		);

		$this->add_control(
			'slider_autoplay',
			array(
				'label'        => esc_html__( 'Autoplay', 'thim-elementor-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'thim-elementor-kit' ),
				'label_off'    => esc_html__( 'No', 'thim-elementor-kit' ),
				'return_value' => 'yes',
				'default'      => 'no',
				//'frontend_available' => true,
			)
		);

		$this->add_control(
			'pause_on_interaction',
			array(
				'label'        => esc_html__( 'Pause on Interaction', 'thim-elementor-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'thim-elementor-kit' ),
				'label_off'    => esc_html__( 'No', 'thim-elementor-kit' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				//'frontend_available' => true,
				'condition'    => array(
					'slider_autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'        => esc_html__( 'Pause on Hover', 'thim-elementor-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Yes', 'thim-elementor-kit' ),
				'label_off'    => esc_html__( 'No', 'thim-elementor-kit' ),
				'return_value' => 'yes',
				//'frontend_available' => true,
				'condition'    => array(
					'slider_autoplay' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'slider_show_arrow',
			array(
				'label'        => esc_html__( 'Show Arrow', 'thim-elementor-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'thim-elementor-kit' ),
				'label_off'    => esc_html__( 'No', 'thim-elementor-kit' ),
				'return_value' => 'yes',
				'default'      => '',
				//'frontend_available' => true,
			)
		);

		$this->add_control(
			'slider_show_pagination',
			array(
				'label'   => esc_html__( 'Pagination Options', 'thim-elementor-kit' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'        => esc_html__( 'Hide', 'thim-elementor-kit' ),
					'bullets'     => esc_html__( 'Bullets', 'thim-elementor-kit' ),
					'number'      => esc_html__( 'Number', 'thim-elementor-kit' ),
					'progressbar' => esc_html__( 'Progress', 'thim-elementor-kit' ),
					'scrollbar'   => esc_html__( 'Scrollbar', 'thim-elementor-kit' ),
					'fraction'    => esc_html__( 'Fraction', 'thim-elementor-kit' ),
				),
				//'frontend_available' => true,
			)
		);

		$this->add_control(
			'slider_loop',
			array(
				'label'        => esc_html__( 'Enable Loop?', 'thim-elementor-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'thim-elementor-kit' ),
				'label_off'    => esc_html__( 'No', 'thim-elementor-kit' ),
				'return_value' => 'yes',
				'default'      => '',
				//'frontend_available' => true,
			)
		);

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$slider_settings = [
			'slidesPerView'          => 1,
			'slidesPerGroup'         => 1,
			'spaceBetween'           => 0,
			'slider_speed'           => $settings['slider_speed'],
			'slider_show_pagination' => $settings['slider_show_pagination'] ?? 'no',
			'slider_autoplay'        => $settings['slider_autoplay'] ?? 'false',
			'slider_show_arrow'      => $settings['slider_show_arrow'] ?? 'no',
			'slider_loop'            => $settings['slider_loop'] ?? 'false',
		];

		if ( isset( $settings['pause_on_interaction'] ) && $settings['pause_on_interaction'] ) {
			$slider_settings ['pause_on_interaction'] = $settings['pause_on_interaction'];
		}

		if ( isset( $settings['pause_on_hover'] ) && $settings['pause_on_hover'] ) {
			$slider_settings ['pause_on_hover'] = $settings['pause_on_hover'];
		}
		$this->add_render_attribute( '_wrapper', 'data-settings', wp_json_encode( $slider_settings ) );


		if ( empty( $settings['slider_id'] ) ) {
			return;
		}

		$query_args = array(
			'post_type'           => 'thim_ekits_slide',
			'posts_per_page'      => - 1,
			'orderby'             => 'menu_order',
			'order'               => 'ASC',
			'ignore_sticky_posts' => true,
			'tax_query'           => array(
				array(
					'taxonomy' => 'thim_ekits_slider',
					'field'    => 'term_id',
					'terms'    => $settings['slider_id'],
				),
			)
		);

		$slides = get_posts( $query_args );
		if ( is_wp_error( $slides ) || empty( $slides ) ) {
			return;
		}

		$this->render_nav_pagination_slider( $settings );
		$swiper_class = \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_swiper_latest' ) ? 'swiper' : 'swiper-container';
		$class       = 'thim-ekits-sliders ' . $swiper_class;
		?>

		<div class="<?php echo esc_attr($class); ?>>">
			<div class="swiper-wrapper">
				<?php
				foreach ( $slides as $slide ) :
					echo '<div class="swiper-slide">';
					echo \Thim_EL_Kit\Utilities\Elementor::instance()->render_content( $slide->ID );
					echo '</div>';
				endforeach;
				?>
			</div>
		</div>

		<?php
	}
}
