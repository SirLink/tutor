<?php
/**
 * Template for displaying instructors earnings
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wpdb;

$user_id = get_current_user_id();

/**
 * Getting the Last Week
 */

$previous_week = strtotime( '-1 week +1 day' );
$start_date    = strtotime( 'last sunday midnight', $previous_week );
$end_date      = strtotime( 'next saturday', $start_date );
$start_date    = date( 'Y-m-d 00:00:00', $start_date );
$end_date      = date( 'Y-m-d 23:59:59', $end_date );

$stats = tutils()->get_earning_chart( $user_id, $start_date, $end_date );
extract( $stats );

if ( ! $earning_sum ) {
	echo _esc_html( '<p>' . __( 'No Earning info available', 'tutor' ) . '</p>' );
	return;
}
?>

	<div class="tutor-dashboard-earning-info-cards">
		<div class="tutor-dashboard-info-card" title="<?php _e( 'All time', 'tutor' ); ?>">
			<p>
				<span> <?php _e( 'My Earning', 'tutor' ); ?> </span>
				<span class="tutor-dashboard-info-val"><?php echo _esc_html( tutor_utils()->tutor_price( $earning_sum->instructor_amount ) ); ?></span>
			</p>
		</div>
		<div class="tutor-dashboard-info-card" title="<?php _e( 'Based on course price', 'tutor' ); ?>">
			<p>
				<span> <?php _e( 'All time sales', 'tutor' ); ?> </span>
				<span class="tutor-dashboard-info-val"><?php echo _esc_html( tutor_utils()->tutor_price( $earning_sum->course_price_total ) ); ?></span>
			</p>
		</div>
		<div class="tutor-dashboard-info-card">
			<p>
				<span> <?php _e( 'Deducted Commissions', 'tutor' ); ?> </span>
				<span class="tutor-dashboard-info-val"><?php echo _esc_html( tutor_utils()->tutor_price( $earning_sum->admin_amount ) ); ?></span>
			</p>
		</div>


		<?php if ( $earning_sum->deduct_fees_amount > 0 ) { ?>
			<div class="tutor-dashboard-info-card" title="<?php _e( 'Deducted Fees', 'tutor' ); ?>">
				<p>
					<span> <?php _e( 'Deducted Fees', 'tutor' ); ?> </span>
					<span class="tutor-dashboard-info-val"><?php echo _esc_html( tutor_utils()->tutor_price( $earning_sum->deduct_fees_amount ) ); ?></span>
				</p>
			</div>
		<?php } ?>
	</div>






<div class="tutor-dashboard-item-group">
	<h4><?php echo sprintf( __( 'Showing Result from %1$s to %2$s', 'tutor' ), $begin->format( 'd F, Y' ), $end->format( 'd F, Y' ) ); ?></h4>
	<?php
	tutor_load_template( 'dashboard.earning.chart-body', compact( 'chartData', 'statements' ) );
	?>
</div>

<div class="tutor-dashboard-item-group">
	<h4><?php _e( 'Sales statements for this period', 'tutor' ); ?></h4>
	<?php tutor_load_template( 'dashboard.earning.statement', compact( 'chartData', 'statements' ) ); ?>
</div>
