<?php
/**
 * Template part: Photo Grid
 *
 * Renders a 3-column image grid for the current page (or a page passed via
 * the $kaneism_photo_grid_post_id global set by kaneism_display_photo_grid()).
 *
 * Usage in a template:
 *   kaneism_display_photo_grid();          // current page
 *   kaneism_display_photo_grid( $post_id ); // specific page
 *
 * @package Kaneism
 */

global $kaneism_photo_grid_post_id;
$post_id = $kaneism_photo_grid_post_id ? (int) $kaneism_photo_grid_post_id : get_the_ID();
$images  = kaneism_get_photo_grid_images( $post_id );

if ( empty( $images ) ) {
	return;
}
?>
<section class="grid-general grid--3col tight--grid">
	<h3 class="hide-text"><?php esc_html_e( 'Gallery of Images', 'kaneism' ); ?></h3>
	<?php foreach ( $images as $index => $image ) : ?>
	<div class="photo-grid-item">
		<figure>
			<?php
			echo wp_get_attachment_image(
				$image['id'],
				'kaneism-img-s',
				false,
				[
					'class'   => 'photo-grid-image',
					'alt'     => $image['alt'],
					'loading' => $index === 0 ? 'eager' : 'lazy',
				]
			);
			?>
			<?php if ( ! empty( $image['caption'] ) ) : ?>
			<figcaption class="img--caption"><?php echo esc_html( $image['caption'] ); ?></figcaption>
			<?php endif; ?>
		</figure>
	</div>
	<?php endforeach; ?>
</section>
