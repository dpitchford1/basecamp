<?php
/**
 * Template part: Bits Section Component
 *
 * Displays a 4-column grid of preview images pulled from the Bits page's
 * photo grid, with a "view all" link.
 *
 * @package Kaneism
 */

$bits_page  = get_page_by_path( 'bits' );
$bits_limit = apply_filters( 'kaneism_bits_component_limit', 4 );
?>
<section class="region component--section">
	<h3 class="sizes-LG section--heading"><?php esc_html_e( 'Bits', 'kaneism' ); ?></h3>
	<p><?php esc_html_e( 'A selection of snippets and pieces from the archives, scratch your bits and dig in.', 'kaneism' ); ?></p>

	<div class="grid-general grid--4col">
		<?php if ( $bits_page ) :
			$images = kaneism_get_photo_grid_images( $bits_page->ID );
			if ( ! empty( $images ) ) :
				$count = 0;
				foreach ( $images as $img ) :
					if ( $count >= $bits_limit ) {
						break;
					}
				?>
				<div class="kane-work-item bit-item">
					<a href="<?php echo esc_url( get_permalink( $bits_page ) ); ?>" class="feature-img" tabindex="-1" aria-hidden="true">
						<?php echo wp_get_attachment_image( $img['id'], 'kaneism-img-s', false, [ 'class' => 'feature-imgs' ] ); ?>
					</a>
					<?php if ( ! empty( $img['caption'] ) ) : ?>
					<div><?php echo esc_html( $img['caption'] ); ?></div>
					<?php endif; ?>
				</div>
				<?php
				$count++;
				endforeach;
			else : ?>
				<p><?php esc_html_e( 'No Bits images found.', 'kaneism' ); ?></p>
			<?php endif;
		else : ?>
			<p><?php esc_html_e( 'Bits page not found.', 'kaneism' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $bits_page ) : ?>
	<div class="view-all">
		<a href="<?php echo esc_url( get_permalink( $bits_page ) ); ?>">
			<?php esc_html_e( 'Scratch your Bits', 'kaneism' ); ?>
		</a>
	</div>
	<?php endif; ?>
</section>
