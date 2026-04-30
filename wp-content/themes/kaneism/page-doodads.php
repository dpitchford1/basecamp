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
    <?php // the_title( '<h2 class="sizes-LG">', '</h2>' ); ?>
    <h2 class="sizes-XLG page--heading">Kaneism Doodads</h2>
    <?php
    while ( have_posts() ) :
        the_post();

        //do_action( 'kaneism_page_before' );
        ?>
        <div class="has--ul"><?php the_content(); ?></div> 
        
        <?php /* Photo grid with srcset */ ?>
        <?php kaneism_display_photo_grid(); ?>
        <?php
        /**
         * Functions hooked in to kaneism_page_after action
         *
         * @hooked kaneism_display_comments - 10
         */
        //do_action( 'kaneism_page_after' );

    endwhile; // End of the loop.
    ?>

</main><?php /* main */ ?>

<?php
//do_action( 'kaneism_sidebar' );
get_footer();
