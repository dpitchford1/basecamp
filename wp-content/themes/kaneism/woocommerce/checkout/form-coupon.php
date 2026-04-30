<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
	return;
}

?>
<div class="woocommerce-form-coupon-toggle">
	<?php wc_print_notice( apply_filters( 'woocommerce_checkout_coupon_message', esc_html__( 'Have a coupon?', 'woocommerce' ) . ' <a href="#showcoupon" class="showcoupon">' . esc_html__( 'Enter your code here', 'woocommerce' ) . '</a>' ), 'notice' ); ?>
</div>

<form id="showcoupon" class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none">

	<p>If you have a coupon code, please apply it below.</p>

	<p class="form-row form-row-first">
		<label for="coupon_code" class="hide-text">Coupon:</label>
		<input type="text" name="coupon_code" class="input-text" placeholder="Coupon code" id="coupon_code" value="" />
        <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="Apply coupon">Apply Coupon</button>
	</p>

	<!-- <p class="form-row form-row-last">
		
	</p> -->
</form>
