<?php // WordPress custom title script

if ( function_exists('is_tag') && is_tag() || is_category() || is_tax() ) { ?>

	<h2 class="sizes-XLG page--heading"><?php _e( 'Posts Categorized:', 'kaneism' ); ?> <?php single_cat_title(); ?></h2>

<?php } elseif ( is_archive() ) { ?>

	<h2 class="sizes-XLG page--heading"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>

<?php } elseif ( is_search() ) { ?>

	<h4 class="sizes-LG section--heading"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>

<?php } elseif ( !(is_404() ) && ( is_single() ) || ( is_page() )) { ?>

	<h2 class="sizes-XLG page--heading" itemprop="headline"><?php the_title(); ?></h2>


<?php } elseif ( is_404() ) { ?>

	<h2 class="sizes-XLG page--heading"><?php _e( '404', 'kaneism' ); ?></h2>

<?php } elseif ( is_home() ) { ?>

	<h2 class="sizes-XLG page--heading"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>

<?php } else { ?>

	<h2 class="sizes-XLG page--heading" itemprop="headline"><?php the_title(); ?></h2>

<?php }


?>