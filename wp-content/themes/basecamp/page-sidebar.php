<?php
/*
 Template Name: Sidebar Page
 
 * This is a default page with a sidebar. 
 * 
 * 
 * For more info: http://codex.wordpress.org/Page_Templates
 * 
 * Visual interactive WordPress template hierarchy: https://wphierarchy.com
*/
?>

<?php get_header(); ?>

<main id="main" class="m-all t-2of3 d-5of7" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

    <?php // Edit the loop in /templates/loop. Or roll your own. ?>
    <?php get_template_part( 'templates/loop'); ?>

</main>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
