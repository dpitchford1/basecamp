<?php
/**
 * Template part: Latest Projects
 *
 * Queries up to 3 random Work posts and renders them in a 3-column grid.
 * First item gets eager loading and high fetchpriority; remaining items
 * are lazy-loaded.
 *
 * Requires the Work plugin (post type 'work').
 *
 * @package Kaneism
 */

if ( ! post_type_exists( 'work' ) ) {
	return;
}

$latest_query = new WP_Query( [
	'post_type'      => 'work',
	'posts_per_page' => apply_filters( 'kaneism_latest_projects_limit', 3 ),
	'orderby'        => 'rand',
] );
?>
<section class="region component--section">
	<h3 class="sizes-LG section--heading"><?php esc_html_e( 'Latest Projects', 'kaneism' ); ?></h3>

	<div class="grid-general grid--3col">
		<?php if ( $latest_query->have_posts() ) :
			$index = 0;
			while ( $latest_query->have_posts() ) :
				$latest_query->the_post();
		?>
		<div class="kane-work-item">
			<a href="<?php the_permalink(); ?>" class="feature-img" tabindex="-1" aria-hidden="true">
				<?php if ( has_post_thumbnail() ) :
					echo wp_get_attachment_image(
						get_post_thumbnail_id(),
						'kaneism-img-sm',
						false,
						[
							'loading'       => $index === 0 ? 'eager' : 'lazy',
							'fetchpriority' => $index === 0 ? 'high'  : 'low',
						]
					);
				endif; ?>
			</a>
			<h4 class="sizes-M"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
		</div>
		<?php
			$index++;
			endwhile;
			wp_reset_postdata();
		else : ?>
			<p><?php esc_html_e( 'No projects found.', 'kaneism' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="view-all">
		<a href="<?php echo esc_url( (string) get_post_type_archive_link( 'work' ) ); ?>">
			<?php esc_html_e( 'View All Projects', 'kaneism' ); ?>
		</a>
	</div>
</section>
