<?php get_header(); ?>

<main id="main-content" class="site-main">

    <?php // Edit the loop in /templates/single-loop. Or roll your own. ?>
    <?php get_template_part( 'template-pages/single', 'loop'); ?>

</main>
<?php // get_sidebar(); ?>

<?php get_footer(); ?>
