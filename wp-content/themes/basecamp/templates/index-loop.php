<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<section id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">

		<div class="article-header">

			<?php get_template_part( 'templates/header', 'title'); ?>
			
			<?php get_template_part( 'templates/byline'); ?>

		</div>

		<article class="entry-content cf">
									
			<?php the_content(); ?>

		</article>
		<div class="article-footer cf">

			<?php get_template_part( 'templates/comment', 'count'); ?>

            <?php get_template_part( 'templates/category-tags'); ?>

		</div>

	</section>

<?php endwhile; endif; ?>

<?php get_template_part( 'templates/post-navigation'); ?>