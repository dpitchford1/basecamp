<?php
/**
 * Template part: Featured Projects
 *
 * Queries up to 3 Work posts with _work_is_featured = '1' and renders
 * them in a 3-column grid.
 *
 * Requires the Work plugin (post type 'work').
 *
 * @package Kaneism
 */

if ( ! post_type_exists( 'work' ) ) {
	return;
}

$featured_query = new WP_Query( [
	'post_type'      => 'work',
	'posts_per_page' => apply_filters( 'kaneism_featured_projects_limit', 3 ),
	'meta_query'     => [ [
		'key'     => '_work_is_featured',
		'value'   => '1',
		'compare' => '=',
	] ],
] );
?>
<section class="region component--section">
	<h3 class="sizes-LG section--heading"><?php esc_html_e( 'Highlighted Projects', 'kaneism' ); ?></h3>

	<div class="grid-general grid--3col">
		<?php if ( $featured_query->have_posts() ) :
			while ( $featured_query->have_posts() ) :
				$featured_query->the_post();
		?>
		<div class="kane-work-item feature is--featured">
			<a href="<?php the_permalink(); ?>" class="feature-img img--isFeatured" tabindex="-1" aria-hidden="true">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'kaneism-img-s' ); ?>
				<?php endif; ?>
			</a>
			<h4 class="sizes-M"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
			<?php if ( has_excerpt() ) : ?>
			<div><?php the_excerpt(); ?></div>
			<?php endif; ?>
		</div>
		<?php
			endwhile;
			wp_reset_postdata();
		else : ?>
			<p><?php esc_html_e( 'No featured projects found.', 'kaneism' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="view-all">
		<a href="<?php echo esc_url( (string) get_post_type_archive_link( 'work' ) ); ?>">
			<?php esc_html_e( 'View All Projects', 'kaneism' ); ?>
		</a>
	</div>
</section>
