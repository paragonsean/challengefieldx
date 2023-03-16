<?php
class LP_Addon_Frontend_Editor extends LP_Addon {

	public $version = LP_ADDON_FRONTEND_EDITOR_VER;

	public $require_version = LP_ADDON_FRONTEND_EDITOR_REQUIRE_VER;

	public $plugin_file = LP_ADDON_FRONTEND_EDITOR_FILE;

	public function __construct() {
		parent::__construct();

		add_action( 'admin_bar_menu', array( $this, 'add_admin_menu' ), 80 );
		add_filter( 'learn-press/admin/settings-tabs-array', array( $this, 'admin_settings' ) );
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_action( 'template_include', array( $this, 'template_includes' ), 1000 );
		add_filter( 'learnpress_metabox_settings_sanitize_option_learn_press_frontend_editor_page_slug', array( $this, 'sanitize_setting' ), 10, 3 );
	}

	public function _includes() {
		include_once LP_ADDON_FRONTEND_EDITOR_PATH . '/inc/functions.php';
		include_once LP_ADDON_FRONTEND_EDITOR_PATH . '/inc/class-rest-api.php';
	}

	public function enqueue_scripts() {
		$info = include LP_ADDON_FRONTEND_EDITOR_PATH . '/build/frontend-editor.asset.php';
		wp_enqueue_style( 'learnpress-frontend-editor', LP_ADDON_FRONTEND_EDITOR_URL . '/build/frontend-editor.css', array(), $info['version'] );
		wp_enqueue_script( 'learnpress-frontend-editor', LP_ADDON_FRONTEND_EDITOR_URL . '/build/frontend-editor.js', $info['dependencies'], $info['version'], true );

		wp_localize_script(
			'learnpress-frontend-editor',
			'learnpress_frontend_editor',
			apply_filters(
				'learnpress_frontend_editor_localize_script',
				array(
					'page_slug'             => learnpress_frontend_editor_get_slug(),
					'site_url'              => home_url( '/' ),
					'admin_url'             => admin_url(),
					'logout_url'            => wp_logout_url( home_url() ),
					'elementor_cpt_support' => defined( 'ELEMENTOR_VERSION' ) ? get_option( 'elementor_cpt_support', array() ) : array(),
					'course_item_types'     => learn_press_course_get_support_item_types(),
					'is_admin'              => current_user_can( 'manage_options' ),
					'is_review_course'      => LP_Settings::get_option( 'required_review', 'yes' ) === 'yes',
					'nonce'                 => wp_create_nonce( 'wp_rest' ),
					'logo_url'              => '', // Use for custom logo url.
					'logo_small_url'        => '', // Use for custom logo small url.
					'add_ons'               => array(), // If add-on support frontend use this filter show.
				)
			)
		);

		wp_set_script_translations( 'learnpress-frontend-editor', 'learnpress-frontend-editor', LP_ADDON_FRONTEND_EDITOR_PATH . '/languages' );

		learnpress_frontend_editor_tinymce_inline_scripts();

		wp_enqueue_editor(); // Support for tinymce.
		wp_enqueue_media(); // Support for tinymce media.
		wp_enqueue_script( 'media-audiovideo' );
		wp_enqueue_style( 'media-views' );
		wp_enqueue_script( 'mce-view' );

		do_action( 'learnpress/addons/frontend_editor/enqueue_scripts' );
	}

