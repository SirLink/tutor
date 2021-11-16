<?php
/**
 * Display the content
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

global $post;
global $previous_id;
global $next_id;
$course_content_id = '';
$completedCount = '';
$totalContents = '';
$post_id = get_the_ID();
$_is_preview = get_post_meta($post_id, '_is_preview', true);
$content_id = tutor_utils()->get_post_id($course_content_id);
$contents = tutor_utils()->get_course_prev_next_contents_by_id($content_id);
$previous_id = $contents->previous_id;
$next_id = $contents->next_id;
$completed_count = tutor_utils()->get_course_completed_percent();
$complete = tutor_utils()->get_course_completed_percent($completedCount);
$total = tutor_utils()->get_course_completed_percent($totalContents);
$jsonData = array();
$jsonData['post_id'] = get_the_ID();
$jsonData['best_watch_time'] = 0;
$jsonData['autoload_next_course_content'] = (bool) get_tutor_option('autoload_next_course_content');

$best_watch_time = tutor_utils()->get_lesson_reading_info(get_the_ID(), 0, 'video_best_watched_time');
if ($best_watch_time > 0){
	$jsonData['best_watch_time'] = $best_watch_time;
}

?>

<?php do_action('tutor_lesson/single/before/content'); ?>
<?php if(!$_is_preview){ ?>
<div class="tutor-single-page-top-bar d-flex justify-content-between">
    <div class="tutor-topbar-left-item d-flex"> 
        <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center">
            <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
                <span class="ttr-icon-light-left-line color-text-white flex-center"></span>
            </a>
        </div>
        <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
            <span class="ttr-youtube-brand color-text-white tutor-mr-5"></span>
            <span class="text-regular-caption color-design-white">
                <?php 
                    esc_html_e( 'Lesson: ', 'tutor' );
                    the_title();
                ?>
            </span>
        </div>
    </div>
    <div class="tutor-topbar-right-item d-flex align-items-center">
        <div class="tutor-topbar-assignment-details d-flex align-items-center">
            <?php
                do_action('tutor_course/single/enrolled/before/lead_info/progress_bar');
            ?>
            <div class="text-regular-caption color-design-white">
                <span class="tutor-progress-content color-primary-60">
                    <?php _e('Your Progress:', 'tutor'); ?>
                </span>
                <span class="text-bold-caption">
                    <?php echo $complete; ?>
                </span> 
                <?php _e('of ', 'tutor'); ?>
                <span class="text-bold-caption">
                    <?php echo $total; ?>
                </span>
                (<?php echo $completed_count.'%'; ?>)
            </div>
            <?php
                do_action('tutor_course/single/enrolled/after/lead_info/progress_bar');
            ?>
            <div class="tutor-topbar-complete-btn tutor-ml-30 tutor-mr-15">
                <?php tutor_lesson_mark_complete_html(); ?>
            </div>
        </div>
        <div class="tutor-topbar-cross-icon flex-center">
            <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
            <a href="<?php echo get_the_permalink($course_id); ?>">
                <span class="ttr-line-cross-line color-text-white flex-center"></span>
            </a>
        </div>
    </div>

</div>
<?php } else { ?>
<div class="tutor-single-page-top-bar d-flex justify-content-between">
    <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center">
        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
            <span class="ttr-icon-light-left-line color-text-white flex-center"></span>
        </a>
    </div>
    <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
        <span class="ttr-youtube-brand color-text-white tutor-mr-5"></span>
		<span class="text-regular-caption color-design-white">
			<?php 
				esc_html_e( 'Lesson: ', 'tutor' );
				the_title();
			?>
		</span>
    </div>

    <div class="tutor-topbar-cross-icon flex-center">
        <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
        <a href="<?php echo get_the_permalink($course_id); ?>">
            <span class="ttr-line-cross-line color-text-white flex-center"></span>
        </a>
    </div>

</div>
<?php } ?>

<div class="course-players flex-center">
    <input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData)); ?>">
	<?php tutor_lesson_video(); ?>
    <div class="tutor-lesson-prev flex-center">
        <a href="<?php echo get_the_permalink($previous_id); ?>">
            <span class="ttr-angle-left-filled"></span>
        </a>
    </div>
    <div class="tutor-lesson-next flex-center">
        <a href="<?php echo get_the_permalink($next_id); ?>">
            <span class="ttr-angle-right-filled"></span>
        </a>
    </div>
</div>

<div class="tutor-course-spotlight-wrapper">
    <div class="tutor-default-tab tutor-course-details-tab">
        <div class="tab-header tutor-bs-d-flex justify-content-center">
            <div class="tab-header-item flex-center is-active" data-tutor-tab-target="tutor-course-details-tab-1">
                <span class="ttr-document-alt-filled"></span>
                <span><?php _e('Overview','tutor'); ?></span>
            </div>
            <div class="tab-header-item flex-center" data-tutor-tab-target="tutor-course-details-tab-2">
                <span class="ttr-attach-filled"></span>
                <span><?php _e('Exercise Files','tutor'); ?></span>
            </div>
            <div class="tab-header-item flex-center" data-tutor-tab-target="tutor-course-details-tab-3">
                <span class="ttr-comment-filled"></span>
                <span><?php _e('Comments','tutor'); ?></span>
            </div>
        </div>
        <div class="tab-body">
            <div class="tab-body-item is-active" id="tutor-course-details-tab-1">
                <div class="text-medium-h6 color-text-primary"><?php _e('About Lesson','tutor'); ?></div>
                <div class="text-regular-body color-text-subsued tutor-mt-12">
                    <?php the_content(); ?>
                </div>
            </div>
            <div class="tab-body-item" id="tutor-course-details-tab-2">
                <div class="text-medium-h6 color-text-primary"><?php _e('Exercise Files','tutor'); ?></div>
                <?php get_tutor_posts_attachments(); ?>
            </div>
            <div class="tab-body-item" id="tutor-course-details-tab-3">
                <div class="text-medium-h6 color-text-primary"><?php _e('Join the conversation', 'tutor'); ?></div>
                <?php
                    comments_template();
                ?>
            </div>
        </div>
    </div>
</div>

<?php do_action('tutor_lesson/single/after/content'); ?>