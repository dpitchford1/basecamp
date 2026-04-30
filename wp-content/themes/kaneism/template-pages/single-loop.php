<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(''); ?>>

    <?php get_template_part( 'template-pages/header', 'title'); ?>

    <?php // Delete or comment out if you don't need this on your page or post. Edit in /templates/byline.php ?>
    <?php get_template_part( 'template-pages/byline'); ?>

    <div class="entry-content cf" itemprop="articleBody">
        <?php if ( has_post_format()) { 
            get_template_part( 'format', get_post_format() ); 
        }
        ?>
        <?php the_content(); ?>
    </div> <?php // end article section ?>

    <?php get_template_part( 'template-pages/category-tags'); ?>

</article> <?php // end article ?>

<?php endwhile; endif; ?>