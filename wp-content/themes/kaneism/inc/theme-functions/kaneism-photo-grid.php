<?php
/**
 * Photo Grid — Metabox, helpers, and display function
 *
 * Provides:
 *   - A "Photo Collection Grid" metabox on pages (opt-in via page slug or a
 *     per-page toggle in Page Attributes)
 *   - kaneism_get_photo_grid_images( $post_id ) — returns structured image array
 *     (falls back from ACF repeater → _kaneism_bits_images meta)
 *   - kaneism_display_photo_grid( $post_id ) — renders template-parts/content-photo-grid.php
 *
 * Meta keys
 *   _kaneism_show_photo_grid  'yes'|'no'   — per-page opt-in toggle
 *   _kaneism_bits_images      int[]        — ordered attachment ID list
 *
 * @package Kaneism
 */

declare( strict_types=1 );

namespace Kaneism\ThemeFunctions;

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ---------------------------------------------------------------------------
// Metabox registration
// ---------------------------------------------------------------------------

add_action( 'add_meta_boxes', function (): void {
	global $post;

	if ( ! is_admin() || ! $post || $post->post_type !== 'page' ) {
		return;
	}

	$allowed_pages    = apply_filters( 'kaneism_photo_grid_page_slugs', [ 'bits', 'doodads', 'about', 'some-90s', 'hands' ] );
	$show_photo_grid  = get_post_meta( $post->ID, '_kaneism_show_photo_grid', true );
	$show_meta_box    = in_array( $post->post_name, $allowed_pages, true ) || $show_photo_grid === 'yes';

	if ( ! $show_meta_box ) {
		return;
	}

	add_meta_box(
		'kaneism_bits_gallery',
		__( 'Photo Collection Grid', 'kaneism' ),
		__NAMESPACE__ . '\photo_grid_meta_box_callback',
		'page',
		'normal',
		'high'
	);
} );

// ---------------------------------------------------------------------------
// Page Attributes toggle (shows in sidebar)
// ---------------------------------------------------------------------------

add_action( 'page_attributes_misc_attributes', function (): void {
	global $post;

	if ( ! is_admin() || ! $post || $post->post_type !== 'page' ) {
		return;
	}

	$show_photo_grid = get_post_meta( $post->ID, '_kaneism_show_photo_grid', true );
	?>
	<p class="post-attributes-label-wrapper">
		<label class="post-attributes-label" for="kaneism_show_photo_grid">
			<?php esc_html_e( 'Photo Grid', 'kaneism' ); ?>
		</label>
	</p>
	<select name="kaneism_show_photo_grid" id="kaneism_show_photo_grid">
		<option value="no" <?php selected( $show_photo_grid, 'no' ); ?>><?php esc_html_e( 'No Photo Grid', 'kaneism' ); ?></option>
		<option value="yes" <?php selected( $show_photo_grid, 'yes' ); ?>><?php esc_html_e( 'Enable Photo Grid', 'kaneism' ); ?></option>
	</select>
	<p class="description">
		<?php esc_html_e( 'Enable to show the photo grid editor and display a gallery grid on this page.', 'kaneism' ); ?>
	</p>
	<?php
} );

add_action( 'save_post_page', function ( int $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['kaneism_show_photo_grid'] ) ) {
		update_post_meta( $post_id, '_kaneism_show_photo_grid', sanitize_text_field( wp_unslash( $_POST['kaneism_show_photo_grid'] ) ) );
	}
} );

// ---------------------------------------------------------------------------
// Metabox callback
// ---------------------------------------------------------------------------

/**
 * @param \WP_Post $post
 */
