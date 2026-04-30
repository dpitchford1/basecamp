<?php get_header(); ?>

<main id="main-content" class="search-results">

	<header class="search-results__header">
		<h2 class="search-results__title">
			<?php
			if ( get_search_query() ) {
				printf(
					'%s <span class="search-results__query">&#8220;%s&#8221;</span>',
					esc_html__( 'Search results for:', 'basecamp' ),
					esc_html( get_search_query() )
				);
			} else {
				esc_html_e( 'Search Results', 'basecamp' );
			}
			?>
		</h2>
	</header>

	<?php if ( have_posts() ) : ?>

		<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'search-results__item' ); ?>>

				<?php get_template_part( 'templates/header', 'title' ); ?>

				<?php get_template_part( 'templates/byline' ); ?>

				<div class="entry-content">
					<?php get_template_part( 'templates/content', 'excerpt' ); ?>
				</div>

				<?php get_template_part( 'templates/category-tags' ); ?>

			</article>

		<?php endwhile; ?>

		<?php get_template_part( 'templates/post-navigation' ); ?>

	<?php else : ?>

		<section class="search-results__no-results">
			<p><?php esc_html_e( 'No results found. Try a different search term.', 'basecamp' ); ?></p>
			<?php get_search_form(); ?>
		</section>

	<?php endif; ?>

</main>

<?php get_footer(); ?>
