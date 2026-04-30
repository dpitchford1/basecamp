<?php get_header(); ?>

<main id="main-content" class="site-main">
    <?php get_template_part( 'template-pages/header', 'title'); ?>
    <section id="post-not-found" class="region component--section">
        <h3 class="sizes-LG"><?php _e( 'I\'m sorry Dave, I\'m afraid I can\'t do that.', 'kaneism' ); ?></h3>
        <p>We couldn't find what you are looking for. Search for something else, or you could browse some other sections below.</p>
    </section>
    <?php get_template_part( 'template-parts/content-bits-component'); ?>
    <?php get_template_part( 'template-parts/content-latest-projects'); ?>

</main>

<?php get_footer(); ?>
