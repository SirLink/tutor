<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<h3><?php _e('Dashboard', 'tutor') ?></h3>

<div class="tutor-dashboard-content-inner">

	<?php
	$enrolled_course = tutor_utils()->get_enrolled_courses_by_user();
	$completed_courses = tutor_utils()->get_completed_courses_ids_by_user();
	$total_students = tutor_utils()->get_total_students_by_instructor(get_current_user_id());
	$my_courses = tutor_utils()->get_courses_by_instructor(get_current_user_id(), 'publish');
	$earning_sum = tutor_utils()->get_earning_sum();

	$enrolled_course_count = $enrolled_course ? $enrolled_course->post_count : 0;
	$completed_course_count = count($completed_courses);
    $active_course_count = $enrolled_course_count - $completed_course_count;
    $active_course_count<0 ? $active_course_count=0 : 0;
    
    $status_translations = array(
        'publish' => __('Published', 'tutor'),
        'pending' => __('Pending', 'tutor'),
        'trash' => __('Trash', 'tutor')
    );

    $icon_base = tutor()->url . 'assets/images/images-v2/icons/';
	?>

    <div class="row tutor-dashboard-cards-container">
        <div class="col-12 col-sm-6 col-md-6 col-lg-4">
            <p>
                <span class="tutor-round-icon"><img src="<?php echo $icon_base; ?>book-open.svg"/></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($enrolled_course_count); ?></span>
                <span><?php _e('Enrolled Courses', 'tutor'); ?></span>
            </p>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4">
            <p>
                <span class="tutor-round-icon"><img src="<?php echo $icon_base; ?>graduation-cap.svg"/></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($active_course_count); ?></span>
                <span><?php _e('Active Courses', 'tutor'); ?></span>
            </p>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-4">
            <p>
                <span class="tutor-round-icon"><img src="<?php echo $icon_base; ?>award.svg"/></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($completed_course_count); ?></span>
                <span><?php _e('Completed Courses', 'tutor'); ?></span>
            </p>
        </div>

		<?php
		if(current_user_can(tutor()->instructor_role)) :
			?>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <p>
                    <span class="tutor-round-icon"><img src="<?php echo $icon_base; ?>graduated-user.svg"/></span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html($total_students); ?></span>
                    <span><?php _e('Total Students', 'tutor'); ?></span>
                </p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <p>
                    <span class="tutor-round-icon"><img src="<?php echo $icon_base; ?>open-box.svg"/></span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html(count($my_courses)); ?></span>
                    <span><?php _e('Total Courses', 'tutor'); ?></span>
                </p>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <p>
                    <span class="tutor-round-icon"><img src="<?php echo $icon_base; ?>coins.svg"/></span>
                    <span class="tutor-dashboard-info-val"><?php echo tutor_utils()->tutor_price($earning_sum->instructor_amount); ?></span>
                    <span><?php _e('Total Earnings', 'tutor'); ?></span>
                </p>
            </div>
		<?php
		endif;
		?>
    </div>
</div>

<?php
$instructor_course = tutor_utils()->get_courses_for_instructors(get_current_user_id());

if(count($instructor_course)) {
    $course_badges = array(
        'publish' => 'success',
        'pending' => 'warning',
        'trash' => 'danger'
    );

    ?>
        <h3 class="popular-courses-heading-dashboard">
            <?php _e('Popular Courses', 'tutor'); ?>
            <a style="float:right" class="tutor-view-all-course" href="<?php echo tutor_utils()->tutor_dashboard_url('my-courses'); ?>">
                <?php _e('View All', 'tutor'); ?>
            </a>
        </h3>
        <div class="tutor-dashboard-content-inner">
            <table class="tutor-dashboard-table tutor-popular-course-table">
                <thead>
                    <tr>
                        <th><?php _e('Course Name', 'tutor'); ?></th>
                        <th><?php _e('Enrolled', 'tutor'); ?></th>
                        <th><?php _e('Status', 'tutor'); ?></th>
                        <th><?php _e('Rating', 'tutor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($instructor_course as $course){
                    $enrolled = tutor_utils()->count_enrolled_users_by_course($course->ID);
                    $course_status = isset($status_translations[$course->post_status]) ? $status_translations[$course->post_status] : __($course->post_status, 'tutor');
                    $course_rating = tutor_utils()->get_course_rating($course->ID);
                    $course_badge =  isset($course_badges[$course->post_status]) ? $course_badges[$course->post_status] : 'dark';
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo get_the_permalink($course->ID); ?>" target="_blank">
                                <?php echo $course->post_title; ?>
                            </a>
                        </td>
                        <td>
                            <?php echo $enrolled; ?>
                        </td>
                        <td>
                            <small class="tutor-badge tutor-bg-<?php echo $course_badge; ?> tutor-m-5"> 
                                <?php echo $course_status; ?>
                            </small>
                        </td>
                        <td>
                            <?php tutor_utils()->star_rating_generator($course_rating->rating_avg); ?> <span><?php echo $course_rating->rating_avg; ?></span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php 
    } 
?>