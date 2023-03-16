<?php

namespace Thim_EL_Kit;

use Elementor\Plugin;

/**
 *
 * Manage all ajax request for Thim Kit
 *
 * @class       Ajax_Handler
 * @since       1.0.8
 */
trait Ajax_Handler {

	/**
	 * init_ajax_hooks
	 */
	public static function get_widget_settings( $page_id, $widget_id ) {
		$document = Plugin::$instance->documents->get( $page_id );
		$settings = [];
		if ( $document ) {
			$elements    = Plugin::instance()->documents->get( $page_id )->get_elements_data();
			$widget_data = self::element_recursive( $elements, $widget_id );
			if ( ! empty( $widget_data ) && is_array( $widget_data ) ) {
				$widget = Plugin::instance()->elements_manager->create_element_instance( $widget_data );
			}
			if ( ! empty( $widget ) ) {
				$settings = $widget->get_settings_for_display();
			}
		}

		return $settings;
	}

	/**
	 * Get Widget data.
	 *
	 * @param array  $elements Element array.
	 * @param string $form_id  Element ID.
	 *
	 * @return bool|array
	 */
	public static function element_recursive( $elements, $form_id ) {

		foreach ( $elements as $element ) {
			if ( $form_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = self::element_recursive( $element['elements'], $form_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}

	public function init_ajax_hooks() {
		// if ( class_exists( 'WooCommerce' ) ) {
		add_action( 'wp_ajax_thim_load_content', array( $this, 'ajax_load_content_product' ) );
		add_action( 'wp_ajax_nopriv_thim_load_content', array( $this, 'ajax_load_content_product' ) );
		// }
	}

	public function ajax_load_content_product() {
		ob_start();
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		$params = htmlspecialchars_decode( $_POST['params'] );
		$params = json_decode( str_replace( '\\', '', $params ), true );
		$cat_id = $_POST['category'];

		if ( ! class_exists( '\Elementor\Thim_Ekit_Products_Base' ) ) {
			include THIM_EKIT_PLUGIN_PATH . 'inc/elementor/widgets/global/product-base/product-base.php';
		}
		if ( ! class_exists( '\Elementor\Thim_Ekit_Widget_List_Product' ) ) {
			include THIM_EKIT_PLUGIN_PATH . 'inc/elementor/widgets/global/list-product/list-product.php';
		}

		$list_product = new \Elementor\Thim_Ekit_Widget_List_Product();

		$settings = $this->get_widget_settings( intval( $params['page_id'] ), sanitize_text_field( $params['widget_id'] ) );

		$list_product->render_data_content_tab( $settings, $cat_id );

		$html = ob_get_contents();

		ob_end_clean();

		wp_send_json_success( $html );

		wp_die();
	}

}
