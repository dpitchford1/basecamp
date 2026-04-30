<?php // WordPress custom title script

if ( function_exists('is_tag') && is_tag() || is_category() || is_tax() ) { ?>

	<h2 class="archive-title h2"><span><?php _e( 'Posts Categorized:', 'basecamp' ); ?></span> <?php single_cat_title(); ?></h2>

<?php } elseif ( is_archive() ) { ?>

	<h3 class="h2 entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

<?php } elseif ( is_search() ) { ?>

	<h3 class="search-title entry-title">

		<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
									
	</h3>

<?php } elseif ( !(is_404() ) && ( is_single() ) || ( is_page() )) { ?>

	<h2 class="page-title" itemprop="headline"><?php the_title(); ?></h2>


<?php } elseif ( is_404() ) { ?>

	<h2><?php _e( '404', 'basecamp' ); ?></h2>

<?php } elseif ( is_home() ) { ?>

	<h2 class="h2 entry-title">
		<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>

	</h2>

<?php } else { ?>

	<h2 class="page-title" itemprop="headline"><?php the_title(); ?></h2>
<?php }


?>