<?php

namespace Tutor;

if (!defined('ABSPATH')) {
	exit;
}

class Options_V2 {

	public $option;
	public $options_attr;

	public function __construct() {
		$this->option       = (array) maybe_unserialize(get_option('tutor_option'));
		$this->options_attr = $this->options_attr();

		//Saving option
		add_action('wp_ajax_tutor_option_save', array($this, 'tutor_option_save'));
	}

	private function get($key = null, $default = false) {
		$option = $this->option;

		if (empty($option) || !is_array($option)) {
			return $default;
		}

		if (!$key) {
			return $option;
		}

		if (array_key_exists($key, $option)) {
			return apply_filters($key, $option[$key]);
		}

		//Access array value via dot notation, such as option->get('value.subvalue')
		if (strpos($key, '.')) {
			$option_key_array = explode('.', $key);
			$new_option       = $option;
			foreach ($option_key_array as $dotKey) {
				if (isset($new_option[$dotKey])) {
					$new_option = $new_option[$dotKey];
				} else {
					return $default;
				}
			}

			return apply_filters($key, $new_option);
		}

		return $default;
	}

	public function tutor_option_save() {
		tutils()->checking_nonce();

		!current_user_can('manage_options') ? wp_send_json_error() : 0;

		do_action('tutor_option_save_before');

		$option = (array) tutils()->array_get('tutor_option', $_POST, array());
		$option = apply_filters('tutor_option_input', $option);
		update_option('tutor_option', $option);

		do_action('tutor_option_save_after');

		//re-sync settings
		//init::tutor_activate();

		wp_send_json_success(array('msg' => __('Option Updated', 'tutor')));
	}

