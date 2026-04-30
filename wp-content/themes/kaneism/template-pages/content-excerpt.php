

<?php if ( has_post_thumbnail() ) { ?>

<div class="post-thumbnail">
	<?php the_post_thumbnail( 'kaneism-img-m' ); ?>
</div>
<div class="post-thumbnail-caption">
    <?php get_template_part( 'template-pages/header', 'title'); ?>
    <?php the_excerpt(); ?>
</div>

<?php } ?>
