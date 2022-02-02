<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

use \TUTOR_ASSIGNMENTS\Assignments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
global $wpdb;
global $next_id;
global $assignment_submitted_id;
$is_submitted  = false;
$is_submitting = tutor_utils()->is_assignment_submitting( get_the_ID() );
// get the comment
$post_id            = get_the_ID();
$user_id            = get_current_user_id();
$user_data          = get_userdata( $user_id );
$assignment_comment = tutor_utils()->get_single_comment_user_post_id( $post_id, $user_id );
// $submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submitted_id );
$submitted_assignment = tutor_utils()->is_assignment_submitted( get_the_ID() );
if ( $assignment_comment != false ) {
	$submitted                                = $assignment_comment->comment_approved;
	$submitted == 'submitted' ? $is_submitted = true : '';
}

// Get the ID of this content and the corresponding course
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );

// Get total content count
$course_stats = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

function tutor_assignment_convert_seconds( $seconds ) {
	$dt1 = new DateTime( '@0' );
	$dt2 = new DateTime( "@$seconds" );
	return $dt1->diff( $dt2 )->format( '%a Days, %h Hours' );
}
$next_prev_content_id = tutor_utils()->get_course_prev_next_contents_by_id( $post_id );
$content              = get_the_content();
$s_content            = $content;
$allow_to_upload      = (int) tutor_utils()->get_assignment_option( $post_id, 'upload_files_limit' );
?>

<?php do_action( 'tutor_assignment/single/before/content' ); ?>

<div class="tutor-single-page-top-bar tutor-bs-d-flex justify-content-between">
	<div class="tutor-topbar-left-item tutor-bs-d-flex"> 
		<div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center tutor-bs-d-none tutor-bs-d-xl-flex">
			<a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
				<span class="tutor-icon-icon-light-left-line tutor-color-text-white flex-center"></span>
			</a>
		</div>
		<div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
			<span class="tutor-icon-assignment-filled tutor-color-text-white tutor-mr-5"></span>
			<span class="text-regular-caption tutor-color-design-white">
				<?php
					esc_html_e( 'Assignment: ', 'tutor' );
					the_title();
				?>
			</span>
		</div>
	</div>
	<div class="tutor-topbar-right-item tutor-bs-d-flex">
		<div class="tutor-topbar-assignment-details tutor-bs-d-flex tutor-bs-align-items-center">
			<?php
				do_action( 'tutor_course/single/enrolled/before/lead_info/progress_bar' );
			?>
			<div class="text-regular-caption tutor-color-design-white">
				<span class="tutor-progress-content tutor-color-primary-60">
					<?php _e( 'Your Progress:', 'tutor' ); ?>
				</span>
				<span class="text-bold-caption">
					<?php echo $course_stats['completed_count']; ?>
				</span> 
				<?php _e( 'of ', 'tutor' ); ?>
				<span class="text-bold-caption">
					<?php echo $course_stats['total_count']; ?>
				</span>
				(<?php echo $course_stats['completed_percent'] . '%'; ?>)
			</div>
			<?php
				do_action( 'tutor_course/single/enrolled/after/lead_info/progress_bar' );
			?>
		</div>
		<div class="tutor-topbar-cross-icon tutor-ml-15 flex-center">
			<?php $course_id = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() ); ?>
			<a href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>">
				<span class="tutor-icon-line-cross-line tutor-color-text-white flex-center"></span>
			</a>
		</div>
	</div>
</div>

<div class="tutor-mobile-top-navigation tutor-bs-d-block tutor-bs-d-sm-none tutor-my-20 tutor-mx-10">
	<div class="tutor-mobile-top-nav d-grid">
		<a href="<?php echo esc_url( get_the_permalink( isset( $previous_id ) ? $previous_id : '' ) ); ?>">
			<span class="tutor-top-nav-icon tutor-icon-previous-line design-lightgrey"></span>
		</a>
		<div class="tutor-top-nav-title tutor-text-regular-body tutor-color-text-primary">
			<?php
				the_title();
			?>
		</div>
	</div>
