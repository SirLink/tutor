<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if (!defined('ABSPATH'))
	exit;
global $post;
global $wpdb;
global $next_id;
global $assignment_submitted_id;
$is_submitted = false;
$is_submitting = tutor_utils()->is_assignment_submitting(get_the_ID());
//get the comment
$post_id = get_the_ID();
$user_id = get_current_user_id();
$assignment_comment = tutor_utils()->get_single_comment_user_post_id($post_id, $user_id);
$submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submitted_id );
if ($assignment_comment != false) {
	$submitted = $assignment_comment->comment_approved;
	$submitted == 'submitted' ? $is_submitted = true : '';
}
?>

<?php do_action('tutor_assignment/single/before/content'); ?>

<div class="tutor-single-page-top-bar d-flex justify-content-between">
    <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center">
        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
            <span class="ttr-icon-light-left-line color-text-white flex-center"></span>
        </a>
    </div>
    <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
        <?php

        if ($post->post_type === 'tutor_quiz') {
            echo wp_kses_post( '<span class="ttr-quiz-filled color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
            esc_html_e( 'Quiz: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } elseif ($post->post_type === 'tutor_assignments'){
            echo wp_kses_post( '<span class="ttr-assignment-filled color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
            esc_html_e( 'Assignment: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } elseif ($post->post_type === 'tutor_zoom_meeting'){
            echo wp_kses_post( '<span class="ttr-zoom-brand color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
            esc_html_e( 'Zoom Meeting: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        } else{
            echo wp_kses_post( '<span class="ttr-youtube-brand color-text-white tutor-mr-5"></span>' );
            echo wp_kses_post( '<span class="text-regular-caption color-design-white">' );
            esc_html_e( 'Lesson: ', 'tutor' );
            the_title(); 
            echo wp_kses_post( '</span>' );
        }

        ?>
    </div>
	
    <div class="tutor-topbar-cross-icon flex-center">
        <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
        <a href="<?php echo get_the_permalink($course_id); ?>">
            <span class="ttr-line-cross-line color-text-white flex-center"></span>
        </a>
    </div>

</div>

<div class="tutor-quiz-wrapper tutor-quiz-wrapper d-flex justify-content-center tutor-mt-100 tutor-pb-100">
	<div id="tutor-assignment-wrap" class="tutor-quiz-wrap tutor-course-assignment-details tutor-submit-assignment  tutor-assignment-result-pending">	
		<div class="tutor-assignment-title text-medium-h4 color-text-primary">
			<?php the_title(); ?>
		</div>

		<?php
			$time_duration = tutor_utils()->get_assignment_option(get_the_ID(), 'time_duration', array('time'=>'', 'value'=>0));

			$total_mark = tutor_utils()->get_assignment_option(get_the_ID(), 'total_mark');
			$pass_mark = tutor_utils()->get_assignment_option(get_the_ID(), 'pass_mark');
			$file_upload_limit = tutor_utils()->get_assignment_option(get_the_ID(), 'upload_file_size_limit');

			global $post;
			$assignment_created_time = strtotime($post->post_date_gmt);
			$time_duration_in_sec = 0;
			if (isset($time_duration['value']) and isset($time_duration['time'])) {
				switch ($time_duration['time']) {
					case 'hours':
						$time_duration_in_sec = 3600;
						break;
					case 'days':
						$time_duration_in_sec = 86400;
						break;
					case 'weeks':
						$time_duration_in_sec = 7 * 86400;
						break;
					default:
						$time_duration_in_sec = 0;
						break;
				}
			}
			$time_duration_in_sec = $time_duration_in_sec * $time_duration['value'];
			$remaining_time = $assignment_created_time + $time_duration_in_sec;
			$now = time();

		?>

		<div class="tutor-assignment-meta-info d-flex justify-content-between tutor-mt-25 tutor-mt-sm-35 tutor-py-15 tutor-py-sm-22">
			<div class="tutor-assignment-detail-info d-flex">
				<div class="tutor-assignment-duration">
					<span class="text-regular-body color-text-hints"><?php _e('Duration:', 'tutor'); ?></span>
					<span class="text-medium-body color-text-primary">
						<?php echo $time_duration["value"] ? $time_duration["value"] . ' ' . $time_duration["time"] : __('No limit', 'tutor'); ?>
					</span>
				</div>
				<div class="tutor-assignmetn-deadline">
					<span class="text-regular-body color-text-hints"><?php _e('Deadline:', 'tutor'); ?></span>
					<span class="text-medium-body color-text-primary">
						<?php
							if ($time_duration['value'] != 0) {
								if ($now > $remaining_time and $is_submitted == false) { ?>
						<?php _e('Expired', 'tutor'); ?>
						<?php
								} else {
									echo $remaining_time;
								}
							}
						?>
					</span>
				</div>
			</div>
			<div class="tutor-assignment-detail-info d-flex">
				<div class="tutor-assignment-marks">
					<span class="text-regular-body color-text-hints"><?php _e('Total Marks:', 'tutor'); ?></span>
					<span class="text-medium-body color-text-primary"><?php echo $total_mark; ?></span>
				</div>
				<div class="tutor-assignmetn-pass-mark">
					<span class="text-regular-body color-text-hints"><?php _e('Passing Mark:', 'tutor'); ?></span>
					<span class="text-medium-body color-text-primary"><?php echo $pass_mark; ?></span>
				</div>
			</div>
		</div>
		<?php
		/*
		*time_duration[value]==0 means no limit
		*if have unlimited time then no msg should
		*appear 
		*/
		if ($time_duration['value'] != 0) :
			if ($now > $remaining_time and $is_submitted == false) : ?>
			<div class="quiz-flash-message tutor-mt-25 tutor-mt-sm-35">
				<div class="tutor-quiz-warning-box time-over d-flex align-items-center justify-content-between">
					<div class="flash-info d-flex align-items-center">
						<span class="ttr-cross-cricle-filled color-design-danger tutor-mr-7"></span>
						<span class="text-regular-caption color-danger-100">
							<?php _e('You have missed the submission deadline. Please contact the instructor for more information.', 'tutor'); ?>
						</span>
					</div>
				</div>
			</div>
		<?php
			endif;
		endif;
		?>
		<?php if (!$is_submitting){ ?>
		<div class="tutor-time-out-assignment-details tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
			<div class="tutor-to-assignment tutor-pt-30 tutor-pt-sm-40 has-show-more">

				<div class="tutor-to-title text-medium-h6 color-text-primary">
					<?php _e('Description', 'tutor'); ?>
				</div>

				<div class="tutor-to-body text-regular-body color-text-subsued tutor-pt-12">
					<?php the_content(); ?>
				</div>

			</div>
		</div>
		<?php } ?>
		<?php
		$assignment_attachments = maybe_unserialize(get_post_meta(get_the_ID(), '_tutor_assignment_attachments', true));
		if (tutor_utils()->count($assignment_attachments)) {
		?>
			<div class="tutor-assignment-attachments">
				<h2><?php _e('Attachments', 'tutor'); ?></h2>
				<?php
				foreach ($assignment_attachments as $attachment_id) {
					if ($attachment_id) {

						$attachment_name =  get_post_meta($attachment_id, '_wp_attached_file', true);
						$attachment_name = substr($attachment_name, strrpos($attachment_name, '/') + 1);
				?>
						<p class="attachment-file-name">
							<a href="<?php echo wp_get_attachment_url($attachment_id); ?>" target="_blank">
								<i class="tutor-icon-attach"></i> <?php echo $attachment_name; ?>
							</a>
						</p>
				<?php
					}
				}
				?>
			</div>
		<?php
		}

		if ($is_submitting and ($remaining_time > $now or $time_duration['value'] == 0)) { ?>

			<div class="tutor-assignment-submission tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
				<form action="" method="post" id="tutor_assignment_submit_form" enctype="multipart/form-data">
					<?php wp_nonce_field(tutor()->nonce_action, tutor()->nonce); ?>
					<input type="hidden" value="tutor_assignment_submit" name="tutor_action" />
					<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

					<?php $allowd_upload_files = (int) tutor_utils()->get_assignment_option(get_the_ID(), 'upload_files_limit'); ?>
					<div class="tutor-as-body tutor-pt-30 tutor-pt-sm-40 has-show-more">
						<div class="tutor-to-title text-medium-h6 color-text-primary">
							<?php _e('Assignment Submission', 'tutor'); ?>
						</div>
						<div class="text-regular-caption color-text-subsued tutor-pt-15 tutor-pt-sm-30">
						<?php _e('Assignment answer form', 'tutor'); ?>
						</div>
						<div class="tutor-as-text-area tutor-pt-20">
							<textarea  name="assignment_answer" class="tutor-form-control"></textarea>
						</div>

						<?php if ($allowd_upload_files) { ?>
							<div class="tutor-as-attachment tutor-mt-30 tutor-py-20 tutor-px-15 tutor-py-sm-30 tutor-px-sm-30">
								<div class="text-regular-caption color-text-subsued">
									<?php _e('Attach assignment files', 'tutor'); ?>
								</div>

								<?php
									for ($item = 1; $item <= $allowd_upload_files; $item++) {
								?>
								<div class="tutor-attachment-files d-flex tutor-mt-12">
									<div class="tutor-browse-input tutor-input-group tutor-form-control-sm">
										<input type="text" class="tutor-form-control" placeholder="Browse your folder" />
									</div>
									<div class="tutor-as-upload-btn tutor-mt-10 tutor-mt-md-0">
									<button class="tutor-btn tutor-btn-primary tutor-btn-md tutor-ml-md-15 tutor-ml-0"><?php _e('Upload file', 'tutor'); ?></button>
									</div>
								</div>
								<?php } ?>

								<div class="tutor-input-type-size text-regular-small color-text-subsued tutor-mt-12">
								<?php _e('File Support:', 'tutor'); ?> <span class="color-text-primary">jpg, .jpeg,. gif, or .png.</span> no text on the image.
								</div>
								<div class="tutor-input-type-size text-regular-small color-text-subsued tutor-mt-12">
								<?php _e('Total File Size: Max', 'tutor'); ?> <span class="color-text-primary"><?php echo $file_upload_limit; ?>MB</span>
								</div>
								<div class="tutor-input-files d-flex tutor-mt-20 tutor-mt-sm-30">
									<div class="tutor-instructor-card">
										<div class="tutor-icard-content">
											<div class="text-regular-body color-text-title">
												My assignment.zip
											</div>
											<div class="text-regular-small">Size: 15.56 KB</div>
										</div>
										<div class="tutor-avatar tutor-is-xs flex-center">
											<span class="ttr-cross-filled color-design-brand"></span>
										</div>
									</div>
									<div class="tutor-instructor-card">
										<div class="tutor-icard-content">
											<div class="text-regular-body color-text-title">
												My assignment 2.zip
											</div>
											<div class="text-regular-small">Size: 15.56 KB</div>
										</div>
										<div class="tutor-avatar tutor-is-xs flex-center">
											<span class="ttr-cross-filled color-design-brand"></span>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
						<div class="tutor-as-submit-btn tutor-mt-60">
							<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg" id="tutor_assignment_submit_btn">Submit Assignment</button>
						</div>
					</div>
				</form>
			</div> <!-- assignment-submission -->
			<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-30 tutor-pb-sm-45">
				<div class="tutor-ad-body tutor-pt-40 tutor-pt-sm-60 has-show-more">
					<div class="text-medium-h6 color-text-primary">
						<?php _e('Description', 'tutor'); ?>
					</div>
					<div class="text-regular-body color-text-subsued tutor-pt-12">
						<?php the_content(); ?>
					</div>
					<div class="tutor-show-more-btn tutor-pt-12">
						<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-lg">
							<span class="btn-icon ttr-plus-filled color-design-brand"></span>
							<span class="color-text-primary"><?php _e('Show More', 'tutot'); ?></span>
						</button>
					</div>
				</div>
			</div>

			<div class="tutor-assignment-footer d-flex justify-content-end tutor-pt-30 tutor-pt-sm-45">
				<button class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-lg tutor-mt-md-0 tutor-mt-10">
					<?php _e('Sorry I don’t Understand', 'tutot'); ?>
				</button>
			</div>

			<?php
		} else {

			$submitted_assignment = tutor_utils()->is_assignment_submitted(get_the_ID());
			if ($submitted_assignment) {
				$is_reviewed_by_instructor = get_comment_meta($submitted_assignment->comment_ID, 'evaluate_time', true);

				if ($is_reviewed_by_instructor) {
					$assignment_id = $submitted_assignment->comment_post_ID;
					$submit_id = $submitted_assignment->comment_ID;

					$max_mark = tutor_utils()->get_assignment_option($submitted_assignment->comment_post_ID, 'total_mark');
					$pass_mark = tutor_utils()->get_assignment_option($submitted_assignment->comment_post_ID, 'pass_mark');
					$given_mark = get_comment_meta($submitted_assignment->comment_ID, 'assignment_mark', true);
			?>

					<?php ob_start(); ?>
					<div class="tutor-assignment-title text-medium-h4 color-text-primary">
						<?php the_title(); ?>
					</div>
					<div class="tutor-assignment-result-table tutor-mt-30 tutor-mb-40">
						<div class="tutor-ui-table-wrapper">
							<table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts">
								<thead>
									<tr>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Date', 'tutor'); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Total Marks', 'tutor'); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Pass Marks', 'tutor'); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Earned Marks', 'tutor'); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Result', 'tutor'); ?>	
										</span>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td data-th="Date" class="date">
											<div class="td-statement-info">
												<span class="text-medium-small color-text-primary">
													<?php esc_html_e( date('F j Y g:i a', strtotime( $submitted_assignment->comment_date ) ), 'tutor' ); ?>
												</span>
											</div>
										</td>
										<td data-th="Total Marks" class="total-marks">
											<span class="text-medium-caption color-text-primary">
												<?php esc_html_e( $max_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Pass Marks" class="pass-marks">
											<span class="text-medium-caption color-text-primary">
												<?php esc_html_e( $pass_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Earned Marks" class="earned-marks">
											<span class="text-medium-caption color-text-primary">
												<?php esc_html_e( $given_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Result" class="result">
											<?php if ($given_mark >= $pass_mark) {
											?>
												<span class="tutor-badge-label label-warning">
													<?php _e('Passed', 'tutor'); ?>
												</span>
											<?php
											} else {
											?>
												<span class="tutor-badge-label label-warning">
													<?php _e('Failed', 'tutor'); ?>
												</span>
											<?php
											} ?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div> <!-- assignment-result-table -->

					<?php echo apply_filters('tutor_assignment/single/results/after', ob_get_clean(), $submit_id, $assignment_id); ?>

				<?php } ?>

				<?php 
					if ($is_reviewed_by_instructor) {
				?>
				<div class="tutor-instructor-note tutor-my-30 tutor-py-20 tutor-px-25 tutor-py-sm-30 tutor-px-sm-35">
					<div class="tutor-in-title text-medium-h6 color-text-primary">
						<?php _e('Instructor Note', 'tutor'); ?>
					</div>
					<div class="tutor-in-body text-regular-body color-text-subsued tutor-pt-10 tutor-pt-sm-18">
						<?php echo nl2br(get_comment_meta($submitted_assignment->comment_ID, 'instructor_note', true)) ?>
					</div>
				</div>
				<?php } ?>

				<div class="tutor-assignment-details tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
					<div class="tutor-ar-body tutor-pt-25 tutor-pb-40 tutor-px-15 tutor-px-md-30">
						<div class="tutor-ar-header d-flex justify-content-between align-items-center">
							<div class="tutor-ar-title text-medium-h6 color-text-primary">
								<?php _e('Your Assigment', 'tutor'); ?>
							</div>
							<div class="tutor-ar-btn">
							<button class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-sm">Edit</button>
							</div>
						</div>
						<div class="text-regular-body color-text-subsued tutor-pt-18">
							<?php echo nl2br(stripslashes($submitted_assignment->comment_content)); ?>
						</div>
						<?php
							$attached_files = get_comment_meta($submitted_assignment->comment_ID, 'uploaded_attachments', true);
							if ($attached_files) {
								$attached_files = json_decode($attached_files, true);
		
								if (tutor_utils()->count($attached_files)) {
						?>
						<div class="tutor-input-files tutor-mt-20 tutor-mt-sm-40">
							<?php
								$upload_dir = wp_get_upload_dir();
								$upload_baseurl = trailingslashit(tutor_utils()->array_get('baseurl', $upload_dir));
								foreach ($attached_files as $attached_file) {
			
							?>
							<div class="tutor-instructor-card">
								<div class="tutor-icard-content">
									<div class="text-regular-body color-text-title">
										<?php echo tutor_utils()->array_get('name', $attached_file); ?>
									</div>
									<div class="text-regular-small">Size: 15.56 KB</div>
								</div>
								<div class="tutor-avatar tutor-is-xs flex-center">
									<span class="ttr-cross-filled color-design-brand"></span>

									<?php
										if ($is_reviewed_by_instructor) {
									?>
									<a href="<?php echo $upload_baseurl . tutor_utils()->array_get('uploaded_path', $attached_file) ?>" target="_blank">
										<span class="ttr-download-line color-design-brand"></span>
									</a>
									<?php } ?>
								</div>
							</div>
							<?php }  ?>
						</div>
						<?php } } ?>
					</div>
				</div>

				<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-30 tutor-pb-sm-45">
					<div class="tutor-ad-body tutor-pt-40 tutor-pt-sm-60 has-show-more">
						<div class="text-medium-h6 color-text-primary">
							<?php _e('Description', 'tutor'); ?>
						</div>
						<div class="text-regular-body color-text-subsued tutor-pt-12">
							<?php the_content(); ?>
						</div>
						<div class="tutor-show-more-btn tutor-pt-12">
							<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-lg">
								<span class="btn-icon ttr-plus-filled color-design-brand"></span>
								<span class="color-text-primary"><?php _e('Show More', 'tutot'); ?></span>
							</button>
						</div>
					</div>
				</div>

				<div class="tutor-assignment-footer tutor-pt-30 tutor-pt-sm-45">
					<a class="tutor-btn tutor-btn-primary tutor-btn-lg" href="<?php echo get_the_permalink($next_id); ?>">
						<?php _e( 'Continue Lesson', 'tutor' ); ?>
					</a>
				</div>

			<?php
			} else { ?>
				<div class="tutor-assignment-footer tutor-pt-30 tutor-pt-sm-45">
					<form action="" method="post" id="tutor_assignment_start_form">
						<?php wp_nonce_field(tutor()->nonce_action, tutor()->nonce); ?>
						<input type="hidden" value="tutor_assignment_start_submit" name="tutor_action" />
						<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">
						<div class="tutor-assignment-footer-btn tutor-btn-group d-flex justify-content-between">
							<button type="submit" class="tutor-btn tutor-btn-primary <?php if ($time_duration['value'] != 0) { if ($now > $remaining_time) {echo "tutor-btn-disable tutor-no-hover"; } } ?> tutor-btn-lg" id="tutor_assignment_start_btn" <?php if ($time_duration['value'] != 0) { if ($now > $remaining_time) {echo "disabled"; } } ?>>
								<?php _e('Start Assignment Submit', 'tutor'); ?>
							</button>
							<button class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-lg tutor-mt-md-0 tutor-mt-10">
								<?php _e('Sorry I don’t Understand', 'tutor'); ?>
							</button>
						</div>
					</form>
                </div>
		<?php
			}
		}
		?>

		<?php tutor_next_previous_pagination(); ?>
	</div>
</div>

<?php do_action('tutor_assignment/single/after/content'); ?>