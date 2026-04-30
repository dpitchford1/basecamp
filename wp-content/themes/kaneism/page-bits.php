<?php
/**
 * The template for displaying the Bits page.
 *
 * This template specifically displays the Bits photo page.
 *
 * @package kaneism
 */

get_header(); ?>

<main id="main-content" class="site-main">
    <h2 class="sizes-XLG page--heading">Kaneism Bits</h2>
    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <div class="has--ul"><?php the_content(); ?></div>
        
        <?php /* Photo grid with srcset --> template-parts/content-photo-grid.php */ ?>
        <?php kaneism_display_photo_grid(); ?>
        
        <?php
    endwhile; // End of the loop.
    ?>

</main><?php /* #main */ ?>

<?php
//do_action( 'kaneism_sidebar' );
get_footer();
