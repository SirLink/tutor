<?php

/**
 * Options for TutorLMS
 *
 * @since v.2.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Certificate
 * @version 2.0
 */

namespace Tutor;

use TUTOR\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options_V2 {


	private $options;
	private $setting_fields;

	public function __construct() {
		// Saving option.
		add_action( 'wp_ajax_tutor_option_save', array( $this, 'tutor_option_save' ) );
		add_action( 'wp_ajax_tutor_option_default_save', array( $this, 'tutor_option_default_save' ) );
		add_action( 'wp_ajax_tutor_option_search', array( $this, 'tutor_option_search' ) );
		add_action( 'wp_ajax_tutor_export_settings', array( $this, 'tutor_export_settings' ) );
		add_action( 'wp_ajax_tutor_export_single_settings', array( $this, 'tutor_export_single_settings' ) );
		add_action( 'wp_ajax_tutor_delete_single_settings', array( $this, 'tutor_delete_single_settings' ) );
		add_action( 'wp_ajax_tutor_import_settings', array( $this, 'tutor_import_settings' ) );
		add_action( 'wp_ajax_tutor_apply_settings', array( $this, 'tutor_apply_settings' ) );
		add_action( 'wp_ajax_load_saved_data', array( $this, 'load_saved_data' ) );
		add_action( 'wp_ajax_reset_settings_data', array( $this, 'reset_settings_data' ) );
	}

	private function get( $key = null, $default = false ) {

		if ( ! $this->options ) {
			// Get if already not prepared
			$this->options = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		}

		$option = $this->options;

		if ( empty( $option ) || ! is_array( $option ) ) {
			return $default;
		}

		if ( ! $key ) {
			return $option;
		}

		if ( array_key_exists( $key, $option ) ) {
			return apply_filters( $key, $option[ $key ] );
		}

		// Access array value via dot notation, such as option->get('value.subvalue').
		if ( strpos( $key, '.' ) ) {
			$option_key_array = explode( '.', $key );
			$new_option       = $option;
			foreach ( $option_key_array as $dotKey ) {
				if ( isset( $new_option[ $dotKey ] ) ) {
					$new_option = $new_option[ $dotKey ];
				} else {
					return $default;
				}
			}

			return apply_filters( $key, $new_option );
		}

		return $default;
	}

	/**
	 * Function to get all fields for search tutor_option_search
	 *
	 * @return array
	 */
	public function tutor_option_search() {
		tutor_utils()->checking_nonce();

		$data_array = array();
		foreach ( $this->get_setting_fields() as $sections ) {
			if ( is_array( $sections ) && ! empty( $sections ) ) {
				foreach ( tutils()->sanitize_recursively( $sections ) as $section ) {
					foreach ( $section['blocks'] as $blocks ) {
						if ( isset( $blocks['fields'] ) && ! empty( $blocks['fields'] ) ) {
							foreach ( $blocks['fields'] as $fields ) {
								$fields['section_label'] = isset( $section['label'] ) ? $section['label'] : '';
								$fields['section_slug']  = isset( $section['slug'] ) ? $section['slug'] : '';
								$fields['block_label']   = isset( $blocks['label'] ) ? $blocks['label'] : '';
								$data_array['fields'][]  = $fields;
							}
						}
					}
				}
			}
		}

		wp_send_json_success( $data_array );
	}

	/**
	 * Export settings
	 */
	public function tutor_export_settings() {
		wp_send_json_success( (array) maybe_unserialize( get_option( 'tutor_option' ) ) );
	}

	/**
	 * Export single settings
	 */
	public function tutor_export_single_settings() {
		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$export_id          = $this->get_request_data( 'export_id' );
		wp_send_json_success( $tutor_settings_log[ $export_id ] );
	}

	/**
	 * Apply settings
	 */
	public function tutor_apply_settings() {
		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$apply_id           = $this->get_request_data( 'apply_id' );

		update_option( 'tutor_option', $tutor_settings_log[ $apply_id ]['dataset'] );

		wp_send_json_success( $tutor_settings_log[ $apply_id ] );
	}

	/**
	 * Delete single setting
	 */
	public function tutor_delete_single_settings() {
		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$delete_id          = $this->get_request_data( 'delete_id' );
		unset( $tutor_settings_log[ $delete_id ] );

		update_option( 'tutor_settings_log', $tutor_settings_log );

		wp_send_json_success( $tutor_settings_log );
	}

	/**
	 * Get request data
	 *
	 * @return mixed
	 */
	public function get_request_data( $var ) {
		return isset( $_REQUEST[ $var ] ) ? $_REQUEST[ $var ] : null;
	}

	/**
	 * tutor_default_settings
	 *
	 * @return JSON
	 */
	public function tutor_default_settings() {
		$attr = $this->get_setting_fields();
		foreach ( $attr as $sections ) {
			foreach ( $sections['sections'] as $section ) {
				foreach ( $section['blocks'] as $blocks ) {
					foreach ( $blocks['fields'] as $field ) {
						if ( isset( $field['default'] ) ) {
							$attr_default[ $field['key'] ] = $field['default'];
						}
					}
				}
			}
		}

		update_option( 'tutor_option', $attr_default );

		wp_send_json_success( $attr_default );
	}

	public function load_saved_data() {
		tutor_utils()->checking_nonce();
		wp_send_json_success( get_option( 'tutor_settings_log' ) );
	}

	public function reset_settings_data() {
		tutor_utils()->checking_nonce();
		$reset_fields = $return_fields = $return_fields_group = array();
		$reset_page   = isset( $_POST['reset_page'] ) ? sanitize_key( $_POST['reset_page'] ) : null;
		$setting_data = $this->get_setting_fields()['option_fields'][ $reset_page ]['blocks'];

		foreach ( $setting_data as $blocks ) {

			$block_fields = isset( $blocks['fields'] ) ? $blocks['fields'] : array();
			foreach ( $block_fields as $fields ) {
				$return_fields[] = $fields;
			}

			$block_fields_group = isset( $blocks['fields_group'] ) ? $blocks['fields_group'] : array();
			foreach ( $block_fields_group as $fields ) {
				$return_fields_group[] = $fields;
			}
		}

		$reset_fields = array_merge( $return_fields, $return_fields_group );

		wp_send_json_success( $reset_fields );
	}

	public function tutor_import_settings() {
		tutor_utils()->checking_nonce();
		$request = $this->get_request_data( 'tutor_options' );

		$time    = $this->get_request_data( 'time' );
		$request = json_decode( str_replace( '\"', '"', $request ), true );

		$save_import_data['datetime']             = $time;
		$save_import_data['history_date']         = date( 'j M, Y, g:i a', $time );
		$save_import_data['datatype']             = 'imported';
		$save_import_data['dataset']              = $request['data'];
		$import_data[ 'tutor-imported-' . $time ] = $save_import_data;

		// update_option( 'tutor_settings_log', array() );
		$get_option_data = get_option( 'tutor_settings_log' );
		if ( empty( $get_option_data ) ) {
			$get_option_data = array();
		}
		if ( ! empty( $get_option_data ) && null !== $save_import_data['dataset'] ) {

			$update_option = array_merge( $get_option_data, $import_data );

			$update_option = tutor_utils()->sanitize_recursively( $update_option );

			if ( ! empty( $update_option ) ) {
				update_option( 'tutor_settings_log', $update_option );
			}

			if ( ! empty( $save_import_data ) ) {
				update_option( 'tutor_option', $save_import_data['dataset'] );
			}

			$get_final_data = get_option( 'tutor_settings_log' );

		} else {
			if ( ! empty( $import_data ) ) {
				update_option( 'tutor_settings_log', $import_data );
			}

			if ( ! empty( $save_import_data ) ) {
				update_option( 'tutor_option', $save_import_data['dataset'] );
			}
			$get_final_data = get_option( 'tutor_settings_log' );
		}

		wp_send_json_success( $get_final_data );
	}


	/**
	 * Function tutor_option_save
	 *
	 * @return JSON
	 */
	public function tutor_option_save() {
		tutor_utils()->checking_nonce();

		! current_user_can( 'manage_options' ) ? wp_send_json_error() : 0;

		do_action( 'tutor_option_save_before' );

		$option = (array) tutor_utils()->array_get( 'tutor_option', $_POST, array() );

		$option = tutor_utils()->sanitize_recursively( $option );

		$option = apply_filters( 'tutor_option_input', $option );

		// $request = $this->get_request_data( 'tutor_options' );
		$time                                     = strtotime( 'now' ) + ( 6 * 60 * 60 );
		$save_import_data['datetime']             = $time;
		$save_import_data['history_date']         = date( 'j M, Y, g:i a', $time );
		$save_import_data['datatype']             = 'saved';
		$save_import_data['dataset']              = $option;
		$import_data[ 'tutor-imported-' . $time ] = $save_import_data;
		$update_option                            = array();
		$get_option_data                          = get_option( 'tutor_settings_log', array() );

		if ( ! empty( $get_option_data ) ) {
			$update_option = array_merge( $import_data, $get_option_data );
		} else {
			$update_option = array_merge( $import_data );
		}

		update_option( 'tutor_settings_log', $update_option );

		update_option( 'tutor_option', $option );

		do_action( 'tutor_option_save_after' );

		// wp_send_json_success(array('msg' => __('Option Updated', 'tutor'), 'return' => $option));
		wp_send_json_success( $_POST );
	}

	/**
	 * Function tutor_option_save
	 *
	 * @return JSON
	 */
	public function tutor_option_default_save() {
		tutor_utils()->checking_nonce();

		! current_user_can( 'manage_options' ) ? wp_send_json_error() : 0;

		$default_options = tutor_utils()->sanitize_recursively( $this->tutor_default_settings() );

		update_option( 'tutor_option', $default_options );

		wp_send_json_success( $default_options );
	}

	public function load_settings_page() {
		extract( $this->get_setting_fields() );

		if ( ! $template_path ) {
			$template_path = tutor()->path . '/views/options/settings.php';
		}

		include $template_path;
	}

	private function get_setting_fields() {

		if ( $this->setting_fields ) {
			// Return from property if already prepared
			return $this->setting_fields;
		}

		$pages       = tutor_utils()->get_pages();
		$lesson_url  = site_url() . '/course/' . 'sample-course/<code>lessons</code>/sample-lesson/';
		$student_url = tutor_utils()->profile_url();

		$methods_array     = array();
		$withdrawl_methods = apply_filters( 'tutor_withdrawal_methods_all', array() );

		foreach ( $withdrawl_methods as $key => $method ) {
			$methods_array[ $key ] = $method['method_name'];
		}

		$attr = array(
			'general'      => array(
				'label'    => __( 'General', 'tutor' ),
				'slug'     => 'general',
				'desc'     => __( 'General Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => __( 'earth', 'tutor' ),
				'blocks'   => array(
					array(
						'label'      => false,
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'tutor_dashboard_page_id',
								'type'    => 'select',
								'label'   => __( 'Dashboard Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'This page will be used for student and instructor dashboard', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Others', 'tutor' ),
						'slug'       => 'others',
						'block_type' => 'isolate',
						'fields'     => array(
							array(
								'key'         => 'enable_course_marketplace',
								'type'        => 'toggle_switch',
								'label'       => __( 'Enable Marketplace', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Allow multiple instructors to upload their courses.', 'tutor' ),
							),
							array(
								'key'     => 'pagination_per_page',
								'type'    => 'number',
								'label'   => __( 'Pagination', 'tutor' ),
								'default' => '20',
								'desc'    => __( 'Number of items you would like displayed "per page" in the pagination', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Instructor', 'tutor' ),
						'slug'       => 'instructor',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'instructor_can_publish_course',
								'type'        => 'toggle_switch',
								'label'       => __( 'Allow Instructors Publishing Courses', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enable instructors to publish the course directly. If disabled, admins will be able to review course content before publishing.', 'tutor' ),
							),
							array(
								'key'         => 'enable_become_instructor_btn',
								'type'        => 'toggle_switch',
								'label'       => __( 'Become Instructor Button', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Uncheck this option to hide the button from student dashboard.', 'tutor' ),
							),
						),
					),
				),
			),
			'course'       => array(
				'label'    => __( 'Course', 'tutor' ),
				'slug'     => 'course',
				'desc'     => __( 'Course Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => __( 'book-open', 'tutor' ),
				'blocks'   => array(
					'block_course' => array(
						'label'      => '',
						'slug'       => 'course',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'student_must_login_to_view_course',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Visibility', 'tutor' ),
								'label_title' => __( 'Logged Only', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Students must be logged in to view course', 'tutor' ),
							),
							array(
								'key'         => 'course_content_access_for_ia',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Content Access', 'tutor' ),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __( 'Allow instructors and admins to view the course content without enrolling', 'tutor' ),
							),
							array(
								'key'         => 'enable_spotlight_mode',
								'type'        => 'toggle_switch',
								'label'       => __( 'Spotlight mode', 'tutor' ),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __( 'This will hide the header and the footer and enable spotlight (full screen) mode when students view lessons.', 'tutor' ),
							),
							array(
								'key'            => 'course_completion_process',
								'type'           => 'radio_vertical',
								'label'          => __( 'Course Completion Process', 'tutor' ),
								'default'        => 'flexible',
								'select_options' => false,
								'options'        => array(
									'flexible' => __( 'Flexible', 'tutor' ),
									'strict'   => __( 'Strict Mode', 'tutor' ),
								),
								'desc'           => __( 'Students can complete courses anytime in the Flexible mode. In the Strict mode, students have to complete, pass all the lessons and quizzes (if any) to mark a course as complete.', 'tutor' ),
							),
							array(
								'key'         => 'course_retake_feature',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Retake', 'tutor' ),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __( 'Enabling this feature will allow students to reset course progress and start over.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Lesson', 'tutor' ),
						'slug'       => 'lesson',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_lesson_classic_editor',
								'type'        => 'toggle_switch',
								'label'       => __( 'Classic Editor for Lesson', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enable classic editor to edit lesson.', 'tutor' ),
							),
							array(
								'key'         => 'autoload_next_course_content',
								'type'        => 'toggle_switch',
								'label'       => __( 'Automatically load next course content.', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enabling this feature will be load next course content automatically after finishing current video.', 'tutor' ),
							),
						),
					),
					'block_quiz'   => array(
						'label'      => __( 'Quiz', 'tutor' ),
						'slug'       => 'quiz',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'          => 'quiz_time_limit',
								'type'         => 'group_fields',
								'label'        => __( 'Time Limit', 'tutor' ),
								'desc'         => __( '0 means unlimited time.', 'tutor' ),
								'group_fields' => array(
									'value' => array(
										'type'    => 'text',
										'default' => '0',
									),
									'time'  => array(
										'type'           => 'select',
										'default'        => 'minutes',
										'select_options' => false,
										'options'        => array(
											'weeks'   => __( 'Weeks', 'tutor' ),
											'days'    => __( 'Days', 'tutor' ),
											'hours'   => __( 'Hours', 'tutor' ),
											'minutes' => __( 'Minutes', 'tutor' ),
											'seconds' => __( 'Seconds', 'tutor' ),
										),
									),
								),
							),
							array(
								'key'            => 'quiz_when_time_expires',
								'type'           => 'radio_vertical',
								'label'          => __( 'When time expires', 'tutor' ),
								'default'        => 'grace_period',
								'select_options' => false,
								'options'        => array(
									'auto_submit'  => __( 'The current quiz answers are submitted automatically.', 'tutor' ),
									'grace_period' => __( 'The current quiz answers are submitted by students.', 'tutor' ),
									'auto_abandon' => __( 'Attempts must be submitted before time expires, otherwise they will not be counted', 'tutor' ),
								),
								'desc'           => __( 'Choose which action to follow when the quiz time expires.', 'tutor' ),
							),
							array(
								'key'     => 'quiz_attempts_allowed',
								'type'    => 'number',
								'label'   => __( 'Quiz Attempts allowed', 'tutor' ),
								'default' => '10',
								'desc'    => __( 'The highest number of attempts students are allowed to take for a quiz. 0 means unlimited attempts.', 'tutor' ),
							),
							array(
								'key'     => 'quiz_previous_button_disabled',
								'type'    => 'toggle_switch',
								'label'   => __( 'Hide Quiz Previous Button', 'tutor' ),
								'default' => 'off',
								'desc'    => __( 'Choose whether to show or hide previous button for single question.', 'tutor' ),
							),
							array(
								'key'     => 'quiz_grade_method',
								'type'    => 'radio_horizontal_full',
								'label'   => __( 'Final grade calculation', 'tutor' ),
								'desc'    => __( 'When multiple attempts are allowed, which method should be used to calculate a student\'s final grade for the quiz.', 'tutor' ),
								'default' => 'highest_grade',
								'options' => array(
									'highest_grade' => __( 'Highest Grade', 'tutor' ),
									'average_grade' => __( 'Average Grade', 'tutor' ),
									'first_attempt' => __( 'First Attempt', 'tutor' ),
									'last_attempt'  => __( 'Last Attempt', 'tutor' ),
								),
							),
						),
					),
					array(
						'label'      => __( 'Video', 'tutor' ),
						'slug'       => 'video',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'supported_video_sources',
								'type'        => 'checkbox_vertical',
								'default'     => array( 'youtube', 'vimeo' ),
								'label'       => __( 'Preferred Video Source', 'tutor' ),
								'label_title' => __( 'Preferred Video Source', 'tutor' ),
								'options'     => array(
									'html5'        => __( 'HTML 5 (mp4)', 'tutor' ),
									'external_url' => __( 'External URL', 'tutor' ),
									'youtube'      => __( 'Youtube', 'tutor' ),
									'vimeo'        => __( 'Vimeo', 'tutor' ),
									'embedded'     => __( 'Embedded', 'tutor' ),
								),
								'desc'        => __( 'Choose video sources you\'d like to support. Unchecking all will not disable video feature.', 'tutor' ),
							),
						),
					),
				),
			),
			'monetization' => array(
				'label'    => __( 'Monetization', 'tutor' ),
				'slug'     => 'monetization',
				'desc'     => __( 'Monitization Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => __( 'discount-filled', 'tutor' ),
				'blocks'   => array(
					array(
						'label'      => false,
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_tutor_earning',
								'type'        => 'toggle_switch',
								'label'       => __( 'Enable Monetization', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enable monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro', 'tutor' ),
							),
						),
					),
					'block_options' => array(
						'label'      => __( 'Options', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'            => 'monetize_by',
								'type'           => 'select',
								'label'          => __( 'Select eCommerce Engine', 'tutor' ),
								'select_options' => false,
								'options'        => apply_filters(
									'tutor_monetization_options',
									array(
										'free' => __( 'Disable Monetization', 'tutor' ),
									)
								),
								'default'        => 'free',
								'desc'           => __( 'Select a monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro', 'tutor' ),
							),
							array(
								'key'         => 'sharing_percentage',
								'type'        => 'double_input',
								'label'       => __( 'Sharing Percentage', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => '',
								'fields'      => array(
									'earning_instructor_commission' => array(
										'id'      => 'revenue-instructor',
										'type'    => 'ratio',
										'title'   => 'Instructor Takes',
										'default' => 0,
									),
									'earning_admin_commission' => array(
										'id'      => 'revenue-admin',
										'type'    => 'ratio',
										'title'   => 'Admin Takes',
										'default' => 100,
									),
								),
								'desc'        => __( 'Select a monetization option to generate revenue by selling courses. Supports: WooCommerce', 'tutor' ),
							),
							array(
								'key'         => 'enable_revenue_sharing',
								'type'        => 'toggle_switch',
								'label'       => __( 'Enable Revenue Sharing', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Content description', 'tutor' ),
							),
							array(
								'key'     => 'statement_show_per_page',
								'type'    => 'number',
								'label'   => __( 'Show Statement Per Page', 'tutor' ),
								'default' => '20',

								'desc'    => __( 'Define the number of statements to show.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Fees', 'tutor' ),
						'slug'       => 'fees',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_fees_deducting',
								'type'        => 'toggle_switch',
								'label'       => __( 'Deduct Fees', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Fees are charged from the entire sales amount. The remaining amount will be divided among admin and instructors.', 'tutor' ),
							),
							array(
								'key'         => 'fees_name',
								'type'        => 'text',
								'label'       => __( 'Fee Description', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'free',
							),
							array(
								'key'          => 'fee_amount_type',
								'type'         => 'group_fields',
								'label'        => __( 'Fee Amount & Type', 'tutor' ),
								'group_fields' => array(
									'fees_type'   => array(
										'type'    => 'select',
										'default' => 'fixed',
										'options' => array(
											'percent' => __( 'Percent', 'tutor' ),
											'fixed'   => __( 'Fixed', 'tutor' ),
										),
									),
									'fees_amount' => array(
										'type'    => 'number',
										'default' => '0',
									),
								),
							),
						),
					),
					array(
						'label'      => __( 'Withdraw', 'tutor' ),
						'slug'       => 'withdraw',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'min_withdraw_amount',
								'type'    => 'number',
								'label'   => __( 'Minimum Withdrawal Amount', 'tutor' ),
								'default' => '80',
								'desc'    => __( 'Instructors should earn equal or above this amount to make a withdraw request.', 'tutor' ),
							),
							array(
								'key'     => 'minimum_days_for_balance_to_be_available',
								'type'    => 'number',
								'label'   => __( 'Minimum Days for Balance to be Available', 'tutor' ),
								'default' => '80',
								'desc'    => __( 'Instructors should earn equal or above this amount to make a withdraw request.', 'tutor' ),
							),
							array(
								'key'     => 'tutor_withdrawal_methods',
								'type'    => 'checkbox_horizontal',
								'label'   => __( 'Enable withdraw method', 'tutor' ),
								'default' => array( 'bank_transfer_withdraw' ),
								'options' => $methods_array,
								'desc'    => __( 'Choose preferred filter options you\'d like to show in course archive page.', 'tutor' ),
							),
							array(
								'key'     => 'tutor_bank_transfer_withdraw_instruction',
								'type'    => 'textarea',
								'label'   => __( 'Bank Instructions', 'tutor' ),
								'default' => __( 'Write the up to date bank informations of your instructor here.', 'tutor' ),
								'desc'    => __( 'Write instruction for the instructor to fill bank information', 'tutor' ),
							),
						),
					),
				),
			),
			'design'       => array(
				'label'    => __( 'Design', 'tutor' ),
				'slug'     => 'design',
				'desc'     => __( 'Design Settings', 'tutor' ),
				'template' => 'design',
				'icon'     => __( 'design', 'tutor' ),
				'blocks'   => array(
					'block_course'    => array(
						'label'      => __( 'Course', 'tutor' ),
						'slug'       => 'course',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'courses_col_per_row',
								'type'    => 'radio_horizontal',
								'label'   => __( 'Column Per Row', 'tutor' ),
								'default' => '4',
								'options' => array(
									'1' => 'One',
									'2' => 'Two',
									'3' => 'Three',
									'4' => 'Four',
								),
								'desc'    => __( 'Define how many column you want to use to display courses.', 'tutor' ),
							),
							array(
								'key'         => 'course_archive_filter',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Filter', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Show sorting and filtering options on course archive page', 'tutor' ),
							),
							array(
								'key'     => 'courses_per_page',
								'type'    => 'number',
								'label'   => __( 'Pagination', 'tutor' ),
								'default' => '12',
								'desc'    => __( 'Number of items you want to be displayed "per page" in the pagination', 'tutor' ),
							),
							array(
								'key'     => 'supported_course_filters',
								'type'    => 'checkbox_horizontal',
								'label'   => __( 'Preferred Course Filters', 'tutor' ),
								'default' => array( 'search', 'category' ),
								'options' => array(
									'search'           => __( 'Keyword Search', 'tutor' ),
									'category'         => __( 'Category', 'tutor' ),
									'tag'              => __( 'Tag', 'tutor' ),
									'difficulty_level' => __( 'Difficulty Level', 'tutor' ),
									'price_type'       => __( 'Price Type', 'tutor' ),
								),
								'desc'    => __( 'Choose preferred filter options you\'d like to show in course archive page.', 'tutor' ),
							),
						),
					),
					'layout'          => array(
						'label'      => __( 'Layout', 'tutor' ),
						'slug'       => 'layout',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'           => 'instructor_list_layout',
								'type'          => 'group_radio',
								'label'         => __( 'Instructor List Layout', 'tutor' ),
								'desc'          => __( 'Choose a layout for the list of instructor inside a course page. You can change this any time.', 'tutor' ),
								'default'       => 'pp-top-full',
								'group_options' => array(
									'vertical'   => array(
										'pp-top-full' => array(
											'title' => 'Portrait',
											'image' => 'instructor-layout/intructor-portrait.svg',
										),
										'pp-cp'       => array(
											'title' => 'Cover',
											'image' => 'instructor-layout/instructor-cover.svg',
										),
										'pp-top-left' => array(
											'title' => 'Minimal',
											'image' => 'instructor-layout/instructor-minimal.svg',
										),
									),
									'horizontal' => array(
										'pp-left-full'   => array(
											'title' => 'Horizontal Portrait',
											'image' => 'instructor-layout/instructor-horizontal-portrait.svg',
										),
										'pp-left-middle' => array(
											'title' => 'Horizontal Minimal',
											'image' => 'instructor-layout/instructor-horizontal-minimal.svg',
										),
									),
								),
							),
							array(
								'key'           => 'public_profile_layout',
								'type'          => 'group_radio_full_3',
								'label'         => __( 'Public Profile Layout', 'tutor' ),
								'desc'          => __( 'Choose a layout design for a user’s public profile', 'tutor' ),
								'default'       => 'pp-rectangle',
								'group_options' => array(
									'private'      => array(
										'title' => 'Private',
										'image' => 'profile-layout/profile-private.svg',
									),
									'pp-circle'    => array(
										'title' => 'Modern',
										'image' => 'profile-layout/profile-modern.svg',
									),
									'no-cp'        => array(
										'title' => 'Minimal',
										'image' => 'profile-layout/profile-minimal.svg',
									),
									'pp-rectangle' => array(
										'title' => 'Classic',
										'image' => 'profile-layout/profile-classic.svg',
									),
								),
							),
						),
					),
					'course-details'  => array(
						'label'      => __( 'Course Details', 'tutor' ),
						'slug'       => 'course-details',
						'block_type' => 'isolate',
						'fields'     => array(
							array(
								'key'           => 'course_details_adjustments',
								'type'          => 'checkgroup',
								'label'         => __( 'Course Details Adjustments', 'tutor' ),
								'group_options' => array(
									array(
										'key'     => 'display_course_instructors',
										'type'    => 'toggle_single',
										'label'   => __( 'Instructor Info', 'tutor' ),
										'default' => 'off',
										'desc'    => __( 'Toggle to show instructor info', 'tutor' ),
									),
									array(
										'key'     => 'enable_q_and_a_on_course',
										'type'    => 'toggle_single',
										'label'   => __( 'Q&A', 'tutor' ),
										'default' => 'on',
										'desc'    => __( 'Enable to add a Q&A section', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_author',
										'type'        => 'toggle_single',
										'label'       => __( 'Disable Author', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Enabling to remove course author name', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_level',
										'type'        => 'toggle_single',
										'label'       => __( 'Disable Level', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Toggle to remove course level', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_share',
										'type'        => 'toggle_single',
										'label'       => __( 'Disable Share', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Toggle to hide course share', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_duration',
										'type'        => 'toggle_single',
										'label'       => __( 'Duration', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Enable to show course duration', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_total_enrolled',
										'type'        => 'toggle_single',
										'label'       => __( 'Enrolled Students', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Enable to show total enrolled students', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_update_date',
										'type'        => 'toggle_single',
										'label'       => __( 'Update Date', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Disable to hide course update infromation', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_progress_bar',
										'type'        => 'toggle_single',
										'label'       => __( 'Progress Bar', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Disable to hide course progress for Students', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_material',
										'type'        => 'toggle_single',
										'label'       => __( 'Material', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Disable to hide course materials', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_about',
										'type'        => 'toggle_single',
										'label'       => __( 'About', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Disable to hide course about section', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_description',
										'type'        => 'toggle_single',
										'label'       => __( 'Description', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Disable to hide course description', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_benefits',
										'type'        => 'toggle_single',
										'label'       => __( 'Benefits', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Disable to hide course benefits section', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_requirements',
										'type'        => 'toggle_single',
										'label'       => __( 'Pre-Requirements', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Disable to hide courses requirements setion', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_target_audience',
										'type'        => 'toggle_single',
										'label'       => __( 'Target Audience', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Enable to show course target audience setion', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_announcements',
										'type'        => 'toggle_single',
										'label'       => __( 'Announcements', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Disable to hide course announcements settion', 'tutor' ),
									),
									array(
										'key'         => 'disable_course_review',
										'type'        => 'toggle_single',
										'label'       => __( 'Review', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Disable to hide course review section', 'tutor' ),
									),
								),
								'desc'          => __( 'Content Needed Here...', 'tutor' ),
							),
						),
					),
					'colors'          => array(
						'label'        => __( 'Colors', 'tutor' ),
						'slug'         => 'colors',
						'block_type'   => 'color_picker',
						'fields_group' => array(
							array(
								'key'     => 'color_preset_type',
								'type'    => 'color_preset',
								'label'   => __( 'Preset Colors', 'tutor' ),
								'desc'    => __( 'These colors will be used throughout your website. Choose between these presets or create your own custom palette.', 'tutor' ),
								'default' => 'default',
								'fields'  => array(
									/* First 4 preset_name should be same as color_fields */
									array(
										'key'    => 'default',
										'label'  => 'Default',
										'colors' => array(
											array(
												'slug'  => 'tutor_primary_color',
												'preset_name' => 'primary',
												'value' => '#3E64DE',
											),
											array(
												'slug'  => 'tutor_primary_hover_color',
												'preset_name' => 'hover',
												'value' => '#395BCA',
											),
											array(
												'slug'  => 'tutor_text_color',
												'preset_name' => 'text',
												'value' => '#212327',
											),
											array(
												'slug'  => 'tutor_background_color',
												'preset_name' => 'background',
												'value' => '#F6F8FD',
											),
											array(
												'slug'  => 'tutor_border_color',
												'preset_name' => 'border',
												'value' => '#CDCFD5',
											),
											array(
												'slug'  => 'tutor_success_color',
												'preset_name' => 'success',
												'value' => '#24A148',
											),
											array(
												'slug'  => 'tutor_warning_color',
												'preset_name' => 'warning',
												'value' => '#ED9700',
											),
											array(
												'slug'  => 'tutor_danger_color',
												'preset_name' => 'danger',
												'value' => '#F44337',
											),
											array(
												'slug'  => 'tutor_disable_color',
												'preset_name' => 'disable',
												'value' => '#E3E6EB',
											),
											array(
												'slug'  => 'tutor_table_background_color',
												'preset_name' => 'table_background',
												'value' => '#EFF1F6',
											),
										),
									),
									array(
										'key'    => 'landscape',
										'label'  => 'Landscape',
										'colors' => array(
											array(
												'slug'  => 'tutor_primary_color',
												'preset_name' => 'primary',
												'value' => '#239371',
											),
											array(
												'slug'  => 'tutor_primary_hover_color',
												'preset_name' => 'hover',
												'value' => '#117D5D',
											),
											array(
												'slug'  => 'tutor_text_color',
												'preset_name' => 'text',
												'value' => '#212327',
											),
											array(
												'slug'  => 'tutor_background_color',
												'preset_name' => 'background',
												'value' => '#ECF7F3',
											),
											array(
												'slug'  => 'tutor_border_color',
												'preset_name' => 'border',
												'value' => '#CDCFD5',
											),
											array(
												'slug'  => 'tutor_success_color',
												'preset_name' => 'success',
												'value' => '#24A148',
											),
											array(
												'slug'  => 'tutor_warning_color',
												'preset_name' => 'warning',
												'value' => '#ED9700',
											),
											array(
												'slug'  => 'tutor_danger_color',
												'preset_name' => 'danger',
												'value' => '#F44337',
											),
											array(
												'slug'  => 'tutor_disable_color',
												'preset_name' => 'disable',
												'value' => '#E3E6EB',
											),
											array(
												'slug'  => 'tutor_table_background_color',
												'preset_name' => 'table_background',
												'value' => '#EFF1F6',
											),
										),
									),
									array(
										'key'    => 'ocean',
										'label'  => 'Ocean',
										'colors' => array(
											array(
												'slug'  => 'tutor_primary_color',
												'preset_name' => 'primary',
												'value' => '#5A18C2',
											),
											array(
												'slug'  => 'tutor_primary_hover_color',
												'preset_name' => 'hover',
												'value' => '#3F02A0',
											),
											array(
												'slug'  => 'tutor_text_color',
												'preset_name' => 'text',
												'value' => '#212327',
											),
											array(
												'slug'  => 'tutor_background_color',
												'preset_name' => 'background',
												'value' => '#FAF6FF',
											),
											array(
												'slug'  => 'tutor_border_color',
												'preset_name' => 'border',
												'value' => '#CDCFD5',
											),
											array(
												'slug'  => 'tutor_success_color',
												'preset_name' => 'success',
												'value' => '#24A148',
											),
											array(
												'slug'  => 'tutor_warning_color',
												'preset_name' => 'warning',
												'value' => '#ED9700',
											),
											array(
												'slug'  => 'tutor_danger_color',
												'preset_name' => 'danger',
												'value' => '#F44337',
											),
											array(
												'slug'  => 'tutor_disable_color',
												'preset_name' => 'disable',
												'value' => '#E3E6EB',
											),
											array(
												'slug'  => 'tutor_table_background_color',
												'preset_name' => 'table_background',
												'value' => '#EFF1F6',
											),
										),
									),
									array(
										'key'    => 'custom',
										'label'  => 'Custom',
										'colors' => array(
											array(
												'slug'  => 'tutor_primary_color',
												'preset_name' => 'primary',
												'value' => '#3E64DE',
											),
											array(
												'slug'  => 'tutor_primary_hover_color',
												'preset_name' => 'hover',
												'value' => '#28408E',
											),
											array(
												'slug'  => 'tutor_text_color',
												'preset_name' => 'text',
												'value' => '#1A1B1E',
											),
											array(
												'slug'  => 'tutor_background_color',
												'preset_name' => 'background',
												'value' => '#F6F8FD',
											),
										),
									),
								),
							),
							array(
								'key'    => 'tutor_color_presets',
								'type'   => 'color_fields',
								'label'  => __( 'Preset Colors', 'tutor' ),
								'fields' => array(
									array(
										'key'          => 'tutor_primary_color',
										'type'         => 'color_field',
										'preset_name'  => 'primary',
										'preset_exist' => true,
										'label'        => __( 'Primary Color', 'tutor' ),
										'default'      => '#3E64DE',
										'desc'         => __( 'Choose a custom primary color', 'tutor' ),
									),
									array(
										'key'          => 'tutor_primary_hover_color',
										'type'         => 'color_field',
										'preset_name'  => 'hover',
										'preset_exist' => true,
										'label'        => __( 'Primary Hover color', 'tutor' ),
										'default'      => '#395BCA',
										'desc'         => __( 'Choose a custom primary hover color', 'tutor' ),
									),
									array(
										'key'          => 'tutor_text_color',
										'type'         => 'color_field',
										'preset_name'  => 'text',
										'preset_exist' => true,
										'label'        => __( 'Text Color', 'tutor' ),
										'default'      => '#212327',
										'desc'         => __( 'Choose a Text Color for your website text', 'tutor' ),
									),
									array(
										'key'          => 'tutor_background_color',
										'type'         => 'color_field',
										'preset_name'  => 'background',
										'preset_exist' => true,
										'label'        => __( 'Background', 'tutor' ),
										'default'      => '#FFFFFF',
										'desc'         => __( 'Choose a background color for your website', 'tutor' ),
									),
									array(
										'key'          => 'tutor_border_color',
										'type'         => 'color_field',
										'preset_name'  => 'border',
										'preset_exist' => false,
										'label'        => __( 'Border', 'tutor' ),
										'default'      => '#CDCFD5',
										'desc'         => __( 'Choose a light color for your website ', 'tutor' ),
									),
									array(
										'key'          => 'tutor_success_color',
										'type'         => 'color_field',
										'preset_name'  => 'success',
										'preset_exist' => false,
										'label'        => __( 'Success', 'tutor' ),
										'default'      => '#24A148',
										'desc'         => __( 'Choose a color for an operation success message', 'tutor' ),
									),
									array(
										'key'          => 'tutor_warning_color',
										'type'         => 'color_field',
										'preset_name'  => 'warning',
										'preset_exist' => false,
										'label'        => __( 'Warning', 'tutor' ),
										'default'      => '#ED9700',
										'desc'         => __( 'Choose a color for an operation pending message ', 'tutor' ),
									),
									array(
										'key'          => 'tutor_danger_color',
										'type'         => 'color_field',
										'preset_name'  => 'danger',
										'preset_exist' => false,
										'label'        => __( 'Danger', 'tutor' ),
										'default'      => '#d8d8d8',
										'desc'         => __( 'Choose a color for an operation error message ', 'tutor' ),
									),
									array(
										'key'          => 'tutor_disable_color',
										'type'         => 'color_field',
										'preset_name'  => 'disable',
										'preset_exist' => false,
										'label'        => __( 'Disable', 'tutor' ),
										'default'      => '#E3E6EB',
										'desc'         => __( 'Choose a color for disabled elements ', 'tutor' ),
									),
									array(
										'key'          => 'tutor_table_background_color',
										'type'         => 'color_field',
										'preset_name'  => 'table_background',
										'preset_exist' => false,
										'label'        => __( 'Table Background', 'tutor' ),
										'default'      => '#EFF1F6',
										'desc'         => __( 'Choose a color for the background of table elements ', 'tutor' ),
									),
								),
							),
						),
					),
					'student_profile' => array(
						'label'      => __( 'Student Profile', 'tutor' ),
						'slug'       => 'student_profile',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'students_own_review_show_at_profile',
								'type'    => 'toggle_switch',
								'label'   => __( 'Show reviews on profile', 'tutor' ),
								'default' => 'on',
								'desc'    => __( 'Enabling this will show the reviews written by each student on their profile', 'tutor' ) . '<br />' . $student_url,
							),
							array(
								'key'     => 'show_courses_completed_by_student',
								'type'    => 'toggle_switch',
								'label'   => __( 'Show completed courses', 'tutor' ),
								'default' => 'on',
								'desc'    => __( 'Completed courses will be shown on student profiles. <br/> For example, you can see this link-', 'tutor' ) . $student_url,
							),
						),
					),
					'video_player'    => array(
						'label'      => __( 'Video Player', 'tutor' ),
						'slug'       => 'video_player',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'disable_default_player_youtube',
								'type'        => 'toggle_switch',
								'label'       => __( 'Use Tutor Player for YouTube', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enable this option to use Tutor LMS video player.', 'tutor' ),
							),
							array(
								'key'         => 'disable_default_player_vimeo',
								'type'        => 'toggle_switch',
								'label'       => __( 'Use Tutor Player for Vimeo', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enable this option to use Tutor LMS video player.', 'tutor' ),
							),
						),
					),
				),
			),
			'advanced'     => array(
				'label'    => __( 'Advanced', 'tutor' ),
				'slug'     => 'advanced',
				'desc'     => __( 'Advanced Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => __( 'filter', 'tutor' ),
				'blocks'   => array(
					array(
						'label'      => __( 'Course', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_gutenberg_course_edit',
								'type'        => 'toggle_switch',
								'label'       => __( 'Gutenberg Editor', 'tutor' ),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __( 'Use Gutenberg editor on course description area.', 'tutor' ),
							),
							array(
								'key'         => 'hide_course_from_shop_page',
								'type'        => 'toggle_switch',
								'label'       => __( 'Hide course products from shop page', 'tutor' ),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __( 'Enabling this feature will remove course products from the shop page.', 'tutor' ),
							),
							array(
								'key'     => 'course_archive_page',
								'type'    => 'select',
								'label'   => __( 'Course Archive Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'This page will be used to list all the published courses.', 'tutor' ),
							),
							array(
								'key'     => 'instructor_register_page',
								'type'    => 'select',
								'label'   => __( 'Instructor Registration Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'This page will be used to sign up new instructors.', 'tutor' ),
							),
							array(
								'key'     => 'student_register_page',
								'type'    => 'select',
								'label'   => __( 'Student Registration Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'Choose the page for student registration page', 'tutor' ),
							),
							array(
								'key'     => 'lesson_permalink_base',
								'type'    => 'text',
								'label'   => __( 'Lesson Permalink Base', 'tutor' ),
								'default' => 'lessons',
								'desc'    => $lesson_url,
							),
							array(
								'key'     => 'lesson_video_duration_youtube_api_key',
								'type'    => 'text',
								'label'   => __( 'Youtube API Key', 'tutor' ),
								'default' => '',
								'desc'    => __( 'To get dynamic video duration from Youtube, you need to set API key first', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Options', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_profile_completion',
								'type'        => 'toggle_switch',
								'label'       => __( 'Profile Completion', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enabling this feature will show a notification bar to students and instructors to complete their profile information', 'tutor' ),
							),
							array(
								'key'         => 'disable_tutor_native_login',
								'type'        => 'toggle_switch',
								'label'       => __( 'Disbale Tutor Login', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'on',
								'desc'        => __( 'Disable to use the default WordPress login page', 'tutor' ),
							),
							array(
								'key'         => 'hide_admin_bar_for_users',
								'type'        => 'toggle_switch',
								'label'       => __( 'Hide Frontend Admin Bar', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Hide admin bar option allow you to hide WordPress admin bar entirely from the frontend. It will still show to administrator roles user', 'tutor' ),
							),
							array(
								'key'         => 'delete_on_uninstall',
								'type'        => 'toggle_switch',
								'label'       => __( 'Erase upon uninstallation', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Delete all data during uninstallation', 'tutor' ),
							),
							array(
								'key'         => 'enable_tutor_maintenance_mode',
								'type'        => 'toggle_switch',
								'label'       => __( 'Maintenance Mode', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enabling the maintenance mode allows you to display a custom message on the frontend. During this time, visitors can not access the site content. But the wp-admin dashboard will remain accessible.', 'tutor' ),
							),
						),
					),
				),
			),
		);

		$attrs = apply_filters( 'tutor/options/extend/attr', apply_filters( 'tutor/options/attr', $attr ) );

		// Get the active tab
		$tab_page = tutor_utils()->array_get( 'tab_page', $_REQUEST, 'general' );
		$tab_data = null;
		$template = null;

		foreach ( $attrs as $key => $section ) {
			if ( $tab_page == $key ) {
				if ( isset( $section['template_path'] ) && $section['template_path'] ) {
					$template = $section['template_path'];
					$tab_data = $section;
				}
				break;
			}
		}

		// Store in runtime cache
		$this->setting_fields = array(
			'option_fields'   => $attrs,
			'active_tab'      => $tab_page,
			'active_tab_data' => $tab_data,
			'template_path'   => $template,
		);

		return $this->setting_fields;
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 *
	 * Generate Option Field
	 */
	public function generate_field( $field = array() ) {
		ob_start();
		include tutor()->path . "views/options/field-types/{$field['type']}.php";

		return ob_get_clean();
	}

	public function field_type( $field = array() ) {
		ob_start();
		include tutor()->path . "views/options/field-types/{$field['type']}.php";

		return ob_get_clean();
	}

	public function blocks( $blocks = array() ) {
		ob_start();
		include tutor()->path . 'views/options/option_blocks.php';
		return ob_get_clean();
	}

	public function template( $section = array() ) {
		ob_start();
		$blocks = $section['blocks'];
		include tutor()->path . "views/options/template/{$section['template']}.php";
		return ob_get_clean();
	}

	/*
	 public function this_confirmation( $modal = array() ) {
		ob_start();
		require tutor()->path . 'views/options/template/modal-confirm.php';
		return ob_get_clean();
	} */

	/**
	 * Load template inside template dirctory
	 *
	 * @param  mixed $template_slug
	 * @param  mixed $section
	 * @return void
	 */
	public function view_template( $template_slug, $section = array() ) {
		ob_start();
		require tutor()->path . "views/options/template/{$template_slug}";
		return ob_get_clean();
	}

}