</div>
<div class="tutor-quiz-wrapper tutor-quiz-wrapper d-flex justify-content-center tutor-mt-100 tutor-pb-100">
	<div id="tutor-assignment-wrap" class="tutor-quiz-wrap tutor-course-assignment-details tutor-submit-assignment  tutor-assignment-result-pending">
		<div class="tutor-assignment-title tutor-text-medium-h4 tutor-color-text-primary">
			<?php the_title(); ?>
		</div>

		<?php
			$time_duration = tutor_utils()->get_assignment_option(
				get_the_ID(),
				'time_duration',
				array(
					'time'  => '',
					'value' => 0,
				)
			);

			$total_mark        = tutor_utils()->get_assignment_option( get_the_ID(), 'total_mark' );
			$pass_mark         = tutor_utils()->get_assignment_option( get_the_ID(), 'pass_mark' );
			$file_upload_limit = tutor_utils()->get_assignment_option( get_the_ID(), 'upload_file_size_limit' );

			global $post;
			$assignment_created_time = strtotime( $post->post_date_gmt );
			$time_duration_in_sec    = 0;
			if ( isset( $time_duration['value'] ) and isset( $time_duration['time'] ) ) {
				switch ( $time_duration['time'] ) {
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
			$remaining_time       = $assignment_created_time + $time_duration_in_sec;
			$now                  = time();
			$remaining            = $now - $remaining_time;

			?>
		<?php if ( ! $submitted_assignment ) { ?>
		<div class="tutor-assignment-meta-info d-flex justify-content-between tutor-mt-25 tutor-mt-sm-35 tutor-py-15 tutor-py-sm-22">
			<div class="tutor-assignment-detail-info d-flex">
				<div class="tutor-assignment-duration">
					<span class="text-regular-body tutor-color-text-hints"><?php esc_html_e( 'Duration:', 'tutor' ); ?></span>
					<span class="tutor-text-medium-body  tutor-color-text-primary">
						<?php echo esc_html( $time_duration['value'] ? $time_duration['value'] . ' ' . $time_duration['time'] : __( 'No limit', 'tutor' ) ); ?>
					</span>
				</div>
				<div class="tutor-assignmetn-deadline">
					<span class="text-regular-body tutor-color-text-hints"><?php esc_html_e( 'Deadline:', 'tutor' ); ?></span>
					<span class="tutor-text-medium-body  tutor-color-text-primary">
						<?php
						if ( $time_duration['value'] != 0 ) {
							if ( $now > $remaining_time and $is_submitted == false ) {
								esc_html_e( 'Expired', 'tutor' );
							} else {
								echo esc_html( tutor_assignment_convert_seconds( $remaining ) );
							}
						} else {
							esc_html_e( 'N\\A', 'tutor' );
						}
						?>
					</span>
				</div>
			</div>
			<div class="tutor-assignment-detail-info d-flex">
				<div class="tutor-assignment-marks">
					<span class="text-regular-body tutor-color-text-hints"><?php _e( 'Total Marks:', 'tutor' ); ?></span>
					<span class="tutor-text-medium-body  tutor-color-text-primary"><?php echo $total_mark; ?></span>
				</div>
				<div class="tutor-assignmetn-pass-mark">
					<span class="text-regular-body tutor-color-text-hints"><?php _e( 'Passing Mark:', 'tutor' ); ?></span>
					<span class="tutor-text-medium-body  tutor-color-text-primary"><?php echo $pass_mark; ?></span>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php
		/*
		*time_duration[value]==0 means no limit
		*if have unlimited time then no msg should
		*appear
		*/
		if ( $time_duration['value'] != 0 ) :
			if ( $now > $remaining_time and $is_submitted == false ) :
				?>
			<div class="quiz-flash-message tutor-mt-25 tutor-mt-sm-35">
				<div class="tutor-quiz-warning-box time-over d-flex align-items-center justify-content-between">
					<div class="flash-info d-flex align-items-center">
						<span class="tutor-icon-cross-cricle-filled tutor-color-design-danger tutor-mr-7"></span>
						<span class="text-regular-caption tutor-color-danger-100">
							<?php _e( 'You have missed the submission deadline. Please contact the instructor for more information.', 'tutor' ); ?>
						</span>
					</div>
				</div>
			</div>
				<?php
			endif;
		endif;
		?>
		<?php if ( ! $is_submitting && ! $submitted_assignment ) { ?>
		<div class="tutor-time-out-assignment-details tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
			<div class="tutor-to-assignment tutor-pt-30 tutor-pt-sm-40 has-show-more">

				<div class="tutor-to-title tutor-text-medium-h6 tutor-color-text-primary">
					<?php _e( 'Description', 'tutor' ); ?>
				</div>

				<div class="tutor-to-body tutor-text-regular-body tutor-color-text-subsued tutor-pt-12">
					<?php the_content(); ?>
				</div>

			</div>
		</div>
		<?php } ?>
		<?php
		$assignment_attachments = maybe_unserialize( get_post_meta( get_the_ID(), '_tutor_assignment_attachments', true ) );
		if ( tutor_utils()->count( $assignment_attachments ) ) {
			?>
			<div class="tutor-assignment-attachments tutor-pt-40">
				<span class="tutor-text-medium-h6 tutor-color-text-primary">
					<?php esc_html_e( 'Attachments', 'tutor' ); ?>
				</span>
				<div class="tutor-bs-container tutor-pt-15">
					<div class="tutor-bs-row tutor-bs-gy-3">
					<?php if ( is_array( $assignment_attachments ) && count( $assignment_attachments ) ) : ?>
						<?php
						foreach ( $assignment_attachments as $attachment_id ) :
							$attachment_name = get_post_meta( $attachment_id, '_wp_attached_file', true );
							$attachment_name = substr( $attachment_name, strrpos( $attachment_name, '/' ) + 1 );
							$file_size       = tutor_utils()->get_attachment_file_size( $attachment_id );
							?>
							<div class="tutor-instructor-card tutor-bs-col-sm-5 tutor-py-15 tutor-mr-10">
								<div class="tutor-icard-content">
									<div class="text-regular-body color-text-title">
									<a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" target="_blank">
										<?php echo esc_html( $attachment_name ); ?>
									</a>
									</div>
									<div class="text-regular-small">
										<?php esc_html_e( 'Size: ', 'tutor' ); ?>
										<?php echo esc_html( $file_size ? $file_size . 'KB' : '' ); ?>
									</div>
								</div>
								<div class="tutor-avatar tutor-is-xs flex-center">
									<a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" target="_blank">
										<span class="tutor-icon-download-line"></span>
									</a>
								</div>
							</div>					
						<?php endforeach; ?>
					<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}

		if ( ( $is_submitting || isset( $_GET['update-assignment'] ) ) && ( $remaining_time > $now || $time_duration['value'] == 0 ) ) {
			?>

			<div class="tutor-assignment-submission tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
				<form action="" method="post" id="tutor_assignment_submit_form" enctype="multipart/form-data">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
					<input type="hidden" value="tutor_assignment_submit" name="tutor_action" />
					<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

					<?php $allowed_upload_files = (int) tutor_utils()->get_assignment_option( get_the_ID(), 'upload_files_limit' ); ?>
					<div class="tutor-assignment-body tutor-pt-30 tutor-pt-sm-40 has-show-more">
						<div class="tutor-to-title tutor-text-medium-h6 tutor-color-text-primary">
							<?php _e( 'Assignment Submission', 'tutor' ); ?>
						</div>
						<div class="text-regular-caption tutor-color-text-subsued tutor-pt-15 tutor-pt-sm-30">
							<?php _e( 'Assignment answer form', 'tutor' ); ?>
						</div>
						<div class="tutor-assignment-text-area tutor-pt-20">
							<!-- <textarea  name="assignment_answer" class="tutor-form-control"></textarea> -->
							<?php
								$assignment_comment_id = isset( $_GET['update-assignment'] ) ? sanitize_text_field( $_GET['update-assignment'] ) : 0;
								$content               = $assignment_comment_id ? get_comment( $assignment_comment_id ) : '';
								$args                  = tutor_utils()->text_editor_config();
								$args['tinymce']       = array(
									'toolbar1' => 'formatselect,bold,italic,underline,forecolor,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,undo,redo',
								);
								$args['editor_height'] = '140';
								$editor_args           = array(
									'content' => isset( $content->comment_content ) ? $content->comment_content : '',
									'args'    => $args,
								);
								$text_editor_template  = tutor()->path . 'templates/global/tutor-text-editor.php';
								tutor_load_template_from_custom_path( $text_editor_template, $editor_args );
								?>
						</div>

						<?php if ( $allowed_upload_files ) { ?>
							<div class="tutor-assignment-attachment tutor-mt-30 tutor-py-20 tutor-px-15 tutor-py-sm-30 tutor-px-sm-30">
								<div class="text-regular-caption tutor-color-text-subsued">
									<?php _e( "Attach assignment files (Max: $allow_to_upload file)", 'tutor' ); ?>
								</div>
								<div class="tutor-attachment-files tutor-mt-12">
									<div class="tutor-assignment-upload-btn tutor-mt-10 tutor-mt-md-0">
										<form>
											<label for="tutor-assignment-file-upload">
												<input type="file" id="tutor-assignment-file-upload" name="attached_assignment_files[]" multiple>
												<a class="tutor-btn tutor-btn-primary tutor-btn-md">
													<?php _e( 'Choose file', 'tutor' ); ?>
												</a>
											</label>
										</form>
									</div>
									<div class="tutor-input-type-size">
										<p class="text-regular-small tutor-color-text-subsued">
											<?php _e( 'File Support: ', 'tutor' ); ?>
											<span class="tutor-color-text-primary">
												<?php esc_html_e( 'Any standard Image, Document, Presentation, Sheet, PDF or Text file is allowed', 'tutor' ); ?>
											</span>
											<?php// _e( ' no text on the image.', 'tutor' ); ?>
										</p>
										<p class="text-regular-small tutor-color-text-subsued tutor-mt-7">
											<?php _e( 'Total File Size: Max', 'tutor' ); ?> 
											<span class="tutor-color-text-primary">
												<?php echo $file_upload_limit; ?>
												<?php _e( 'MB', 'tutor' ); ?>
											</span>
										</p>
									</div>
								</div>
								<!-- uploaded attachment by students -->
								<div class="tutor-bs-container tutor-pt-15 tutor-update-assignment-attachments">
									<div class="tutor-bs-row tutor-bs-gy-3" id="tutor-student-assignment-edit-file-preview">
									<?php
										$submitted_attachments = get_comment_meta( $assignment_comment_id, 'uploaded_attachments' );
									if ( is_array( $submitted_attachments ) && count( $submitted_attachments ) ) :
										foreach ( $submitted_attachments as $attach ) :
											$attachments = json_decode( $attach );
											?>
											<?php foreach ( $attachments as $attachment ) : ?>
												<div class="tutor-instructor-card tutor-bs-col-sm-5 tutor-py-15 tutor-mr-15">
													<div class="tutor-icard-content">
														<div class="text-regular-body tutor-color-text-title">
															<?php echo esc_html( $attachment->name ); ?>
														</div>
														<div class="text-regular-small">Size: 230KB;</div>
													</div>
													<div class="tutor-attachment-file-close tutor-avatar tutor-is-xs flex-center">
														<a href="<?php echo esc_url( $attachment->url ); ?>" data-id="<?php echo esc_attr( $assignment_comment_id ); ?>" data-name="<?php echo esc_attr( $attachment->name ); ?>" target="_blank">
															<span class="tutor-icon-cross-filled color-design-brand"></span>
														</a>
													</div>
												</div>
											<?php endforeach; ?>
										<?php endforeach; ?>
										<?php endif; ?>
									</div>
								</div>
								<!-- uploaded attachment by students end -->
							</div>

						<?php } ?>
						<div class="tutor-assignment-submit-btn tutor-mt-60">
							<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg" id="tutor_assignment_submit_btn">
								<?php esc_html_e( 'Submit Assignment', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</form>
			</div> <!-- assignment-submission -->
			<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-30 tutor-pb-sm-45">
				<div class="tutor-pt-40 tutor-pt-sm-60 <?php echo esc_attr( strlen( $s_content ) > 500 ? 'tutor-ad-body has-show-more' : '' ); ?>" id="content-section">
					<div class="text-medium-h6 tutor-color-text-primary">
						<?php _e( 'Description', 'tutor' ); ?>
					</div>
					<div class="text-regular-body tutor-color-text-subsued tutor-pt-12" id="short-text">
						<?php
						if ( strlen( $s_content ) > 500 ) {
							echo wp_kses_post( substr_replace( $s_content, '...', 500 ) );
						} else {
							echo wp_kses_post( $s_content );
						}
						?>
						<span id="dots"></span>
					</div>
					<?php if ( strlen( $s_content ) > 500 ) : ?>
						<div class="text-regular-body tutor-color-text-subsued tutor-pt-12" id="full-text">
							<?php
								echo wp_kses_post( $s_content );
							?>
						</div>
						<div class="tutor-show-more-btn tutor-pt-12">
							<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-lg" id="showBtn">
								<span class="btn-icon tutor-icon-plus-filled tutor-color-design-brand" id="no-icon"></span>
								<span class="tutor-color-text-primary"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( isset( $next_prev_content_id->next_id ) && '' !== $next_prev_content_id->next_id ) : ?>
			<div class="tutor-assignment-footer d-flex justify-content-end tutor-pt-30 tutor-pt-sm-45">
				<a href="<?php echo esc_url( get_permalink( $next_prev_content_id->next_id ) ); ?>" class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-lg tutor-mt-md-0 tutor-mt-10">
					<?php esc_html_e( 'Skip To Next', 'tutor' ); ?>
				</a>
			</div>
			<?php endif; ?>
			<?php
		} else {

			/**
			 * If assignment submitted
			 */
			if ( $submitted_assignment ) {
				$is_reviewed_by_instructor = get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );


					$assignment_id = $submitted_assignment->comment_post_ID;
					$submit_id     = $submitted_assignment->comment_ID;

					$max_mark   = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );
					$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
					$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );
				?>
					<div class="tutor-assignment-result-table tutor-mt-30 tutor-mb-40">
						<div class="tutor-ui-table-wrapper">
							<table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts">
								<thead>
									<tr>
										<th>
										<span class="text-regular-small tutor-color-text-subsued">
											<?php _e( 'Date', 'tutor' ); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small tutor-color-text-subsued">
											<?php _e( 'Total Marks', 'tutor' ); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small tutor-color-text-subsued">
											<?php _e( 'Pass Marks', 'tutor' ); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small tutor-color-text-subsued">
											<?php _e( 'Earned Marks', 'tutor' ); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small tutor-color-text-subsued">
											<?php _e( 'Result', 'tutor' ); ?>	
										</span>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td data-th="Date" class="date">
											<div class="td-statement-info">
												<span class="text-medium-small tutor-color-text-primary">
													<?php esc_html_e( date( 'F j Y g:i a', strtotime( $submitted_assignment->comment_date ) ), 'tutor' ); ?>
												</span>
											</div>
										</td>
										<td data-th="Total Marks" class="total-marks">
											<span class="text-medium-caption tutor-color-text-primary">
												<?php esc_html_e( $max_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Pass Marks" class="pass-marks">
											<span class="text-medium-caption tutor-color-text-primary">
												<?php esc_html_e( $pass_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Earned Marks" class="earned-marks">
											<span class="text-medium-caption tutor-color-text-primary">
												<?php esc_html_e( $given_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Result" class="result">
											<?php
											if ( $is_reviewed_by_instructor ) {
												if ( $given_mark >= $pass_mark ) {
													?>
												<span class="tutor-badge-label label-success">
													<?php _e( 'Passed', 'tutor' ); ?>
												</span>
													<?php
												} else {
													?>
												<span class="tutor-badge-label label-warning">
													<?php _e( 'Failed', 'tutor' ); ?>
												</span>
													<?php
												}
											}
											?>
											<?php
											if ( ! $is_reviewed_by_instructor ) {
												?>
											<span class="tutor-badge-label label-danger">
												<?php _e( 'Pending', 'tutor' ); ?>
											</span>
											<?php } ?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div> <!-- assignment-result-table -->


				<?php
				if ( $is_reviewed_by_instructor ) {
					?>
				<div class="tutor-instructor-note tutor-my-30 tutor-py-20 tutor-px-25 tutor-py-sm-30 tutor-px-sm-35">
					<div class="tutor-in-title tutor-text-medium-h6 tutor-color-text-primary">
					<?php _e( 'Instructor Note', 'tutor' ); ?>
					</div>
					<div class="tutor-in-body tutor-text-regular-body tutor-color-text-subsued tutor-pt-10 tutor-pt-sm-18">
					<?php echo nl2br( get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true ) ); ?>
					</div>
				</div>
				<?php } ?>

				<?php
					/**
					 * If user not submitted assignment and assignment expired
					 * then show expire message
					 *
					 * @since v2.0.0
					 */
				if ( ! $is_submitted && 0 != $time_duration['value'] && ( $now > $remaining_time ) ) :
					?>
						<div class="tutor-mb-40">
						<?php
						$alert_template = tutor()->path . 'templates/global/alert.php';
						if ( file_exists( $alert_template ) ) {
							tutor_load_template_from_custom_path(
								$alert_template,
								array(
									'alert_class' => 'tutor-alert tutor-danger',
									'message'     => __( 'You have missed the submission deadline. Please contact the instructor for more information.', 'tutor_pro' ),
									'icon'        => ' tutor-icon-cross-circle-outline-filled',
								)
							);
						}
						?>
						</div>
					<?php endif; ?>

				<div class="tutor-assignment-details tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
					<div class="tutor-ar-body tutor-pt-25 tutor-pb-40 tutor-px-15 tutor-px-md-30">
						<div class="tutor-ar-header d-flex justify-content-between align-items-center">
							<div class="tutor-ar-title tutor-text-medium-h6 tutor-color-text-primary">
								<?php esc_html_e( 'Your Assignment', 'tutor' ); ?>
							</div>
							<?php
								$evaluated = Assignments::is_evaluated( $post_id );
							if ( ! $evaluated && ( $remaining_time > $now || $time_duration['value'] == 0 ) ) :
								?>
								<div class="tutor-ar-btn">
								<a href="<?php echo esc_url( add_query_arg( 'update-assignment', $submitted_assignment->comment_ID ) ); ?>" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-sm">
								<?php esc_html_e( 'Edit', 'tutor' ); ?>
								</a>
								</div>
							<?php endif; ?>
						</div>
						<div class="text-regular-body tutor-color-text-subsued tutor-pt-18">
							<?php echo nl2br( stripslashes( $submitted_assignment->comment_content ) ); ?>
						</div>
						<?php
							$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );
						if ( $attached_files ) {
							$attached_files = json_decode( $attached_files, true );

							if ( tutor_utils()->count( $attached_files ) ) {
								?>
									<div class="tutor-attachment-files submited-files d-flex tutor-mt-20 tutor-mt-sm-40">
									<?php
										$upload_dir     = wp_get_upload_dir();
										$upload_baseurl = trailingslashit( tutor_utils()->array_get( 'baseurl', $upload_dir ) );

									foreach ( $attached_files as $attached_file ) {
										?>
												<div class="tutor-instructor-card">
													<div class="tutor-icard-content">
														<div class="text-regular-body tutor-color-text-title">
													<?php echo tutor_utils()->array_get( 'name', $attached_file ); ?>
														</div>
														<div class="text-regular-small">Size: <?php echo tutor_utils()->array_get( 'size', $attached_file ); ?></div>
													</div>
													<div class="tutor-attachment-file-close tutor-avatar tutor-is-xs flex-center">
														<a href="<?php echo $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ); ?>" target="_blank">
															<span class="tutor-icon-download-line tutor-color-design-brand"></span>
														</a>
													</div>
												</div>
											<?php
									}
									?>
									</div>
									<?php
							}
						}
						?>
					</div>
				</div>

				<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-30 tutor-pb-sm-45">
					<div class="tutor-pt-40 tutor-pt-sm-60 <?php echo esc_attr( strlen( $s_content ) > 500 ? 'tutor-ad-body has-show-more' : '' ); ?>" id="content-section">
						<div class="text-medium-h6 tutor-color-text-primary">
							<?php _e( 'Description', 'tutor' ); ?>
						</div>
						<div class="text-regular-body tutor-color-text-subsued tutor-pt-12" id="short-text">
							<?php
							if ( strlen( $s_content ) > 500 ) {
								echo wp_kses_post( substr_replace( $s_content, '...', 500 ) );
							} else {
								echo wp_kses_post( $s_content );
							}
							?>
							<span id="dots"></span>
						</div>
						<?php if ( strlen( $s_content ) > 500 ) : ?>
							<div class="text-regular-body tutor-color-text-subsued tutor-pt-12" id="full-text">
								<?php
									echo wp_kses_post( $s_content );
								?>
							</div>
							<div class="tutor-show-more-btn tutor-pt-12">
								<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-lg" id="showBtn">
									<span class="btn-icon tutor-icon-plus-filled tutor-color-design-brand" id="no-icon"></span>
									<span class="tutor-color-text-primary"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
								</button>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( isset( $next_prev_content_id->next_id ) && '' !== $next_prev_content_id->next_id ) : ?>
				<div class="tutor-assignment-footer tutor-pt-30 tutor-pt-sm-45">
					<a class="tutor-btn tutor-btn-primary tutor-btn-lg" href="<?php echo esc_url( get_the_permalink( $next_prev_content_id->next_id ) ); ?>">
						<?php esc_html_e( 'Continue Lesson', 'tutor' ); ?>
					</a>
				</div>
				<?php endif; ?>
				<?php
			} else {
				?>
				<div class="tutor-assignment-footer tutor-pt-30 tutor-pt-sm-45">
					<div class="tutor-assignment-footer-btn tutor-btn-group d-flex justify-content-between">
						<form action="" method="post" id="tutor_assignment_start_form">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
						<input type="hidden" value="tutor_assignment_start_submit" name="tutor_action" />
						<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">
							<button type="submit" class="tutor-btn tutor-btn-primary 
							<?php
							if ( $time_duration['value'] != 0 ) {
								if ( $now > $remaining_time ) {
									echo 'tutor-btn-disable tutor-no-hover'; }
							}
							?>
							 tutor-btn-lg" id="tutor_assignment_start_btn" 
				<?php
				if ( $time_duration['value'] != 0 ) {
					if ( $now > $remaining_time ) {
						echo 'disabled'; }
				}
				?>
>
								<?php esc_html_e( 'Start Assignment Submit', 'tutor' ); ?>
							</button>
						</form>

						<?php if ( isset( $next_prev_content_id->next_id ) && '' !== $next_prev_content_id->next_id ) : ?>
							<a href="<?php echo esc_url( get_permalink( $next_prev_content_id->next_id ) ); ?>" class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-lg tutor-mt-md-0 tutor-mt-10">
							<?php esc_html_e( 'Skip To Next', 'tutor' ); ?>
						</a>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>

<?php do_action( 'tutor_assignment/single/after/content' ); ?>
