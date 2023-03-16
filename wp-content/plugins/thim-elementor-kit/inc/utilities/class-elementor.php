<?php
namespace Thim_EL_Kit\Utilities;

use Thim_EL_Kit\SingletonTrait;

class Elementor {
	use SingletonTrait;

	public function render_content_css( $id ) {
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css_file = new \Elementor\Core\Files\CSS\Post( $id );
			ob_start();
			?>
			<link rel="stylesheet" id="elementor-post-<?php echo esc_attr( $id ); ?>-css" href="<?php echo esc_url( $css_file->get_url() ); ?>" type="text/css" media="all">
			<?php
			return ob_get_clean();
		}
	}

	public function render_content( $id ) {
		return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id );
	}
}
