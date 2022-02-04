<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


$product_id = tutor_utils()->get_course_product_id();
$product = wc_get_product( $product_id );
if ($product) {
    if(tutor_utils()->is_course_added_to_cart($product_id, true)){
        ?>
            <a href="<?php echo wc_get_cart_url(); ?>" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-lg tutor-btn-full">
                <?php _e('View Cart', 'tutor'); ?>
            </a>
        <?php
    } else {
        $sale_price = $product->get_sale_price();
        $regular_price = $product->get_regular_price();
        $
        ?>
        <div class="tutor-course-sidebar-card-pricing tutor-bs-d-flex align-items-end tutor-bs-justify-content-between">
            <div>
                <span className="tutor-text-bold-h4 tutor-color-text-primary">
                    <?php echo  . ($sale_price ? $sale_price : $regular_price); ?>
                </span>
                <del className="tutor-text-regular-caption tutor-color-text-hints tutor-ml-7">
                    <?php echo  get_woocommerce_currency_symbol() . ; ?>
                </del>
            </div>
        </div>
        <form action="<?php echo esc_url( apply_filters( 'tutor_course_add_to_cart_form_action', get_permalink( get_the_ID() ) ) ); ?>" method="post" enctype="multipart/form-data">
            <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"  class="tutor-btn tutor-btn-icon tutor-btn-primary tutor-btn-lg tutor-btn-full tutor-mt-24 tutor-add-to-cart-button">
                <span class="btn-icon tutor-icon-cart-filled"></span>
                <span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
            </button>
        </form>
        <?php
    }
} else {
	?>
	<p class="tutor-alert-warning">
		<?php _e( 'Please make sure that your product exists and valid for this course', 'tutor' ); ?>
	</p>
	<?php
}
