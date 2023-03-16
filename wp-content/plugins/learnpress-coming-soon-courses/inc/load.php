<?php
/**
 * Plugin load class.
 *
 * @author   ThimPress
 * @package  LearnPress/Coming-Soon-Courses/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Addon_Coming_Soon_Courses' ) ) {
	/**
	 * Class LP_Addon_Coming_Soon_Courses.
	 */
	class LP_Addon_Coming_Soon_Courses extends LP_Addon {

		/**
		 * @var RW_Meta_Box
		 */
		public $metabox = null;

		/**
		 * Hold the course ids is coming soon
		 *
		 * @var array
		 */
		protected $_coming_soon_courses = array();

		/**
		 * @var null
		 */
		protected $_course_coming_soon = null;

		/**
		 * @var string
		 */
		public $version = LP_ADDON_COMING_SOON_COURSES_VER;

		/**
		 * @var string
		 */
		public $require_version = LP_ADDON_COMING_SOON_COURSES_REQUIRE_VER;

		/**
		 * Path file addon .
		 *
		 * @var string
		 */
		public $plugin_file = LP_ADDON_COMING_SOON_COURSES_FILE;


		/**
		 * LP_Addon_Coming_Soon_Courses constructor.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Define constants.
		 */
		protected function _define_constants() {
			define( 'LP_ADDON_COMING_SOON_COURSES_PATH', dirname( LP_ADDON_COMING_SOON_COURSES_FILE ) );
			define( 'LP_ADDON_COMING_SOON_COURSES_TEMP', LP_ADDON_COMING_SOON_COURSES_PATH . '/templates/' );
		}

		/**
		 * Includes files.
		 */
		protected function _includes() {
			require_once LP_ADDON_COMING_SOON_COURSES_PATH . '/inc/functions.php';
		}

		/**
		 * Init hooks.
		 */
		protected function _init_hooks() {
			add_action( 'lp_course_data_setting_tab_content', array( $this, 'course_coming_soon_meta_box_v4' ) );

			// Metabox course tab.
			add_filter(
				'lp_course_data_settings_tabs',
				function ( $data ) {
					$data['course_comingsoon'] = array(
						'label'    => esc_html__( 'Coming soon', 'learnpress-collections' ),
						'icon'     => 'dashicons-clock',
						'target'   => 'lp_comingsoon_course_data',
						'priority' => 60,
					);

					return $data;
				}
			);
			add_action(
				'learnpress_save_lp_course_metabox',
				function ( $post_id = 0 ) {

					$opt_csc_enable    = ! empty( $_POST['_lp_coming_soon'] ) ? 'yes' : 'no';
					$opt_csc_msg       = ! empty( $_POST['_lp_coming_soon_msg'] ) ? $_POST['_lp_coming_soon_msg'] : '';
					$opt_csc_endtime   = ! empty( $_POST['_lp_coming_soon_end_time'] ) ? $_POST['_lp_coming_soon_end_time'] : '';
					$opt_csc_countdown = ! empty( $_POST['_lp_coming_soon_countdown'] ) ? 'yes' : 'no';
					$opt_csc_showtext  = ! empty( $_POST['_lp_coming_soon_showtext'] ) ? 'yes' : 'no';
					$opt_csc_metadata  = ! empty( $_POST['_lp_coming_soon_metadata'] ) ? 'yes' : 'no';
					$opt_csc_details   = ! empty( $_POST['_lp_coming_soon_details'] ) ? 'yes' : 'no';

					// Update post meta

					update_post_meta( $post_id, '_lp_coming_soon', $opt_csc_enable );
					update_post_meta( $post_id, '_lp_coming_soon_msg', $opt_csc_msg );
					update_post_meta( $post_id, '_lp_coming_soon_end_time', $opt_csc_endtime );
					update_post_meta( $post_id, '_lp_coming_soon_countdown', $opt_csc_countdown );
					update_post_meta( $post_id, '_lp_coming_soon_showtext', $opt_csc_showtext );
					update_post_meta( $post_id, '_lp_coming_soon_metadata', $opt_csc_metadata );
					update_post_meta( $post_id, '_lp_coming_soon_details', $opt_csc_details );

				}
			);

			add_action( 'learn-press/course-content-summary', array( $this, 'coming_soon_message' ), 10 );
			add_action( 'learn-press/course-content-summary', array( $this, 'coming_soon_countdown' ), 10 );
			add_action( 'template_redirect', array( $this, 'coming_soon_option_v4' ), 10 );
			remove_action(
				'learn-press/course-meta-secondary-left',
				LP()->template( 'course' )->callback( 'single-course/meta/duration' ),
				10
			);

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			// Check can purchase course via product of Woo (Assign courses to product feature)
			add_filter( 'learnpress/wc-order/can-purchase-product', array( $this, 'can_purchase_product_has_courses' ), 10, 2 );
			// Check can purchase course
			add_filter( 'learn-press/user/can-purchase-course', array( $this, 'can_purchase_course' ), 20, 3 );
		}

		/**
		 * Check can purchase product has courses - LP Woo
		 *
		 * @param bool $can_purchase
		 * @param int  $course_id
		 * @return bool
		 * @since 4.0.3
		 * @editor minhpd
		 */
		public function can_purchase_product_has_courses( $can_purchase, $course_id ) {
			if ( $this->is_coming_soon( $course_id ) ) {
				$can_purchase = false;
			}

			return $can_purchase;
		}

		/**
		 * Filer user can purchase course when show button by course is comingsoon
		 *
		 * @param bool $can_purchase
		 * @param int  $user_id
		 * @param int  $course_id
		 *
		 * @return bool
		 * @since 4.0.0
		 * @editor minhpd
		 */
		public function can_purchase_course( $can_purchase, $user_id, $course_id ) {
			if ( $this->is_coming_soon( $course_id ) ) {
				$can_purchase = false;
			}

			return $can_purchase;
		}

		/**
		 * @return string
		 */
		public function get_plugin_file() {
			return $this->plugin_file;
		}

		/**
		 * @param string $plugin_file
		 */
		public function coming_soon_option_v4() {

			$course = learn_press_get_course();

			if ( ! $course ) {
				return;
			}

			$course_id = $course->get_id();

			$check_coming_soon          = get_post_meta( $course_id, '_lp_coming_soon', true );
			$check_coming_soon_metadata = get_post_meta( $course_id, '_lp_coming_soon_metadata', true );
			$check_coming_soon_details  = get_post_meta( $course_id, '_lp_coming_soon_details', true );
			if ( empty( $check_coming_soon ) || $check_coming_soon == 'no' ) {
				return;
			}
			if ( $this->is_coming_soon( $course_id ) ) {
				// delete does not need checking
				// remove_action(
				// 'learn-press/course-summary-sidebar',
				// LP()->template( 'course' )->func( 'course_featured_review' ),
				// 20
				// );
				// remove_action(
				// 'learn-press/course-buttons',
				// LP()->template( 'course' )->func( 'course_enroll_button' ),
				// 5
				// );
				// remove_action(
				// 'learn-press/course-buttons',
				// LP()->template( 'course' )->func( 'course_purchase_button' ),
				// 10
				// );
				// remove_action(
				// 'learn-press/course-buttons',
				// LP()->template( 'course' )->func( 'course_external_button' ),
				// 15
				// );
				// remove_action( 'learn-press/course-buttons', LP()->template( 'course' )->func( 'button_retry' ), 20 );
				// remove_action(
				// 'learn-press/course-buttons',
				// LP()->template( 'course' )->func( 'course_continue_button' ),
				// 25
				// );
				// remove_action(
				// 'learn-press/course-buttons',
				// LP()->template( 'course' )->func( 'course_finish_button' ),
				// 30
				// );
				remove_all_actions( 'learn-press/course-buttons' );
				if ( $check_coming_soon_metadata == 'no' ) {
					// Check conditions before execution
					remove_action(
						'learn-press/course-meta-secondary-left',
						LP()->template( 'course' )->func( 'count_object' ),
						20
					);
					remove_action(
						'learn-press/course-content-summary',
						LP()->template( 'course' )->func( 'course_extra_boxes' ),
						40
					);
					add_action( 'learn_press_course_price_html', array( $this, 'set_course_price_html_empty' ) );
					LP()->template( 'course' )->remove_callback(
						'learn-press/course-meta-secondary-left',
						'single-course/meta/level',
						20
					);
					LP()->template( 'course' )->remove_callback(
						'learn-press/course-meta-secondary-left',
						'single-course/meta/category',
						20
					);
					LP()->template( 'course' )->remove_callback(
						'learn-press/course-meta-secondary-left',
						'single-course/meta/instructor',
						20
					);
					LP()->template( 'course' )->remove_callback(
						'learn-press/course-meta-secondary-left',
						'single-course/meta/duration',
						10
					);
					LP()->template( 'course' )->remove_callback(
						'learn-press/course-meta-primary-left',
						'single-course/meta/category',
						20
					);

					// Remove add_filter group
					add_filter(
						'learn-press/course-tabs',
						function ( $defaults ) {
							unset( $defaults['instructor'] );

							return $defaults;
						},
						10,
						1
					);

					add_filter(
						'learn-press/course-tabs',
						function ( $defaults ) {
							unset( $defaults['faqs'] );

							return $defaults;
						},
						10,
						1
					);
				}

				if ( $check_coming_soon_details == 'no' ) {
					// Remove add_filter group
					add_filter(
						'learn-press/course-tabs',
						function ( $defaults ) {
							unset( $defaults['overview'] );
							unset( $defaults['curriculum'] );

							return $defaults;
						},
						10,
						1
					);
				}
			}
		}

		/**
		 * Assets.
		 */
		public function enqueue_scripts() {
			wp_register_style( 'lp-coming-soon-course', $this->get_plugin_url( 'assets/css/coming-soon-course.css' ) );
			wp_register_script( 'lp-jquery-mb-coming-soon', $this->get_plugin_url( 'assets/js/jquery.mb-coming-soon.min.js' ), array( 'jquery' ) );
			wp_register_script( 'lp-coming-soon-course', $this->get_plugin_url( 'assets/js/coming-soon-course.js' ) );

			$translation_array = array(
				'days'    => __( 'days', 'learnpress-coming-soon-courses' ),
				'hours'   => __( 'hours', 'learnpress-coming-soon-courses' ),
				'minutes' => __( 'minutes', 'learnpress-coming-soon-courses' ),
				'seconds' => __( 'seconds', 'learnpress-coming-soon-courses' ),
			);

			if ( LP_PAGE_SINGLE_COURSE === LP_Page_Controller::page_current() ) {
				$course = learn_press_get_course();

				if ( ! $course ) {
					return;
				}

				if ( ! $this->is_coming_soon( $course->get_id() ) ) {
					return;
				}

				wp_enqueue_style( 'lp-coming-soon-course' );
				wp_enqueue_script( 'lp-jquery-mb-coming-soon' );
				wp_enqueue_script( 'lp-coming-soon-course' );

				wp_localize_script( 'lp-coming-soon-course', 'lp_coming_soon_translation', $translation_array );

			} elseif ( LP_PAGE_COURSES === LP_Page_Controller::page_current() ) {
				wp_enqueue_style( 'lp-coming-soon-course' );
				wp_enqueue_script( 'lp-jquery-mb-coming-soon' );
				wp_enqueue_script( 'lp-coming-soon-course' );

				$all_courses = learn_press_get_all_courses();
				if ( count( $all_courses ) ) {
					foreach ( $all_courses as $course_item ) {
						if ( learn_press_is_coming_soon( $course_item ) ) {
							wp_localize_script(
								'lp-coming-soon-course',
								'lp_coming_soon_translation',
								$translation_array
							);
							break;
						}
					}
				}
			}
		}

		public function admin_enqueue_scripts() {
			$handle = 'jquery.datetimepicker.full.min.js';
			$list   = 'enqueued';
			if ( wp_script_is( $handle, $list ) ) {

				return;

			} else {

				wp_enqueue_style(
					'lp-coming-soon-date-style',
					$this->get_plugin_url( 'assets/css/jquery.datetimepicker.min.css' )
				);
				wp_enqueue_script(
					'lp-coming-soon-date-script',
					$this->get_plugin_url( 'assets/js/jquery.datetimepicker.full.min.js' )
				);
				wp_enqueue_script( 'lp-coming-soon-admin-script', $this->get_plugin_url( 'assets/js/admin.js' ) );

			}
			wp_enqueue_style( 'lp-coming-soon-admin-style', $this->get_plugin_url( 'assets/css/admin.css' ) );
		}

		/**
		 * Add Coming soon tab in admin course.
		 *
		 * @param $tabs
		 *
		 * @return array
		 */
		public function add_course_tab( $tabs ) {
			$forum = array( 'course_coming_soon' => new RW_Meta_Box( self::course_coming_soon_meta_box_v3() ) );

			return array_merge( $tabs, $forum );
		}

		/**
		 * Coming soon course meta box LP3.
		 *
		 * @return mixed
		 */
		public function course_coming_soon_meta_box_v3() {

			// for dependent options
			$visibility   = array(
				'state'       => 'show',
				'conditional' => array(
					array(
						'field'   => '_lp_coming_soon',
						'compare' => '==',
						'value'   => 'yes',
					),
				),
			);
			$text_options = array(
				'closeText'       => __( 'DONE', 'learnpress-coming-soon-courses' ),
				'prevText'        => __( 'Previous', 'learnpress-coming-soon-courses' ),
				'nextText'        => __( 'Next', 'learnpress-coming-soon-courses' ),
				'currentText'     => __( 'NOW', 'learnpress-coming-soon-courses' ),
				'monthNames'      => array(
					__( 'January', 'learnpress-coming-soon-courses' ),
					__( 'February', 'learnpress-coming-soon-courses' ),
					__( 'March', 'learnpress-coming-soon-courses' ),
					__( 'April', 'learnpress-coming-soon-courses' ),
					__( 'May', 'learnpress-coming-soon-courses' ),
					__( 'June', 'learnpress-coming-soon-courses' ),
					__( 'July', 'learnpress-coming-soon-courses' ),
					__( 'August', 'learnpress-coming-soon-courses' ),
					__( 'September', 'learnpress-coming-soon-courses' ),
					__( 'October', 'learnpress-coming-soon-courses' ),
					__( 'November', 'learnpress-coming-soon-courses' ),
					__( 'December', 'learnpress-coming-soon-courses' ),
				),
				'monthNamesShort' => array(
					__( 'Jan', 'learnpress-coming-soon-courses' ),
					__( 'Feb', 'learnpress-coming-soon-courses' ),
					__( 'Mar', 'learnpress-coming-soon-courses' ),
					__( 'Apr', 'learnpress-coming-soon-courses' ),
					__( 'May', 'learnpress-coming-soon-courses' ),
					__( 'Jun', 'learnpress-coming-soon-courses' ),
					__( 'Jul', 'learnpress-coming-soon-courses' ),
					__( 'Aug', 'learnpress-coming-soon-courses' ),
					__( 'Sep', 'learnpress-coming-soon-courses' ),
					__( 'Oct', 'learnpress-coming-soon-courses' ),
					__( 'Nov', 'learnpress-coming-soon-courses' ),
					__( 'Dec', 'learnpress-coming-soon-courses' ),
				),
				'dayNames'        => array(
					__( 'Monday', 'learnpress-coming-soon-courses' ),
					__( 'Tuesday', 'learnpress-coming-soon-courses' ),
					__( 'Wednesday', 'learnpress-coming-soon-courses' ),
					__( 'Thursday', 'learnpress-coming-soon-courses' ),
					__( 'Friday', 'learnpress-coming-soon-courses' ),
					__( 'Saturday', 'learnpress-coming-soon-courses' ),
					__( 'Sunday', 'learnpress-coming-soon-courses' ),
				),
				'dayNamesShort'   => array(
					__( 'Mon', 'learnpress-coming-soon-courses' ),
					__( 'Tue', 'learnpress-coming-soon-courses' ),
					__( 'Wed', 'learnpress-coming-soon-courses' ),
					__( 'Thu', 'learnpress-coming-soon-courses' ),
					__( 'Fri', 'learnpress-coming-soon-courses' ),
					__( 'Sat', 'learnpress-coming-soon-courses' ),
					__( 'Sun', 'learnpress-coming-soon-courses' ),
				),
				'timeText'        => __( 'Time', 'learnpress-coming-soon-courses' ),
				'hourText'        => __( 'Hour', 'learnpress-coming-soon-courses' ),
				'minuteText'      => __( 'Minute', 'learnpress-coming-soon-courses' ),
			);

			$meta_box = array(
				'id'       => 'course_coming_soon',
				'title'    => __( 'Coming soon', 'learnpress-coming-soon-courses' ),
				'icon'     => 'dashicons-clock',
				'priority' => 'high',
				'pages'    => array( LP_COURSE_CPT ),
				'fields'   => array(
					array(
						'name' => __( 'Enable', 'learnpress-coming-soon-courses' ),
						'id'   => '_lp_coming_soon',
						'type' => 'yes-no',
						'desc' => __( 'Enable coming soon mode.', 'learnpress-coming-soon-courses' ),
						'std'  => 'no',
					),
					array(
						'name'       => __( 'Message', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_msg',
						'type'       => 'wysiwyg',
						'editor'     => true,
						'desc'       => __(
							'The coming soon message will show in single course page.',
							'learnpress-coming-soon-courses'
						),
						'std'        => __( 'This course will coming soon', 'learnpress-coming-soon-courses' ),
						'visibility' => $visibility,
					),
					array(
						'name'       => __( 'Coming soon end time', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_end_time',
						'type'       => 'datetime',
						'js_options' => $text_options,
						'desc'       => __( 'Set end time coming soon.', 'learnpress-coming-soon-courses' ),
						'visibility' => $visibility,
					),
					array(
						'name'       => __( 'Show Countdown', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_countdown',
						'type'       => 'yes-no',
						'desc'       => __( 'Show countdown counter.', 'learnpress-coming-soon-courses' ),
						'std'        => 'no',
						'visibility' => $visibility,
					),
					array(
						'name'       => __( 'Show DateTime Text', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_showtext',
						'type'       => 'yes-no',
						'desc'       => __(
							'Show date and time text (days, hours, minutes, seconds) on single course page.',
							'learnpress-coming-soon-courses'
						),
						'std'        => 'no',
						'visibility' => $visibility,
					),
					array(
						'name'       => __( 'Show Meta', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_metadata',
						'type'       => 'yes-no',
						'desc'       => __(
							'Show meta data (such as info about Instructor, price, FAQs, so on) of the course.',
							'learnpress-coming-soon-courses'
						),
						'std'        => 'no',
						'visibility' => $visibility,
					),
					array(
						'name'       => __( 'Show Details', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_details',
						'type'       => 'yes-no',
						'desc'       => __( 'Show details content of the course.', 'learnpress-coming-soon-courses' ),
						'std'        => 'no',
						'visibility' => $visibility,
					),
				),
			);

			return apply_filters( 'learn-press/course-coming-soon/settings-meta-box-args', $meta_box );
		}

		/**
		 * Coming soon course meta box LP4.
		 *
		 * @return mixed
		 */
		public function course_coming_soon_meta_box_v4() {

			// Start option for Lp4
			echo '<div id="lp_comingsoon_course_data" class="lp-meta-box-course-panels">';

			lp_meta_box_checkbox_field(
				array(
					'id'          => '_lp_coming_soon',
					'label'       => esc_html__( 'Enable', 'learnpress-coming-soon-courses' ),
					'description' => esc_html__(
						'Enable coming soon mode.',
						'learnpress-coming-soon-courses'
					),
					'default'     => 'no',
				)
			);
			global $post;
			$lpcs_class   = '';
			$lpcs_enabled = get_post_meta( $post->ID, '_lp_coming_soon', true );
			if ( $lpcs_enabled == 'no' || $lpcs_enabled == '' ) {
				$lpcs_class = 'locked';
			}
			echo '<div class="lpcs_enable_area ' . $lpcs_class . '">';

			lp_meta_box_textarea_field(
				array(
					'id'          => '_lp_coming_soon_msg',
					'label'       => __( 'Message', 'learnpress-coming-soon-courses' ),
					'description' => __(
						'The coming soon message will show in single course page.',
						'learnpress-coming-soon-courses'
					),
					'default'     => __( 'This course will coming soon', 'learnpress-coming-soon-courses' ),
				)
			);

			lp_meta_box_text_input_field(
				array(
					'id'          => '_lp_coming_soon_end_time',
					'label'       => __( 'Coming soon end time', 'learnpress-coming-soon-courses' ),
					'description' => __( 'Set end time coming soon.', 'learnpress-coming-soon-courses' ),
					'default'     => '',
				)
			);

			lp_meta_box_checkbox_field(
				array(
					'id'          => '_lp_coming_soon_countdown',
					'label'       => __( 'Show Countdown', 'learnpress-coming-soon-courses' ),
					'description' => __( 'Show countdown counter.', 'learnpress-coming-soon-courses' ),
					'default'     => 'no',
				)
			);

			lp_meta_box_checkbox_field(
				array(
					'id'          => '_lp_coming_soon_showtext',
					'label'       => __( 'Show DateTime Text', 'learnpress-coming-soon-courses' ),
					'description' => __(
						'Show date and time text (days, hours, minutes, seconds) on single course page.',
						'learnpress-coming-soon-courses'
					),
					'default'     => 'no',
				)
			);

			lp_meta_box_checkbox_field(
				array(
					'id'          => '_lp_coming_soon_metadata',
					'label'       => __( 'Show Meta', 'learnpress-coming-soon-courses' ),
					'description' => __(
						'Show meta data (such as info about Instructor, price, so on) of the course.',
						'learnpress-coming-soon-courses'
					),
					'default'     => 'no',
				)
			);

			lp_meta_box_checkbox_field(
				array(
					'id'          => '_lp_coming_soon_details',
					'label'       => __( 'Show Details', 'learnpress-coming-soon-courses' ),
					'description' => __( 'Show details content of the course.', 'learnpress-coming-soon-courses' ),
					'default'     => 'no',
				)
			);

			echo '</div>';

			echo '</div>';
			// End option Lp4
		}

		/**
		 * @param $located
		 * @param $template_name
		 * @param $args
		 * @param $template_path
		 * @param $default_path
		 *
		 * @return string
		 */
		public function change_default_template( $located, $template_name, $args, $template_path, $default_path ) {
			remove_filter( 'learn_press_get_template', array( $this, 'change_default_template' ), 100, 5 );
			if ( $template_name == 'content-single-course.php' ) {
				$course = learn_press_get_course();
				if ( $course ) {
					$course_id = $course->get_id();
					if ( $this->is_coming_soon( $course_id ) ) {
						$located = learn_press_coming_soon_course_locate_template( $template_name );
					}
				}
			}
			add_filter( 'learn_press_get_template', array( $this, 'change_default_template' ), 100, 5 );

			return $located;
		}

		/**
		 * @param $template
		 * @param $slug
		 * @param $name
		 *
		 * @return string
		 */
		public function change_content_course_template( $template, $slug, $name ) {
			if ( $slug == 'content' && $name == 'course' ) {
				$course    = learn_press_get_course();
				$course_id = $course->get_id();
				if ( $this->is_coming_soon( $course_id ) ) {
					remove_filter(
						'learn_press_get_template_part',
						array(
							$this,
							'change_content_course_template',
						),
						100,
						3
					);
					$template = learn_press_coming_soon_course_locate_template( 'content-course.php' );
					add_filter(
						'learn_press_get_template_part',
						array(
							$this,
							'change_content_course_template',
						),
						100,
						3
					);
				}
			}

			return $template;
		}


		/**
		 * Display coming soon message
		 */
		public function coming_soon_message() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			$message   = get_post_meta(
				$course_id,
				'_lp_coming_soon_msg',
				true
			);
			if ( $this->is_coming_soon( $course_id ) && '' !== $message ) {
				// enable shortcode in coming message
				$message = do_shortcode( $message );
				learn_press_coming_soon_course_template( 'single-course/message.php', array( 'message' => $message ) );
			}
		}

		/**
		 * Display meta data of the course
		 */
		public function coming_soon_meta_details() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			$details   = get_post_meta(
				$course_id,
				'_lp_coming_soon_metadata',
				true
			);
			if ( $this->is_coming_soon( $course_id ) && 'no' !== $details ) {
				learn_press_course_meta_start_wrapper();
				learn_press_course_price();
				learn_press_course_instructor();
				learn_press_course_students();
				learn_press_course_meta_end_wrapper();
			}
		}

		/**
		 * Display content tabs of the course
		 */
		public function coming_soon_content_tabs() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			$details   = get_post_meta(
				$course_id,
				'_lp_coming_soon_details',
				true
			);
			if ( $this->is_coming_soon( $course_id ) && 'no' !== $details ) {
				learn_press_coming_soon_course_template( 'single-course/content-tabs.php', array() );
			}
		}

		/**
		 * Display Enroll button of the course. This need to be checked more!
		 */
		public function coming_soon_enroll_button() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			$details   = get_post_meta(
				$course_id,
				'_lp_coming_soon_enroll_button',
				true
			);
			if ( $this->is_coming_soon( $course_id ) && 'no' !== $details ) {
				learn_press_course_buttons();
			}
		}

		/**
		 * Display coming soon countdown
		 */
		public function coming_soon_countdown() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			if ( get_post_meta( $course_id, '_lp_coming_soon', true ) != 'yes' ) {
				return;
			}
			$end_time = $this->get_coming_soon_end_time( $course_id, 'Y-m-d H:i:s' );
			$datetime = new DateTime( $end_time );
			$timezone = get_option( 'gmt_offset' );
			$showtext = get_post_meta( $course_id, '_lp_coming_soon_showtext', true );
			learn_press_coming_soon_course_template(
				'single-course/countdown.php',
				array(
					'datetime' => $datetime,
					'timezone' => $timezone,
					'showtext' => $showtext,
				)
			);
		}

		/**
		 * /**
		 * Check all options and return TRUE if a course has 'Coming Soon'
		 *
		 * @param int $course_id
		 *
		 * @return mixed
		 */
		public function is_coming_soon( $course_id = 0 ) {
			if ( ! $course_id && LP_COURSE_CPT == get_post_type() ) {
				$course_id = get_the_ID();
			}
			if ( empty( $this->_coming_soon_courses[ $course_id ] ) ) {
				$this->_coming_soon_courses[ $course_id ] = false;
				if ( $this->is_enable_coming_soon( $course_id ) ) {
					$end_time     = $this->get_coming_soon_end_time( $course_id );
					$current_time = current_time( 'timestamp' );

					if ( $end_time == 0 || $end_time > $current_time ) {
						$this->_coming_soon_courses[ $course_id ] = true;
					}
				}
			}

			return $this->_coming_soon_courses[ $course_id ];
		}

		/**
		 * Return TRUE if 'Coming Soon' is enabled
		 *
		 * @param int $course_id
		 *
		 * @return bool
		 */
		public function is_enable_coming_soon( $course_id = 0 ) {
			if ( ! $course_id && LP_COURSE_CPT == get_post_type() ) {
				$course_id = get_the_ID();
			}

			return 'yes' == get_post_meta( $course_id, '_lp_coming_soon', true );
		}

		/**
		 * Return expiration time of 'Coming Soon'
		 *
		 * @param int    $course_id
		 * @param string
		 *
		 * @return int
		 */
		public function get_coming_soon_end_time( $course_id = 0, $format = 'timestamp' ) {
			if ( ! $course_id && LP_COURSE_CPT == get_post_type() ) {
				$course_id = get_the_ID();
			}
			$end_time = 0;
			if ( $this->is_enable_coming_soon( $course_id ) ) {
				$end_time           = get_post_meta( $course_id, '_lp_coming_soon_end_time', true );
				$current_time       = time();
				$end_time_timestamp = strtotime( $end_time, $current_time );
				if ( $format == 'timestamp' ) {
					$end_time = $end_time_timestamp;
				} elseif ( $format ) {
					$end_time = gmdate( $format, $end_time_timestamp );
				}
			}

			return $end_time;
		}

		/**
		 * Return TRUE if a course is enabled countdown
		 *
		 * @param int $course_id
		 *
		 * @return bool
		 */
		public function is_show_coming_soon_countdown( $course_id = 0 ) {
			if ( ! $course_id && LP_COURSE_CPT == get_post_type() ) {
				$course_id = get_the_ID();
			}

			return 'yes' == get_post_meta( $course_id, '_lp_coming_soon_countdown', true );
		}

		/**
		 * Set course price html to empty if not enable "Show Meta"
		 *
		 * @return string
		 */
		public function set_course_price_html_empty( $price ): string {
			return '';
		}
	}
}

add_action( 'learn-press/ready', array( 'LP_Addon_Coming_Soon_Courses', 'instance' ) );
