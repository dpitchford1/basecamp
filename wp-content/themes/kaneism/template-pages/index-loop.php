<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?>>

    <?php get_template_part( 'template-pages/header', 'title'); ?>
    <?php get_template_part( 'template-pages/byline'); ?>
    
    <?php the_content(); ?>
    <?php get_template_part( 'temtemplate-pagesplates/category-tags'); ?>

</article>

<?php endwhile; endif; ?>

<?php // get_template_part( 'template-pages/post-navigation'); ?>