<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<?php get_template_part( 'template-pages/header', 'title'); ?>

 <?php the_content(); ?>

<?php endwhile; endif; ?>