	public function add_admin_menu( $wp_admin_bar ) {
		if ( ! $this->can_view_frontend_editor() ) {
			return;
		}

		$title = esc_html__( 'Course Frontend Editor', 'Courses-frontend-editor' );
		$href  = learnpress_frontend_editor_get_url();

		if ( is_singular( LP_COURSE_CPT ) && get_the_ID() ) {
			$title = esc_html__( 'Edit with LearnPress Frontend Editor', 'learnpress-frontend-editor' );
			$href  = learnpress_frontend_editor_get_url( 'course/' . get_the_ID() . '/' );
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'class-frontend-editor',
				'title' => '
					<img style="width: 20px; height: 20px; padding: 0; line-height: 1.84615384; vertical-align: middle; margin: -6px 0 0 0;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAC6UlEQVRYw+3WTYiVVRgH8N97vRqZH1ga2dcmarKu8RbcWxQ011oV7iS1FpUNRQw1YUjlQqVF2aIpF5b0tXEhU0SRfVgR8o6LId8RfWtuM1CUiBUIFREUGjqnRUe6lOm9451xUQ+8m/c55/z/5zn/54P/uiVnCjgdHC4TSqUzAp7ls4XQRTL1BNIsvxC34FDRXT1cnrqQ5wRXRfC3inrtByhN0XsngptwN94v6rXvj/tKUxDyaUJYil5slST7m/3lSQafgfuwFOtIRovuqikhkGb5HKzBEjyKfUW9+o915UkCvwAb0I1eiT1Fd+2Ea8sdFhshXIbncA0eItlVdFfDv+0pt3Cb+bgiVs1xjBX12s8nWEcI12IzLvkT3I6iXh0/2fmtZMEcPI+P4rcmzfLpfwMv4TZsxZVYi/eKem38VIefmkBiP15AwGw8iFvTbLhZ6ffiFVyK9RJvtALecjNKs/xsvIh74p7dWI4fY6jXxsusw5aiXvu9490wzfIuvIlK1MJrOIyeeM6T2FTUa0faEW4bWZB8SdiIlzALqyLwMfRjc7vgbZXiol4NeBsfNJE/hi14pqjXfp1I6pZbf4LhEuFGXB1/HcWrsw5889TFO7b/VkywdkxrY3pZFrPh8qiBbeftzfsXDn7Sh3mVyuIvGo2RzkcgzfKzhHA/1mNBBN9+7ud7+xfkQxuwDF+hwFhHNZBm+dyo7qcjeMCH6Dt/aNdP6MJ0LMJjK1fedU7HCKRZfhE2YXUsQLATfWPzZx7Et9iIX2I2LMcdK1bcmZwWgTQblmb5IrwcJ5gZ0bUHqyW+PlKpGBjYFvAuBuKzzMQTSZJUJizCdHA4IdwQy+rNTQT34QF8VtT/aquNxsjRSmVxgdGYnkP4rtEYOdR2JYwN5XY8G9/2uI2iRwifFkuu7/jsUGpqKD2xyjWDH8AjkmT3ZIBDOSq9F49jbpPvIB7GzpMNFKdNANdhIV5v+j+Od/Bxq231f5uo/QE+vO9t7GJDTgAAAABJRU5ErkJggg==
					">
					<span class="ab-label">' . $title . '</span>',
				'href'  => $href,
			)
		);
	}

	public function template_includes( $template ) {
		global $wp_query;

		if ( learnpress_is_page_frontend_editor() ) {
			if ( $this->can_view_frontend_editor() ) {

				$this->setup_the_scripts();

				wp_head();
				?>

				<div id="learnpress-frontend-editor-root"></div>

				<?php
				wp_footer();
				return;
			} else {
				wp_redirect( home_url() );
				exit();
			}
		}

		return $template;
	}

	public function setup_the_scripts() {
		add_filter( 'show_admin_bar', '__return_false' );

		remove_all_actions( 'wp_head' );
		remove_all_actions( 'wp_print_styles' );
		remove_all_actions( 'wp_print_head_scripts' );
		remove_all_actions( 'wp_footer' );

		// Handle `wp_head`
		add_action( 'wp_head', 'wp_enqueue_scripts', 1 );
		add_action( 'wp_head', 'wp_print_styles', 8 );
		add_action( 'wp_head', 'wp_print_head_scripts', 9 );
		add_action( 'wp_head', 'wp_site_icon' );

		// Handle `wp_footer`
		add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );

		// Handle `wp_enqueue_scripts`
		remove_all_actions( 'wp_enqueue_scripts' );

		// Also remove all scripts hooked into after_wp_tiny_mce.
		remove_all_actions( 'after_wp_tiny_mce' );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999999 );

		do_action( 'learnpress/frontend-editor/init' );
	}

	public function can_view_frontend_editor() {
		return is_user_logged_in() && current_user_can( 'edit_lp_courses' );
	}

	public function add_rewrite_rules() {
		$root_slug = learnpress_frontend_editor_get_slug();

		if ( ! $root_slug ) {
			return;
		}

		add_rewrite_rule(
			'^' . $root_slug . '/?$',
			'index.php?frontend-editor=$matches[1]&post-type=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'^' . $root_slug . '/(course)/?(.*)?',
			'index.php?frontend-editor=$matches[1]&post-id=0',
			'top'
		);
		add_rewrite_rule(
			'^' . $root_slug . '/(lesson)/?(.*)?',
			'index.php?frontend-editor=$matches[1]&post-id=0',
			'top'
		);
		add_rewrite_rule(
			'^' . $root_slug . '/(quiz)/?(.*)?',
			'index.php?frontend-editor=$matches[1]&post-id=0',
			'top'
		);
		add_rewrite_rule(
			'^' . $root_slug . '/(questions)/?(.*)?',
			'index.php?frontend-editor=$matches[1]&post-id=0',
			'top'
		);
		add_rewrite_rule(
			'^' . $root_slug . '/(assignment)/?(.*)?',
			'index.php?frontend-editor=$matches[1]&post-id=0',
			'top'
		);
		add_rewrite_rule(
			'^' . $root_slug . '/(settings)/?(.*)?',
			'index.php?frontend-editor=$matches[1]&post-id=0',
			'top'
		);
		add_rewrite_tag( '%frontend-editor%', '([^&]+)' );
		add_rewrite_tag( '%post-type%', '([^&]+)' );
		add_rewrite_tag( '%post-id%', '([^&]+)' );
		add_rewrite_tag( '%item-id%', '([^&]+)' );
		add_rewrite_tag( '%sort%', '([^&]+)' );
		add_rewrite_tag( '%sortby%', '([^&]+)' );

		flush_rewrite_rules();
	}

	public function admin_settings( $tabs ) {
		$tabs['frontend_editor'] = include_once LP_ADDON_FRONTEND_EDITOR_PATH . '/inc/class-settings.php';

		return $tabs;
	}

	public function sanitize_setting( $value, $option, $raw_value ) {
		$value = sanitize_title( $value );

		return $value;
	}
}
