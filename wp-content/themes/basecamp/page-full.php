<?php
/*
 Template Name: Full Width Page
 * 
 * No Sidebar on this page.
*/
?>

<?php get_header(); ?>

<main id="main" class="m-all t-all d-all" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

    <?php // Edit the loop in /templates/loop. Or roll your own. ?>
    <?php get_template_part( 'templates/loop'); ?>

</main>

<?php get_footer(); ?>
