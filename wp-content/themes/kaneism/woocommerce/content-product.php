<?php
/**
 * Custom product card template for WooCommerce.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<article>
	<a class="feature-img" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" tabindex="-1" aria-hidden="true">
    <?php // echo product_thumbnail(); ?>
			<?php
			// Sale flash
			woocommerce_show_product_loop_sale_flash();

			// Product thumbnail - output actual <img> tag
			$thumbnail_id = $product->get_image_id();
			if ( $thumbnail_id ) {
				// Use 'woocommerce_thumbnail' for cropped product image
				echo wp_get_attachment_image( $thumbnail_id, 'woocommerce_thumbnail', false, array( 'class' => 'feature-imgs' ) );
			}
			?>
    </a>
    <div class="product-card-content">
        <h3 class="sizes-L shop--title"><a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
        <?php
        // Price
        echo $product->get_price_html();

        
        // Sale Price with strike through
        // if ( $product->is_on_sale() ) {
        //     echo '<span class="regular-price" style="text-decoration:line-through;">' . wc_price( $product->get_regular_price() ) . '</span> ';
        //     echo '<span class="promo-price">' . wc_price( $product->get_sale_price() ) . '</span>';
        // } else {
        //     echo '<span class="promo-price">' . $product->get_price_html() . '</span>';
        // }
            // $25.00 Original price was: $25.00.$20.00Current price is: $20.00.
        ?>
        <p><?php woocommerce_template_loop_add_to_cart(); ?></p>
    </div>
</article>
