<?php
namespace Thim_EL_Kit;

class Shortcode {
	use SingletonTrait;

	const SHORTCODE_NAME = 'thim_ekit';

	public function __construct() {
		add_shortcode( self::SHORTCODE_NAME, array( $this, 'template_shortcode' ) );
		add_action( 'elementor/document/after_save', array( $this, 'clear_cache_after_save' ), 10, 2 );
	}

	public function template_shortcode( array $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts,
			self::SHORTCODE_NAME
		);

		$id = ! empty( $atts['id'] ) ? apply_filters( 'thim_ekit/shortcode/id', absint( $atts['id'] ) ) : '';

		if ( empty( $id ) ) {
			return '';
		}

		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css_class = new \Elementor\Core\Files\CSS\Post( $id );
		} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
			$css_class = new \Elementor\Post_CSS_File( $id );
		}

		if ( ! empty( $css_class ) ) {
			$css_class->enqueue();
		}

		return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id );
	}

	// Error shortcode cannot save resposive css when save in Editor.
	public function clear_cache_after_save( $document, $data ) {
		if ( get_the_ID() && get_post_type( get_the_ID() ) === Custom_Post_Type::CPT ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}
}

Shortcode::instance();
