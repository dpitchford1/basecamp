<?php
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself.
 *
 * Template name: Homepage
 *
 * @package kaneism
 */

get_header(); ?>

<main id="main-content" class="site-main">
    <h2 class="sizes-XLG page--heading">The Goods</h2>
    <section class="grid-general grid--2col">
    <?php // Display the page content
    while ( have_posts() ) : the_post(); ?>
        
    <?php if ( has_post_thumbnail() ) { ?>
        <div class="post-thumbnail">
            <?php the_post_thumbnail( 'kaneism-img-m',array( 'loading' => 'eager') ); ?>
        </div>
    <?php } ?>

        <div class="blurb--quote">
            <?php the_excerpt(); ?>
            <p><?php echo get_post_meta($post->ID, 'big-excerpt', true); ?></p>
        </div>
        <?php endwhile; ?>
    </section>

    <?php /* Component: Featured Projects */ ?>
    <?php get_template_part( 'template-parts/content-featured-projects'); ?>

    <?php /* Component: Quotable */ ?>
    <section class="region component--section">
    

        <h3 class="sizes-LG section--heading">Quotables</h3>
        <?php
        // Display the page content
        while ( have_posts() ) :
            the_post();
            ?>
            <div class="blurb--quote cvt"><?php the_content(); ?></div>
            <?php
        endwhile;
        ?>
    </section>

    <?php /* Component: Latest Shop Items */ ?>
    <?php if (class_exists('WooCommerce')) : ?>
    <div class="region component--section">
        <h3 class="sizes-LG section--heading"><?php echo esc_html__('From the Shop', 'kaneism'); ?></h3>
        
            <?php
            // Get latest products
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 4,
                'orderby'        => 'date',
                'order'          => 'DESC',
            );
            
            $latest_products = new WP_Query($args);
            
            if ($latest_products->have_posts()) {
                if (function_exists('woocommerce_product_loop_start')) {
                    woocommerce_product_loop_start();
                }
                
                while ($latest_products->have_posts()) : 
                    $latest_products->the_post();
                    if (function_exists('wc_get_template_part')) {
                        wc_get_template_part('content', 'product');
                    }
                endwhile;
                
                if (function_exists('woocommerce_product_loop_end')) {
                    woocommerce_product_loop_end();
                }
                wp_reset_postdata();
            } else {
                echo '<p>' . esc_html__('No products found', 'kaneism') . '</p>';
            }
            ?>
        
        <div class="view-all">
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php echo esc_html__('View All Products', 'kaneism'); ?></a>
        </div>
    </div>
    <?php endif; ?>

    <?php /* Component: Bits Section */ ?>
    <?php get_template_part( 'template-parts/content-bits-component'); ?>

    <?php /* Component: Latest Projects */ ?>
    <?php get_template_part( 'template-parts/content-latest-projects'); ?>

</main><?php /* #main */ ?>
<?php
get_footer();
