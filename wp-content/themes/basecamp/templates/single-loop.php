<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<section id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article" itemscope itemprop="blogPost" itemtype="http://schema.org/BlogPosting">

		<div class="article-header entry-header">

			<?php get_template_part( 'templates/header', 'title'); ?>

			<?php // Delete or comment out if you don't need this on your page or post. Edit in /templates/byline.php ?>
			<?php get_template_part( 'templates/byline'); ?>
                  
		</div> <?php // end article header ?>

        <article class="entry-content cf" itemprop="articleBody">

        	<?php if ( has_post_format()) { 
        		get_template_part( 'format', get_post_format() ); 
        	}
        	?>
        
        	<?php the_content(); ?>

        </article> <?php // end article section ?>

		<div class="article-footer">

			<?php get_template_part( 'templates/category-tags'); ?>

		</div> <?php // end article footer ?>

	</section> <?php // end article ?>

<?php endwhile; endif; ?>