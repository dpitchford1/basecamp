<?php
/*
 Template Name: Home Page
 * 
 * Use this template for a static home page. 
 * If you don't need the main loop, remove it
 * and get busy.
*/
?>

<?php get_header(); ?>

<main id="main" class="m-all t-2of3 d-5of7" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

    <?php // Edit the loop in /templates/loop. Or roll your own. ?>
    <?php get_template_part( 'templates/loop'); ?>

</main>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
