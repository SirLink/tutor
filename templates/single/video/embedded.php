<?php

/**
 * Display Video HTML5
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

global $previous_id;
global $next_id;
$content_id = tutor_utils()->get_post_id($course_content_id??null);
$contents = tutor_utils()->get_course_prev_next_contents_by_id($content_id);
$previous_id = $contents->previous_id;
$next_id = $contents->next_id;
$video_info = tutor_utils()->get_video_info();

do_action( 'tutor_lesson/single/before/video/embedded' );
?>
<?php if($video_info ): ?>
    <div class="course-players">
        <input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData??null)); ?>">

        <?php echo tutor_utils()->array_get('source_embedded', $video_info); ?>

        <?php if($previous_id): ?>
            <div class="tutor-lesson-prev flex-center">
                <a href="<?php echo get_the_permalink($previous_id); ?>">
                    <span class="ttr-angle-left-filled"></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if($next_id): ?>
            <div class="tutor-lesson-next flex-center">
                <a href="<?php echo get_the_permalink($next_id); ?>">
                    <span class="ttr-angle-right-filled"></span>
                </a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php do_action('tutor_lesson/single/after/video/embedded'); ?>