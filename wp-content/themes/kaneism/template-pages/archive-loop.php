<section class="region">
    <?php
    the_archive_title( '<h2 class="sizes-XLG">', '</h2>' );
    // Not all themes show these but you can if you want to
    the_archive_description( '<div class="taxonomy-description">', '</div>' );
    ?>
                                
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?>>

            <?php get_template_part( 'template-pages/header', 'title'); ?>
            <?php get_template_part( 'template-pages/byline'); ?>

            <?php the_post_thumbnail( 'kaneism-img-s' ); ?>

            <?php the_excerpt(); ?>

            <?php // get_template_part( 'template-pages/category-tags'); ?>

        </article>

    <?php endwhile; endif; ?>
</section>
<?php // get_template_part( 'template-pages/post-navigation'); ?>