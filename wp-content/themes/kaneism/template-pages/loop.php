<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?>>

    <?php get_template_part( 'template-pages/header', 'title'); ?>

    <?php // Delete or comment out if you don't need this on your page or post. Edit in /templates/byline.php ?>
    <?php // get_template_part( 'template-pages/byline'); ?>

    <div class="entry-content" itemprop="articleBody">
    <?php get_template_part( 'template-pages/content', 'excerpt'); ?>
        <?php the_content(); ?>
    </div> <?php // end article section ?>

</article>

<?php endwhile; endif; ?>