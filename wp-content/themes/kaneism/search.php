<?php get_header(); ?>

<main id="main-content" class="site-main">
    <h2 class="sizes-XLG page--heading">Search</h2>
    
    <section id="post-<?php the_ID(); ?>" <?php post_class(''); ?>>
        <h3 class="sizes-L section--heading"><?php _e( 'Search Results for:', 'kaneism' ); ?> <?php echo esc_attr(get_search_query()); ?></h3>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="grid-general grid--2col">
            <?php get_template_part( 'template-pages/content', 'excerpt'); ?>
        </article>
    <?php endwhile; endif; ?>
    </section>
    <div class="region component--section">
        <h3 class="sizes-M section--heading">If you didn't find what you were looking for, check out some more funky stuff below.</h3>
    </div>
    <?php get_template_part( 'template-parts/content-featured-projects'); ?>
    <?php get_template_part( 'template-parts/content-bits-component'); ?>
</main>
<?php // get_sidebar(); ?>

<?php get_footer(); ?>
