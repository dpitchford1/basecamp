<?php
/**
 * About Page — Link List Metabox
 *
 * Adds a sortable link repeater to pages using the page-about.php template.
 * Links are stored as an ordered array and can be retrieved with
 * kaneism_about_links_list().
 *
 * Meta key:  _kaneism_about_links
 * Array shape: [ [ 'label' => string, 'url' => string, 'new_tab' => int ], … ]
 *
 * @package Kaneism
 */

declare( strict_types=1 );

namespace Kaneism\ThemeFunctions;

if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'add_meta_boxes', function (): void {
	add_meta_box(
		'kaneism_about_links',
		__( 'About Page Link List', 'kaneism' ),
		__NAMESPACE__ . '\about_links_meta_box_callback',
		'page',
		'normal',
		'default'
	);
} );

/**
 * @param \WP_Post $post
 */
function about_links_meta_box_callback( \WP_Post $post ): void {
	if ( get_page_template_slug( $post->ID ) !== 'page-about.php' ) {
		return;
	}

	$links = get_post_meta( $post->ID, '_kaneism_about_links', true );
	if ( ! is_array( $links ) ) {
		$links = [];
	}

	wp_nonce_field( 'kaneism_about_links_nonce', 'kaneism_about_links_nonce_field' );
	?>
	<style>
		.about-link-row { display: flex; align-items: center; gap: 8px; }
		.about-link-row .drag-handle { cursor: move; font-size: 18px; color: #888; padding: 0 8px; }
	</style>

	<div id="about-links-list">
		<?php foreach ( $links as $i => $link ) : ?>
		<div class="about-link-row" style="margin-bottom:10px;">
			<span class="drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'kaneism' ); ?>">&#9776;</span>
			<input type="text"
				name="kaneism_about_links[<?php echo $i; ?>][label]"
				placeholder="<?php esc_attr_e( 'Label', 'kaneism' ); ?>"
				value="<?php echo esc_attr( $link['label'] ?? '' ); ?>"
				style="width:20%;" />
			<input type="url"
				name="kaneism_about_links[<?php echo $i; ?>][url]"
				placeholder="<?php esc_attr_e( 'URL', 'kaneism' ); ?>"
				value="<?php echo esc_attr( $link['url'] ?? '' ); ?>"
				style="width:40%;" />
			<label>
				<input type="checkbox"
					name="kaneism_about_links[<?php echo $i; ?>][new_tab]"
					value="1"
					<?php checked( ! empty( $link['new_tab'] ) ); ?> />
				<?php esc_html_e( 'Open in new tab', 'kaneism' ); ?>
			</label>
			<button class="remove-link button"><?php esc_html_e( 'Remove', 'kaneism' ); ?></button>
		</div>
		<?php endforeach; ?>
	</div>

	<button type="button" class="button" id="add-about-link">
		<?php esc_html_e( 'Add link', 'kaneism' ); ?>
	</button>

	<script>
	(function($) {
		function updateLinkIndexes() {
			$('#about-links-list .about-link-row').each(function(i, row) {
				$(row).find('input').each(function() {
					var name = $(this).attr('name');
					if (name) {
						$(this).attr('name', name.replace(/kaneism_about_links\[\d+\]/, 'kaneism_about_links[' + i + ']'));
					}
				});
			});
		}

		$(document).ready(function() {
			$('#about-links-list').sortable({
				handle: '.drag-handle',
				items: '.about-link-row',
				update: updateLinkIndexes
			});

			$('#add-about-link').on('click', function(e) {
				e.preventDefault();
				var i   = $('#about-links-list .about-link-row').length;
				var row = '<div class="about-link-row" style="margin-bottom:10px;">' +
					'<span class="drag-handle" title="<?php echo esc_js( __( 'Drag to reorder', 'kaneism' ) ); ?>">&#9776;</span>' +
					'<input type="text" name="kaneism_about_links[' + i + '][label]" placeholder="<?php echo esc_js( __( 'Label', 'kaneism' ) ); ?>" style="width:20%;" />' +
					'<input type="url"  name="kaneism_about_links[' + i + '][url]"   placeholder="<?php echo esc_js( __( 'URL', 'kaneism' ) ); ?>"   style="width:40%;" />' +
					'<label><input type="checkbox" name="kaneism_about_links[' + i + '][new_tab]" value="1" /> <?php echo esc_js( __( 'Open in new tab', 'kaneism' ) ); ?></label>' +
					'<button class="remove-link button"><?php echo esc_js( __( 'Remove', 'kaneism' ) ); ?></button>' +
				'</div>';
				$('#about-links-list').append(row);
			});

			$(document).on('click', '.remove-link', function(e) {
				e.preventDefault();
				$(this).closest('.about-link-row').remove();
				updateLinkIndexes();
			});
		});
	})(jQuery);
	</script>
	<?php
}

add_action( 'save_post', function ( int $post_id ): void {
	if ( ! isset( $_POST['kaneism_about_links_nonce_field'] )
		|| ! wp_verify_nonce( $_POST['kaneism_about_links_nonce_field'], 'kaneism_about_links_nonce' )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw   = isset( $_POST['kaneism_about_links'] ) && is_array( $_POST['kaneism_about_links'] ) ? $_POST['kaneism_about_links'] : [];
	$clean = [];

	foreach ( $raw as $link ) {
		$url = esc_url_raw( wp_unslash( $link['url'] ?? '' ) );
		if ( empty( $url ) ) {
			continue;
		}
		$clean[] = [
			'label'   => sanitize_text_field( wp_unslash( $link['label'] ?? '' ) ),
			'url'     => $url,
			'new_tab' => empty( $link['new_tab'] ) ? 0 : 1,
		];
	}

	if ( ! empty( $clean ) ) {
		update_post_meta( $post_id, '_kaneism_about_links', $clean );
	} else {
		delete_post_meta( $post_id, '_kaneism_about_links' );
	}
} );

// ---------------------------------------------------------------------------
// Template helper (global scope — used in page-about.php)
// ---------------------------------------------------------------------------

/**
 * Get the about page link list array.
 *
 * @param int|null $post_id Defaults to current post.
 * @return array<int, array{label: string, url: string, new_tab: int}>
 */
function kaneism_about_links_list( ?int $post_id = null ): array {
	$post_id = $post_id ?? get_the_ID();
	$links   = get_post_meta( $post_id, '_kaneism_about_links', true );
	return ( is_array( $links ) && ! empty( $links ) ) ? $links : [];
}
