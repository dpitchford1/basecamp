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
    <h2 class="sizes-XLG page--heading">Some 90's</h2>
    <nav class="subnav--global" aria-label="Photo Categories">
        <!-- <input type="checkbox" id="dropdown" class="subnav--checkbox">
        <label for="dropdown" class="dropdown-btn">
            <span>Filter Projects By:</span>
            <span class="arrow"></span>
        </label>
        <span class="filter-label">Filter By: </span>
        <ul class="subnav--menu">
            <li class="subnav--item"><a class="nav--selected" href="<?php echo esc_url( home_url( '/some-90s/' ) ); ?>">Colours</a></li>
            <li class="subnav--item"><a href="<?php echo esc_url( home_url( '/some-90s/hands/' ) ); ?>">Hands</a></li>
        </ul> -->
        <span>Filter Projects By:</span>
        <?php the_subnav(); ?>
    </nav>
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
