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

<main id="main-content" class="site-main">

    <?php // Edit the loop in /templates/loop. Or roll your own. ?>
    <?php get_template_part( 'template-pages/loop'); ?>

</main>

<?php // get_sidebar(); ?>

<?php get_footer(); ?>
