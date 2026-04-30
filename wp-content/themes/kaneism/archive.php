<?php get_header(); ?>

<main id="main-content" class="site-main">

    <?php // Edit the loop in /templates/archive-loop. Or roll your own. ?>
    <?php get_template_part( 'template-pages/archive', 'loop'); ?>

</main>
<?php // get_sidebar(); ?>

<?php get_footer(); ?>