	public function options_attr() {
		$pages = tutor_utils()->get_pages();

		//$course_base = tutor_utils()->course_archive_page_url();
		$lesson_url                    = site_url() . '/course/' . 'sample-course/<code>lessons</code>/sample-lesson/';
		$student_url                   = tutor_utils()->profile_url();
		$attempts_allowed              = array();
		$attempts_allowed['unlimited'] = __('Unlimited', 'tutor');
		$attempts_allowed              = array_merge($attempts_allowed, array_combine(range(1, 20), range(1, 20)));

		$video_sources = array(
			'html5'        => __('HTML 5 (mp4)', 'tutor'),
			'external_url' => __('External URL', 'tutor'),
			'youtube'      => __('Youtube', 'tutor'),
			'vimeo'        => __('Vimeo', 'tutor'),
			'embedded'     => __('Embedded', 'tutor'),
		);

		$course_filters = array(
			'search'           => __('Keyword Search', 'tutor'),
			'category'         => __('Category', 'tutor'),
			'tag'              => __('Tag', 'tutor'),
			'difficulty_level' => __('Difficulty Level', 'tutor'),
			'price_type'       => __('Price Type', 'tutor'),
		);

		$attr = array(
			'basic'  => array(
				'label'    => __('Basic', 'tutor'),
				'sections' => array(
					array(
						'label'  => __('General', 'tutor'),
						'slug'   => 'general',
						'desc'   => __('General Settings', 'tutor'),
						'template'   => 'basic',
						'icon'   => __('earth', 'tutor'),
						'blocks' => array(
							array(
								'label'      => false,
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'    => 'tutor_dashboard_page_id',
										'type'    => 'select',
										'label'   => __('Dashboard Page', 'tutor'),
										'default' => '0',

										'options' => $pages,
										'desc'    => __('This page will be used for student and instructor dashboard', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('Course', 'tutor'),
								'slug'       => 'course',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'        => 'student_must_login_to_view_course',
										'type'        => 'toggle_switch',
										'label'       => __('Course Visibility', 'tutor'),
										'label_title' => __('Logged in only', 'tutor'),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'    => 'course_archive_page',
										'type'    => 'select',
										'label'   => __('Course Archive Page', 'tutor'),
										'default' => '0',

										'options' => $pages,
										'desc'    => __('This page will be used to list all the published courses.', 'tutor'),
									),
									array(
										'key'        => 'course_content_access_for_ia',
										'type'        => 'toggle_switch',
										'label'       => __('Course Content Access', 'tutor'),
										'label_title' => null,
										'desc'        => __('Allow instructors and admins to view the course content without enrolling', 'tutor'),
									),
									array(
										'key'           => 'course_completion_process',
										'type'           => 'radio_vertical',
										'label'          => __('Course Completion Process', 'tutor'),
										'default'        => 'flexible',

										'select_options' => false,
										'options'        => array(
											'flexible' => __('Flexible', 'tutor'),
											'strict'   => __('Strict Mode', 'tutor'),
										),
										'desc'           => __('Students can complete courses anytime in the Flexible mode. In the Strict mode, students have to complete, pass all the lessons and quizzes (if any) to mark a course as complete.', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('Video', 'tutor'),
								'slug'       => 'video',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'    => 'supported_video_sources',
										'type'    => 'checkbox_horizontal',
										'label'   => __('Preferred Video Source', 'tutor'),
										'label_title' => __('Preferred Video Source', 'tutor'),
										'options' => $video_sources,
										'desc'    => __('Choose video sources you\'d like to support. Unchecking all will not disable video feature.', 'tutor'),
									),
									array(
										'key'    => 'default_video_source',
										'type'    => 'select',
										'label'   => __('Default Video Source', 'tutor'),
										'default' => '',

										'options' => $video_sources,
										'desc'    => __('Choose video source to be selected by default.', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('Others', 'tutor'),
								'slug'       => 'others',
								'block_type' => 'isolate',
								'fields'     => array(
									array(
										'key'    => 'lesson_permalink_base',
										'type'    => 'text',
										'label'   => __('Lesson Permalink Base', 'tutor'),
										'default' => 'lessons',

										'desc'    => $lesson_url,
									),
									array(
										'key'    => 'student_register_page',
										'type'    => 'select',
										'label'   => __('Student Registration Page', 'tutor'),
										'default' => '0',

										'options' => $pages,
										'desc'    => __('Choose the page for student registration page', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('Instructor', 'tutor'),
								'slug'       => 'instructor',
								'block_type' => 'uniform',
								'fields'     => array(
									'instructor_register_page'      => array(
										'type'    => 'select',
										'label'   => __('Instructor Registration Page', 'tutor'),
										'default' => '0',

										'options' => $pages,
										'desc'    => __('This page will be used to sign up new instructors.', 'tutor'),
									),
									'instructor_can_publish_course' => array(
										'type'        => 'checkbox',
										'label'       => __('Allow Instructors Publishing Courses', 'tutor'),
										'label_title' => __('Enable', 'tutor'),
										'default'     => '0',

										'desc'        => __('Enable instructors to publish the course directly. If disabled, admins will be able to review course content before publishing.', 'tutor'),
									),
									'enable_become_instructor_btn'  => array(
										'type'        => 'checkbox',
										'label'       => __('Become Instructor Button', 'tutor'),
										'label_title' => __('Enable', 'tutor'),
										'default'     => '0',

										'desc'        => __('Uncheck this option to hide the button from student dashboard.', 'tutor'),
									),
								),
							),
						),
					),
					array(
						'label'  => __('Course', 'tutor'),
						'slug'   => 'course',
						'desc'   => __('Course Settings', 'tutor'),
						'template'   => 'basic',
						'icon'   => __('book-open', 'tutor'),
						'blocks' => array(
							array(
								'label'      => __('Lesson', 'tutor'),
								'slug'       => 'lesson',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'        => 'student_must_login_to_view_course',
										'type'      => 'toggle_switch',
										'label'     => __('Course Visibility', 'tutor'),
										'label_title' => __('Logged in only', 'tutor'),
										'desc'      => __('Students must be logged in to view course', 'tutor'),
									),
									'course_content_access_for_ia' => array(
										'key'        => 'autoload_next_course_content',
										'type'      => 'toggle_switch',
										'label'     => __('Course Content Access', 'tutor'),
										'label_title'   => __('', 'tutor'),
										'desc' => __('Allow instructors and admins to view the course content without enrolling', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('Quiz', 'tutor'),
								'slug'       => 'quiz',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'         => 'quiz_time_limit',
										'type'         => 'group_fields',
										'label'        => __('Time Limit', 'tutor'),
										'desc'         => __('0 means unlimited time.', 'tutor'),
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
													'weeks'   => __('Weeks', 'tutor'),
													'days'    => __('Days', 'tutor'),
													'hours'   => __('Hours', 'tutor'),
													'minutes' => __('Minutes', 'tutor'),
													'seconds' => __('Seconds', 'tutor'),
												),
											),
										),
									),
									array(
										'key'           => 'quiz_when_time_expires',
										'type'           => 'radio_vertical',
										'label'          => __('When time expires', 'tutor'),
										'default'        => 'minutes',

										'select_options' => false,
										'options'        => array(
											'auto_submit'  => __('The current quiz answers are submitted automatically.', 'tutor'),
											'grace_period' => __('The current quiz answers are submitted by students.', 'tutor'),
											'auto_abandon' => __('Attempts must be submitted before time expires, otherwise they will not be counted', 'tutor'),
										),
										'desc'           => __('Choose which action to follow when the quiz time expires.', 'tutor'),
									),
									array(
										'key'    => 'quiz_attempts_allowed',
										'type'    => 'number',
										'label'   => __('Attempts allowed', 'tutor'),
										'default' => '10',

										'desc'    => __('The highest number of attempts students are allowed to take for a quiz. 0 means unlimited attempts.', 'tutor'),
									),
									array(
										'key'           => 'quiz_grade_method',
										'type'           => 'select',
										'label'          => __('Final grade calculation', 'tutor'),
										'default'        => 'minutes',

										'select_options' => false,
										'options'        => array(
											'highest_grade' => __('Highest Grade', 'tutor'),
											'average_grade' => __('Average Grade', 'tutor'),
											'first_attempt' => __('First Attempt', 'tutor'),
											'last_attempt'  => __('Last Attempt', 'tutor'),
										),
										'desc'           => __('When multiple attempts are allowed, which method should be used to calculate a student\'s final grade for the quiz.', 'tutor'),
									),
								),
							),
						),
					),
					array(
						'label'  => __('Monitization', 'tutor'),
						'slug'   => 'monitization',
						'desc'   => __('Monitization Settings', 'tutor'),
						'template'   => 'basic',
						'icon'   => __('discount-filled', 'tutor'),
						'blocks' => array(
							array(
								'label'      => false,
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'           => 'monetize_by',
										'type'           => 'toggle_switch',
										'label'          => __('Disable Monetization', 'tutor'),
										'label_title'          => __('', 'tutor'),
										'default'        => 'free',

										'select_options' => false,
										'options'        => apply_filters('tutor_monetization_options', array(
											'free' => __('Disable Monetization', 'tutor'),
										)),
										'desc'           => __('Select a monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('Options', 'tutor'),
								'slug'       => 'options',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'    => 'statement_show_per_page',
										'type'    => 'number',
										'label'   => __('Show Statement Per Page', 'tutor'),
										'default' => '20',

										'desc'    => __('Define the number of statements to show.', 'tutor'),
									),
								),
							),
						),
					),
					array(
						'label'  => __('Design', 'tutor'),
						'slug'   => 'design',
						'desc'   => __('Design Settings', 'tutor'),
						'template'   => 'design',
						'icon'   => __('design', 'tutor'),
						'blocks' => array(
							array(
								'label'      => __('Course', 'tutor'),
								'slug'       => 'course',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'      => 'course_builder_page_logo',
										'type'      => 'upload_full',
										'label'     => __('Course Builder Page Logo', 'tutor'),
										'desc'      => __('<p>
											Size: <strong>200x40 pixels;</strong> File Support:
											<strong>jpg, .jpeg or .png.</strong>
										</p>', 'tutor'),
									),
									array(
										'key'      => 'courses_col_per_row',
										'type'      => 'radio_horizontal',
										'label'     => __('Column Per Row', 'tutor'),
										'default'   => '4',

										'options'   => array(
											'one' => 'One',
											'two' => 'Two',
											'three' => 'Three',
											'four' => 'Four'
										),
										'desc'      => __('Define how many column you want to use to display courses.', 'tutor'),
									),
									array(
										'key'      => 'course_archive_filter',
										'type'      => 'toggle_switch',
										'label'     => __('Course Filter', 'tutor'),
										'label_title'   => __('', 'tutor'),
										'desc' => __('Show sorting and filtering options on course archive page', 'tutor'),
									),
									array(
										'key'      => 'supported_course_filters',
										'type'      => 'checkbox_horizontal',
										'label'     => __('Preferred Course Filters', 'tutor'),
										'label_title' => __('', 'tutor'),
										'options'	=> $course_filters,
										'desc'      => __('Choose preferred filter options you\'d like to show in course archive page.', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('Layout', 'tutor'),
								'slug'       => 'layout',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'    => 'instructor_list_layout',
										'type'    => 'group_radio',
										'label'   => __('Instructor List Layout', 'tutor'),
										'group_options' => array(
											'vertical' => array(
												'portrait' => array(
													'title' => 'Portrait',
													'image' => 'instructor-layout/intructor-portrait.svg',
												),
												'cover' => array(
													'title' => 'Cover',
													'image' => 'instructor-layout/instructor-cover.svg',
												),
												'minimal' => array(
													'title' => 'Minimal',
													'image' => 'instructor-layout/instructor-minimal.svg',
												)
											),
											'horizontal' => array(
												'horizontal_portrait' => array(
													'title' => 'Minimal',
													'image' => 'instructor-layout/instructor-horizontal-portrait.svg',
												),
												'horizontal_minimal' => array(
													'title' => 'Minimal',
													'image' => 'instructor-layout/instructor-horizontal-minimal.svg',
												)
											),
										),
										'desc'    => __('Content Needed Here...', 'tutor'),
									),
									array(
										'key'    => 'public_profile_layout',
										'type'    => 'group_radio_full_3',
										'label'   => __('Public Profile Layout', 'tutor'),
										'group_options' => array(
											'modern' => array(
												'title' => 'Modern',
												'image' => 'profile-layout/profile-modern.svg',
											),
											'minimal' => array(
												'title' => 'Minimal',
												'image' => 'profile-layout/profile-minimal.svg',
											),
											'classic' => array(
												'title' => 'Classic',
												'image' => 'profile-layout/profile-classic.svg',
											)
										),
										'desc'    => __('Content Needed Here...', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('Course Details', 'tutor'),
								'slug'       => 'course-details',
								'block_type' => 'isolate',
								'fields'    => array(
									array(
										'key'    => 'Public Profile Layout',
										'type'    => 'checkgroup',
										'label'   => __('Public Profile Layout', 'tutor'),
										'group_options' => array(
											array(
												'key'      => 'display_course_instructors',
												'type'      => 'toggle_single',
												'label'     => __('Instructor Info', 'tutor'),
												'label_title'   => __('Enable', 'tutor'),
												'desc' => __('Show instructor bio on each page', 'tutor'),
											),
											array(
												'key'      => 'enable_q_and_a_on_course',
												'type'      => 'toggle_single',
												'label'     => __('Question and Answer', 'tutor'),
												'label_title' => __('Enable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Enabling this feature will add a Q&amp;A section on every course.',	'tutor'),
											),
											array(
												'key'      => 'disable_course_author',
												'type'      => 'toggle_single',
												'label'     => __('Author', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course author name from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_level',
												'type'      => 'toggle_single',
												'label'     => __('Course Level', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course level from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_share',
												'type'      => 'toggle_single',
												'label'     => __('Course Share', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course share option from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_duration',
												'type'      => 'toggle_single',
												'label'     => __('Course Duration', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course duration from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_total_enrolled',
												'type'      => 'toggle_single',
												'label'     => __('Course Total Enrolled', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course total enrolled from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_update_date',
												'type'      => 'toggle_single',
												'label'     => __('Course Update Date', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course update date from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_progress_bar',
												'type'      => 'toggle_single',
												'label'     => __('Course Progress Bar', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed completing progress bar from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_material',
												'type'      => 'toggle_single',
												'label'     => __('Course Material', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course material from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_about',
												'type'      => 'toggle_single',
												'label'     => __('Course About', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course about from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_description',
												'type'      => 'toggle_single',
												'label'     => __('Course Description', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course description from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_benefits',
												'type'      => 'toggle_single',
												'label'     => __('Course Benefits', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course benefits from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_requirements',
												'type'      => 'toggle_single',
												'label'     => __('Course Requirements', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course requirements from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_target_audience',
												'type'      => 'toggle_single',
												'label'     => __('Course Target Audience', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course target audience from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_announcements',
												'type'      => 'toggle_single',
												'label'     => __('Course Announcements', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course announcements from the course page.', 'tutor'),
											),
											array(
												'key'      => 'disable_course_review',
												'type'      => 'toggle_single',
												'label'     => __('Course Review', 'tutor'),
												'label_title' => __('Disable', 'tutor'),
												'default'   => '0',

												'desc'      => __('Disabling this feature will be removed course review system from the course page.', 'tutor'),
											),
											array(
												'key'      => 'supported_video_sources',
												'type'      => 'toggle_single',
												'label'     => __('Preferred Video Source', 'tutor'),
												'options'	=> $video_sources,
												'desc'      => __('Choose video sources you\'d like to support. Unchecking all will not disable video feature.', 'tutor'),
											),
										),
										'desc'    => __('Content Needed Here...', 'tutor'),
									),
									array(
										'key'      => 'default_video_source',
										'type'      => 'select',
										'label'     => __('Default Video Source', 'tutor'),
										'default'   => '',

										'options'   => $video_sources,
										'desc'      => __('Choose video source to be selected by default.',	'tutor'),
									),
								),
							),
						),
					),
					array(
						'label'  => __('Advanced', 'tutor'),
						'slug'   => 'advanced',
						'desc'   => __('Advanced Settings', 'tutor'),
						'template'   => 'basic',
						'icon'   => __('filter', 'tutor'),
						'blocks' => array(
							array(
								'label'      => __('Options', 'tutor'),
								'slug'       => 'options',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'      => 'login_error_message',
										'type'      => 'toggle_switch',
										'label'     => __('Error message for wrong login credentials', 'tutor'),
										'label_title'     => __('', 'tutor'),
										'default'   => 'Incorrect username or password.',

										'desc'      => __('Login error message displayed when the user puts wrong login credentials.', 'tutor'),
									),
									array(
										'key'      => 'hide_admin_bar_for_users',
										'type'      => 'toggle_switch',
										'label'     => __('Hide Frontend Admin Bar', 'tutor'),
										'label_title' => __('', 'tutor'),
										'default'   => '0',

										'desc'      => __('Hide admin bar option allow you to hide WordPress admin bar entirely from the frontend. It will still show to administrator roles user',	'tutor'),
									),
									array(
										'key'      => 'enable_tutor_maintenance_mode',
										'type'      => 'toggle_switch',
										'label'     => __('Maintenance Mode', 'tutor'),
										'label_title' => __('', 'tutor'),
										'default'   => '0',

										'desc'      => __('Enabling the maintenance mode allows you to display a custom message on the frontend. During this time, visitors can not access the site content. But the wp-admin dashboard will remain accessible.',	'tutor'),
									),
								),
							),
						),
					),
					array(
						'label'  => __('Email', 'tutor'),
						'slug'   => 'email',
						'desc'   => __('Email Settings', 'tutor'),
						'template'   => 'basic',
						'icon'   => __('envelope', 'tutor'),
						'blocks' => array(
							array(
								'label'      => __('Course', 'tutor'),
								'slug'       => 'course',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'      => 'email_from_name',
										'type'      => 'text',
										'label'     => __('Name', 'tutor'),
										'default'   => get_option('blogname'),

										'desc'      => __('The name under which all the emails will be sent',	'tutor'),
									),
									array(
										'key'      => 'email_from_address',
										'type'      => 'text',
										'label'     => __('E-Mail Address', 'tutor'),
										'default'   => get_option('admin_email'),

										'desc'      => __('The E-Mail address from which all emails will be sent', 'tutor'),
									),
									array(
										'key'      => 'email_footer_text',
										'type'      => 'textarea',
										'label'     => __('E-Mail Footer Text', 'tutor'),
										'default'   => '',

										'desc'      => __('The text to appear in E-Mail template footer', 'tutor'),
									),
									array(
										'key'      => 'mailer_native_server_cron',
										'type'      => 'group_textarea_code',
										'label'     => __('Mailer Native Server Cron', 'tutor'),
										'label_title'     => __('', 'tutor'),
										'group_fields'   => array(
											array(
												'key'      => 'mailer_native_server_cron',
												'type'      => 'toggle_switch',
												'label'     => __('Mailer Native Server Cron', 'tutor'),
												'label_title'     => __('', 'tutor'),
												'default'   => 1,

												'desc'      => __('If you use OS native cron, then disable it.', 'tutor'),
											),
											array(
												'key'      => 'mailer_native_server',
												'type'      => 'textarea_code',
												'label'     => __('Mailer Native Server Cron', 'tutor'),
												'label_title'     => __('', 'tutor'),
												'default'   => 1,

												'desc'      => __('If you use OS native cron, then disable it.', 'tutor'),
											),
										),
										'desc'      => __('If you use OS native cron, then disable it.', 'tutor'),
									),
								),
							),
							array(
								'label'      => __('E-Mail to Students', 'tutor'),
								'slug'       => 'e_mail_to_students',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'        => 'course_enrolled',
										'type'        => 'toggle_switch_button',
										'label'       => __('Course Enrolled', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of course_enrolled'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'quiz_completed',
										'type'        => 'toggle_switch_button',
										'label'       => __('Quiz Completed', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of quiz_completed'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'completed_a_course',
										'type'        => 'toggle_switch_button',
										'label'       => __('Completed a Course', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of completed_a_course'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
								)
							),
							array(
								'label'      => __('E-Mail to Teachers', 'tutor'),
								'slug'       => 'e_mail_to_teachers',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'        => 'a_student_enrolled_in_course',
										'type'        => 'toggle_switch_button',
										'label'       => __('A Student Enrolled in Course', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of a_student_enrolled_in_course'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'a_student_completed_course',
										'type'        => 'toggle_switch_button',
										'label'       => __('A Student Completed Course', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of a_student_completed_course'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'a_student_completed_lesson',
										'type'        => 'toggle_switch_button',
										'label'       => __('A Student Completed Lesson', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of a_student_completed_lesson'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
								)
							),
							array(
								'label'      => __('E-Mail to Admin', 'tutor'),
								'slug'       => 'e_mail_to_admin',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'        => 'new_instructor_signup',
										'type'        => 'toggle_switch_button',
										'label'       => __('New Instructor Signup', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of new_instructor_signup'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'new_student_signup',
										'type'        => 'toggle_switch_button',
										'label'       => __('New Student Signup', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of new_student_signup'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'new_course_submitted_for_review',
										'type'        => 'toggle_switch_button',
										'label'       => __('New Course Submitted for Review', 'tutor'),
										'label_title' => __('', 'tutor'),
										'buttons' => array(
											'edit' => 'Edit button of new_course_submitted_for_review'
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
								)
							)
						),
					),
					array(
						'label'  => __('Gradebook', 'tutor'),
						'slug'   => 'gradebook',
						'desc'   => __('Gradebook Settings', 'tutor'),
						'template'   => 'gradebook',
						'icon'   => __('gradebook', 'tutor'),
						'blocks' => array(
							array(
								'label'      => __('', 'tutor'),
								'slug'       => 'e_mail_to_students',
								'block_type' => 'isolate',
								'fields'     => array(
									array(
										'key'        => 'use_points_instead_of_grades',
										'type'        => 'toggle_switch',
										'label'       => __('Use Points Instead of Grades', 'tutor'),
										'default'	  => 0,

										'label_title' => __('', 'tutor'),
										'desc'        => __('Enable this option to use numerical points instead of letter grades.', 'tutor'),
									),
									array(
										'key'        => 'show_highest_possible_points',
										'type'        => 'toggle_switch',
										'label'       => __('Show Highest Possible Points', 'tutor'),
										'default'	  => 0,

										'label_title' => __('', 'tutor'),
										'desc'        => __('Display the highest possible points next to a student’s score such as 3.8/4.0', 'tutor'),
									),
									array(
										'key'        => 'separator_between_scores',
										'type'        => 'text',
										'label'       => __('Separator Between Scores', 'tutor'),
										'default'	  => 0,

										'label_title' => __('', 'tutor'),
										'desc'        => __('Input the separator text or symbol to display. Example: Insert / to display 3.8/4.0 or “out of” 3.8 out of 4.', 'tutor'),
									),
									array(
										'key'        => 'grade_scale',
										'type'        => 'text',
										'label'       => __('Grade Scale', 'tutor'),
										'default'	  => 0,

										'label_title' => __('', 'tutor'),
										'desc'        => __('Insert the grade point out of which the final results will be calculated.', 'tutor'),
									),
								)
							),
						),
					),
					array(
						'label'  => __('Certificate', 'tutor'),
						'slug'   => 'certificate',
						'desc'   => __('Certificate Settings', 'tutor'),
						'template'   => 'certificate',
						'icon'   => __('certificate', 'tutor'),
						'blocks' => array(
							array(
								'label'      => __('All Certificate', 'tutor'),
								'slug'       => 'all-certificate',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'        => 'new_instructor_signup',
										'type'        => 'toggle_switch_button_thumb',
										'label'       => __('New Instructor Signup', 'tutor'),
										'label_title' => __('', 'tutor'),
										'thumbs_url' => 'certificate-thumb/cetificate-thumb-1.jpg',
										'buttons' => array(
											'edit' => 'Edit button of new_course_submitted_for_review',
											'delete' => 'Delete button of new_course_submitted_for_review',
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'new_student_signup',
										'type'        => 'toggle_switch_button_thumb',
										'label'       => __('New Student Signup', 'tutor'),
										'label_title' => __('', 'tutor'),
										'thumbs_url' => 'certificate-thumb/cetificate-thumb-2.jpg',
										'buttons' => array(
											'edit' => 'Edit button of new_course_submitted_for_review',
											'delete' => 'Delete button of new_course_submitted_for_review',
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'new_course_submitted_for_review',
										'type'        => 'toggle_switch_button_thumb',
										'label'       => __('New Course Submitted for Review', 'tutor'),
										'label_title' => __('', 'tutor'),
										'thumbs_url' => 'certificate-thumb/cetificate-thumb-3.jpg',
										'buttons' => array(
											'edit' => 'Edit button of new_course_submitted_for_review',
											'delete' => 'Delete button of new_course_submitted_for_review',
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'master_procedural_maze_dungeon_generation',
										'type'        => 'toggle_switch_button_thumb',
										'label'       => __('Master Procedural Maze & Dungeon Generation', 'tutor'),
										'label_title' => __('', 'tutor'),
										'thumbs_url' => 'certificate-thumb/cetificate-thumb-4.jpg',
										'buttons' => array(
											'edit' => 'Edit button of new_course_submitted_for_review',
											'delete' => 'Delete button of new_course_submitted_for_review',
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
								)
							),
							array(
								'label'      => __('Select Certificate Template', 'tutor'),
								'slug'       => 'select_certificate_template',
								'class'       => 'certificate-template"',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'        => 'horizontal_template',
										'type'        => 'radio_thumbs_grid',
										'label'       => __('Horizontal Template', 'tutor'),
										'label_title' => __('', 'tutor'),
										'options' => array(
											array(
												'title' => 'certificate-template-horizontal-1',
												'slug' => 'certificate-template-horizontal-1',
												'thumb_url' => 'certificate-template/certificate-horizontal-1.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-2',
												'slug' => 'certificate-template-horizontal-2',
												'thumb_url' => 'certificate-template/certificate-horizontal-2.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-3',
												'slug' => 'certificate-template-horizontal-3',
												'thumb_url' => 'certificate-template/certificate-horizontal-3.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-4',
												'slug' => 'certificate-template-horizontal-4',
												'thumb_url' => 'certificate-template/certificate-horizontal-4.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-5',
												'slug' => 'certificate-template-horizontal-5',
												'thumb_url' => 'certificate-template/certificate-horizontal-5.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-6',
												'slug' => 'certificate-template-horizontal-6',
												'thumb_url' => 'certificate-template/certificate-horizontal-6.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-7',
												'slug' => 'certificate-template-horizontal-7',
												'thumb_url' => 'certificate-template/certificate-horizontal-7.jpg',
											),
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
									array(
										'key'        => 'vertical_template',
										'type'        => 'radio_thumbs_grid',
										'label'       => __('Vertical Template', 'tutor'),
										'label_title' => __('', 'tutor'),
										'options' => array(
											array(
												'title' => 'certificate-template-horizontal-1',
												'slug' => 'certificate-template-horizontal-1',
												'thumb_url' => 'certificate-template/certificate-vertical-1.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-2',
												'slug' => 'certificate-template-horizontal-2',
												'thumb_url' => 'certificate-template/certificate-vertical-2.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-3',
												'slug' => 'certificate-template-horizontal-3',
												'thumb_url' => 'certificate-template/certificate-vertical-3.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-4',
												'slug' => 'certificate-template-horizontal-4',
												'thumb_url' => 'certificate-template/certificate-vertical-4.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-5',
												'slug' => 'certificate-template-horizontal-5',
												'thumb_url' => 'certificate-template/certificate-vertical-5.jpg',
											),
											array(
												'title' => 'certificate-template-horizontal-6',
												'slug' => 'certificate-template-horizontal-6',
												'thumb_url' => 'certificate-template/certificate-vertical-6.jpg',
											),
										),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
								)
							),

							array(
								'label'      => __('Select Certificate Template', 'tutor'),
								'slug'       => 'select_certificate_template',
								'class'       => 'certificate-settings"',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'        => 'add_instructor_info',
										'type'        => 'toggle_switch',
										'label'       => __('Add Instructor Info', 'tutor'),
										'label_title' => __('', 'tutor'),
										'desc'        => __('Enable to add course instructor’s information on all generated certificates.', 'tutor'),
									),
									array(
										'key'        => 'authorised_company_name',
										'type'        => 'text',
										'label'       => __('Authorised Company Name', 'tutor'),
										'label_title' => __('', 'tutor'),
										'desc'        => __('Add your eLearning company name below your authorized name to add credibility to the certificates.', 'tutor'),
									),
									array(
										'key'      => 'signature',
										'type'      => 'upload_half',
										'label'     => __('Signature', 'tutor'),
										'default'     => 'signature-demo.svg',

										'desc'      => __('<p>
											Size: <strong>200x40 pixels;</strong> File Support:
											<strong>jpg, .jpeg or .png.</strong>
										</p>', 'tutor'),
									),
									array(
										'key'        => 'view_certificate',
										'type'        => 'toggle_switch',
										'label'       => __('View Certificate', 'tutor'),
										'label_title' => __('', 'tutor'),
										'desc'        => __('Students must be logged in to view course', 'tutor'),
									),
								)
							),
						),

					),
				),
			),
			'tools'  => array(
				'label'    => __('Tools', 'tutor'),
				'sections' => array(
					array(
						'label'  => __('Status', 'tutor'),
						'slug'   => 'status',
						'desc'   => __('Status Settings', 'tutor'),
						'template'   => 'status',
						'icon'   => __('chart', 'tutor'),
						'blocks' => array(
							array(
								'label'      => __('WordPress environment', 'tutor'),
								'slug'       => 'wordpress_environment',
								'block_type' => 'column',
								'fieldset'     => array(
									array(
										array(
											'key'        => 'home_url',
											'type'        => 'info_row',
											'label'       => __('Home URL', 'tutor'),
											'status' => 'default',
											'default' => home_url(),

										),
									),
									array(
										array(
											'key'        => 'wordpress_version',
											'type'        => 'info_col',
											'label'       => __('WordPress version', 'tutor'),
											'status' => 'default',
											'default' => get_bloginfo('version'),

										),
										array(
											'key'        => 'wordpress_multisite',
											'type'        => 'info_col',
											'label'       => __('WordPress multisite', 'tutor'),
											'status' => is_multisite() ? 'success' : 'default',
											'default' => is_multisite() ? '✓' : '-',

										),
										array(
											'key'        => 'wordpress_debug_mode',
											'type'        => 'info_col',
											'label'       => __('WordPress debug mode', 'tutor'),
											'status' => (defined('WP_DEBUG') && true === WP_DEBUG) ? 'success' : 'default',
											'default' => (defined('WP_DEBUG') && true === WP_DEBUG) ? '✓' : '-',

										),
										array(
											'key'        => 'language',
											'type'        => 'info_col',
											'label'       => __('Language', 'tutor'),
											'status' => 'default',
											'default' => get_locale(),

										),
									),
									array(
										array(
											'key'        => 'site_url',
											'type'        => 'info_row',
											'label'       => __('Site URL', 'tutor'),
											'status' => 'default',
											'default' => site_url(),

										),
									),
									array(
										array(
											'key'        => 'tutor_version',
											'type'        => 'info_col',
											'label'       => __('Tutor version', 'tutor'),
											'status' => 'success',
											'default' => tutor()->version,

										),
										array(
											'key'        => 'wordpress_memory_limit',
											'type'        => 'info_col',
											'label'       => __('WordPress memory limit', 'tutor'),
											'status' => WP_MEMORY_LIMIT ? 'success' : 'default',
											'default' => WP_MEMORY_LIMIT ?? '-',

										),
										array(
											'key'        => 'wordpress_corn',
											'type'        => 'info_col',
											'label'       => __('WordPress corn', 'tutor'),
											'status' => !empty(_get_cron_array()) ? 'success' : 'default',
											'default' => (defined('DISABLE_WP_CRON')) ? '✓' : '-',

										),
										array(
											'key'        => 'external_object_cache',
											'type'        => 'info_col',
											'label'       => __('External object cache', 'tutor'),
											'status' => 'default',
											'default' => (defined('WP_CACHE')) ? '✓' : '-',

										),
									),
								),
							),
							array(
								'label'      => __('Server environment', 'tutor'),
								'slug'       => 'server_environment',
								'block_type' => 'column',
								'fieldset'     => array(
									array(
										array(
											'key'        => 'server_info',
											'type'        => 'info_col',
											'label'       => __('Server info', 'tutor'),
											'status' => 'default',
											'default' => ucwords($_SERVER['SERVER_SOFTWARE']),

										),
										array(
											'key'        => 'php_version',
											'type'        => 'info_col',
											'label'       => __('PHP version', 'tutor'),
											'status' => 'default',
											'default' => phpversion(),

										),
										array(
											'key'        => 'php_post_max_size',
											'type'        => 'info_col',
											'label'       => __('PHP post max size', 'tutor'),
											'status' => 'default',
											'default' => ini_get('post_max_size'),

										),
										array(
											'key'        => 'php_time_limit',
											'type'        => 'info_col',
											'label'       => __('PHP time limit', 'tutor'),
											'status' => 'default',
											'default' => ini_get('max_execution_time'),

										),
										array(
											'key'        => 'max_input_vars',
											'type'        => 'info_col',
											'label'       => __('max input vars', 'tutor'),
											'status' => 'default',
											'default' => ini_get('max_input_vars'),

										),
										array(
											'key'        => 'curl_version',
											'type'        => 'info_col',
											'label'       => __('cURL version', 'tutor'),
											'status' => 'default',
											'default' => curl_version()['host'] . ', ' . curl_version()['version'],

										),
										array(
											'key'        => 'wordpress_debug_mode',
											'type'        => 'info_col',
											'label'       => __('WordPress debug mode', 'tutor'),
											'status' => 'default',
											'default' => 'http://www.yourwebsite.com',

										),
										array(
											'key'        => 'language',
											'type'        => 'info_col',
											'label'       => __('Language', 'tutor'),
											'status' => 'default',
											'default' => 'http://www.yourwebsite.com',

										),
										array(
											'key'        => 'WordPress debug mode',
											'type'        => 'info_col',
											'label'       => __('WordPress debug mode', 'tutor'),
											'status' => 'default',
											'default' => 'http://www.yourwebsite.com',

										),
									),
									array(
										array(
											'key'        => 'site_url',
											'type'        => 'info_row',
											'label'       => __('Site URL', 'tutor'),
											'status' => 'default',
											'default' => 'http://www.yourwebsite.com',

										),
									),
									array(
										array(
											'key'        => 'tutor_version',
											'type'        => 'info_col',
											'label'       => __('Tutor version', 'tutor'),
											'status' => 'default',
											'default' => '1.7.8',

										),
										array(
											'key'        => 'wordpress_memory_limit',
											'type'        => 'info_col',
											'label'       => __('WordPress memory limit', 'tutor'),
											'status' => 'default',
											'default' => '1GB',

										),
										array(
											'key'        => 'wordpress_corn',
											'type'        => 'info_col',
											'label'       => __('WordPress corn', 'tutor'),
											'status' => 'default',
											'default' => '✓',

										),
										array(
											'key'        => 'external_object_cache',
											'type'        => 'info_col',
											'label'       => __('External object cache', 'tutor'),
											'status' => 'default',
											'default' => 'http://www.yourwebsite.com',

										),
										array(
											'key'        => 'WordPress debug mode',
											'type'        => 'info_col',
											'label'       => __('WordPress debug mode', 'tutor'),
											'status' => 'default',
											'default' => 'http://www.yourwebsite.com',

										),
									),
								),
							),
						),
					),
					array(
						'label'  => __('Import/Export', 'tutor'),
						'slug'   => 'import_export',
						'desc'   => __('Import/Export Settings', 'tutor'),
						'template'   => 'import_export',
						'icon'   => __('import-export', 'tutor'),
						'blocks' => array(),
					),
					array(
						'label'  => __('Tutor Pages', 'tutor'),
						'slug'   => 'tutor-pages',
						'desc'   => __('Tutor Pages Settings', 'tutor'),
						'template'   => 'tutor_pages',
						'icon'   => __('buddypress', 'tutor'),
						'blocks' => array(
							'block' => array(),
						),
					),
					array(
						'label'  => __('Setup Wizard', 'tutor'),
						'slug'   => 'setup_wizard',
						'desc'   => __('Setup Wizard Settings', 'tutor'),
						'template'   => 'setup_wizard',
						'icon'   => __('paid-membersip-pro', 'tutor'),
						'blocks' => array(
							'block' => array(),
						),
					),
				),
			),

		);

		return $attr;
	}



	/**
	 * Check if query string
	 *
	 * @param  mixed $dataArr
	 * @param  mixed $url_page
	 * @return void
	 */
	public function url_exists($dataArr = array(), $url_page = null) {
		$url_exist = false;
		$is_active = false;
		$j         = 0;

		foreach ($dataArr as $section) {
			$j += 1;
			$is_active = isset($url_page) && $url_page === $section['slug'] ? true : (!isset($url_page) && $j === 1 ? true : false);

			if ($is_active === true) {
				$url_exist = true;
				break;
			}
		};

		return $url_exist;
	}

	public function get_active(int $index = null, string $page = null, $slug, $url_exist) {
		$is_active = false;
		$is_active = ($index === 1 && $url_exist === false) ? true : (isset($page) && $page === $slug ? true : (!isset($page) && $index === 1 ? true : false));

		return $is_active;
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 *
	 * Generate Option Field
	 */
	public function generate_field($field = array()) {
		ob_start();
		include tutor()->path . "views/options/field-types/{$field['type']}.php";

		return ob_get_clean();
	}

	public function field_type($field = array()) {
		ob_start();
		include tutor()->path . "views/options/field-types/{$field['type']}.php";

		return ob_get_clean();
	}

	public function generate() {
		ob_start();
		include tutor()->path . 'views/options/options_generator.php';

		return ob_get_clean();
	}

	public function blocks($blocks = array()) {
		ob_start();
		include tutor()->path . "views/options/option_blocks.php";
		return ob_get_clean();
	}

	public function template($section = array()) {
		ob_start();
		include tutor()->path . "views/options/template/{$section['template']}.php";
		return ob_get_clean();
	}
}