function photo_grid_meta_box_callback( \WP_Post $post ): void {
	wp_nonce_field( 'kaneism_bits_gallery_nonce', 'kaneism_bits_gallery_nonce' );

	$gallery_images = get_post_meta( $post->ID, '_kaneism_bits_images', true );
	if ( ! is_array( $gallery_images ) ) {
		$gallery_images = [];
	}
	?>
	<div class="bits-gallery-container">
		<p class="description"><?php esc_html_e( 'Upload and manage photos for the photo grid. Drag images to reorder them.', 'kaneism' ); ?></p>

		<div class="bits-gallery-images" id="bits-gallery-images">
			<?php foreach ( $gallery_images as $image_id ) :
				$image = wp_get_attachment_image_src( (int) $image_id, 'thumbnail' );
				if ( ! $image ) {
					continue;
				}
			?>
			<div class="bits-gallery-image" data-id="<?php echo esc_attr( $image_id ); ?>">
				<img src="<?php echo esc_url( $image[0] ); ?>" alt="">
				<input type="hidden" name="bits_gallery_images[]" value="<?php echo esc_attr( $image_id ); ?>">
				<a href="#" class="bits-remove-image"><?php esc_html_e( 'Remove', 'kaneism' ); ?></a>
			</div>
			<?php endforeach; ?>
		</div>

		<div class="bits-gallery-actions">
			<input type="button" class="button" id="bits-add-images" value="<?php esc_attr_e( 'Add Photos', 'kaneism' ); ?>">
		</div>
	</div>

	<style>
		.bits-gallery-container { margin: 15px 0; }
		.bits-gallery-images { display: flex; flex-wrap: wrap; margin: 0 -5px; min-height: 30px; }
		.bits-gallery-image { position: relative; width: 150px; height: 150px; margin: 5px; border: 1px solid #ddd; overflow: hidden; cursor: move; }
		.bits-gallery-image img { width: 100%; height: 100%; object-fit: cover; }
		.bits-remove-image { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,.7); color: #fff; text-align: center; padding: 5px; text-decoration: none; display: none; }
		.bits-gallery-image:hover .bits-remove-image { display: block; }
		.ui-sortable-placeholder { border: 1px dashed #ccc; visibility: visible !important; background: #f7f7f7; height: 150px; width: 150px; margin: 5px; }
	</style>

	<script>
	jQuery(document).ready(function($) {
		$('#bits-gallery-images').sortable({
			items: '.bits-gallery-image',
			cursor: 'move',
			placeholder: 'ui-sortable-placeholder'
		});

		var file_frame;

		$('#bits-add-images').on('click', function(e) {
			e.preventDefault();
			if (file_frame) { file_frame.open(); return; }

			file_frame = wp.media.frames.file_frame = wp.media({
				title: '<?php echo esc_js( __( 'Select Photos', 'kaneism' ) ); ?>',
				button: { text: '<?php echo esc_js( __( 'Add to Grid', 'kaneism' ) ); ?>' },
				multiple: true
			});

			file_frame.on('select', function() {
				var attachments = file_frame.state().get('selection').toJSON();
				$.each(attachments, function(i, attachment) {
					$('#bits-gallery-images').append(
						'<div class="bits-gallery-image" data-id="' + attachment.id + '">' +
						'<img src="' + (attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" alt="">' +
						'<input type="hidden" name="bits_gallery_images[]" value="' + attachment.id + '">' +
						'<a href="#" class="bits-remove-image"><?php echo esc_js( __( 'Remove', 'kaneism' ) ); ?></a>' +
						'</div>'
					);
				});
			});

			file_frame.open();
		});

		$(document).on('click', '.bits-remove-image', function(e) {
			e.preventDefault();
			$(this).parent().remove();
		});
	});
	</script>
	<?php
}

// ---------------------------------------------------------------------------
// Save
// ---------------------------------------------------------------------------

add_action( 'save_post', function ( int $post_id ): void {
	if ( ! isset( $_POST['kaneism_bits_gallery_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['kaneism_bits_gallery_nonce'], 'kaneism_bits_gallery_nonce' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['bits_gallery_images'] ) ) {
		update_post_meta( $post_id, '_kaneism_bits_images', array_map( 'intval', (array) $_POST['bits_gallery_images'] ) );
	} else {
		delete_post_meta( $post_id, '_kaneism_bits_images' );
	}
} );

// ---------------------------------------------------------------------------
// Template helpers (global scope — used in template-parts)
// ---------------------------------------------------------------------------

/**
 * Get photo grid images for a page.
 *
 * Tries ACF repeater field 'gallery_images' first, then falls back to
 * the _kaneism_bits_images post meta array.
 *
 * @param int|null $post_id Defaults to current post.
 * @return array<int, array{id: int, url: string, alt: string, caption: string, type: string}>
 */
function kaneism_get_photo_grid_images( ?int $post_id = null ): array {
	$post_id = $post_id ?? get_the_ID();
	$images  = [];

	if ( function_exists( 'have_rows' ) && have_rows( 'gallery_images', $post_id ) ) {
		while ( have_rows( 'gallery_images', $post_id ) ) {
			the_row();
			$image = get_sub_field( 'gallery_image' );
			if ( $image ) {
				$images[] = [
					'id'      => (int) ( $image['ID'] ?? 0 ),
					'url'     => $image['url'],
					'alt'     => $image['alt'],
					'caption' => $image['caption'] ?? '',
					'type'    => 'acf',
				];
			}
		}
	}

	if ( empty( $images ) ) {
		$meta_images = get_post_meta( $post_id, '_kaneism_bits_images', true );
		if ( ! empty( $meta_images ) && is_array( $meta_images ) ) {
			foreach ( $meta_images as $image_id ) {
				$image_id = (int) $image_id;
				$image_post = get_post( $image_id );
				$images[] = [
					'id'      => $image_id,
					'url'     => (string) wp_get_attachment_image_url( $image_id, 'full' ),
					'alt'     => (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
					'caption' => $image_post ? $image_post->post_excerpt : '',
					'type'    => 'meta',
				];
			}
		}
	}

	return $images;
}

/**
 * Render the photo grid template part for a given page.
 *
 * @param int|null $post_id Defaults to current post.
 */
function kaneism_display_photo_grid( ?int $post_id = null ): void {
	global $kaneism_photo_grid_post_id;
	$kaneism_photo_grid_post_id = $post_id ?? get_the_ID();
	get_template_part( 'template-parts/content', 'photo-grid' );
	$kaneism_photo_grid_post_id = null;
}
