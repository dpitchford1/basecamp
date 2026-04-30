<?php
/**
 * About Page — Client List Metabox
 *
 * Adds a repeater to the About page for managing a list of clients
 * (name + logo image). Outputs are accessible via:
 *
 *   $clients = get_post_meta( $post->ID, '_about_clients', true );
 *   // array of [ 'name' => string, 'image' => int (attachment ID) ]
 *
 * Meta key: _about_clients
 *
 * @package Kaneism
 */

declare( strict_types=1 );

namespace Kaneism\ThemeFunctions;

if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'add_meta_boxes', function (): void {
	$about = get_page_by_path( 'about' );
	if ( ! $about ) {
		return;
	}

	add_meta_box(
		'about_clients',
		__( 'Client List', 'kaneism' ),
		__NAMESPACE__ . '\clients_meta_box_callback',
		'page',
		'normal',
		'default',
		[ '__block_editor_compatible_meta_box' => true ]
	);
} );

/**
 * @param \WP_Post $post
 */
function clients_meta_box_callback( \WP_Post $post ): void {
	$clients = get_post_meta( $post->ID, '_about_clients', true );
	if ( ! is_array( $clients ) ) {
		$clients = [];
	}

	wp_nonce_field( 'about_clients_nonce', 'about_clients_nonce_field' );
	?>
	<div id="about-clients-repeater">
		<table class="about-clients-table" style="width:100%;border-collapse:collapse;">
			<thead>
				<tr>
					<th style="width:30%;"><?php esc_html_e( 'Client Name', 'kaneism' ); ?></th>
					<th style="width:50%;"><?php esc_html_e( 'Image', 'kaneism' ); ?></th>
					<th style="width:20%;"><?php esc_html_e( 'Actions', 'kaneism' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $clients as $i => $row ) :
				$name  = trim( (string) ( $row['name']  ?? '' ) );
				$image = (int) ( $row['image'] ?? 0 );
				if ( $name === '' && $image === 0 ) {
					continue;
				}
			?>
				<tr class="about-client-row">
					<td>
						<input type="text"
							name="about_clients[<?php echo $i; ?>][name]"
							value="<?php echo esc_attr( $name ); ?>"
							style="width:100%;" />
					</td>
					<td>
						<input type="hidden"
							class="about-client-image-id"
							name="about_clients[<?php echo $i; ?>][image]"
							value="<?php echo esc_attr( $image ); ?>" />
						<img class="about-client-image-preview"
							src="<?php echo $image ? esc_url( (string) wp_get_attachment_image_url( $image, 'thumbnail' ) ) : ''; ?>"
							style="max-width:80px;max-height:80px;display:<?php echo $image ? 'inline' : 'none'; ?>;" />
						<button type="button" class="button about-client-image-upload">
							<?php echo $image ? esc_html__( 'Change Image', 'kaneism' ) : esc_html__( 'Select Image', 'kaneism' ); ?>
						</button>
						<button type="button" class="button about-client-image-remove"
							style="display:<?php echo $image ? 'inline' : 'none'; ?>;">
							<?php esc_html_e( 'Remove', 'kaneism' ); ?>
						</button>
					</td>
					<td>
						<button type="button" class="button about-client-move-up">&#8593;</button>
						<button type="button" class="button about-client-move-down">&#8595;</button>
						<button type="button" class="button about-client-delete"><?php esc_html_e( 'Delete', 'kaneism' ); ?></button>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<button type="button" class="button" id="about-client-add-row">
			<?php esc_html_e( 'Add Client', 'kaneism' ); ?>
		</button>
	</div>

	<style>.about-client-row td { vertical-align: middle; }</style>

	<script>
	jQuery(function($) {
		function updateRowNames() {
			$('#about-clients-repeater tbody tr').each(function(i, row) {
				$(row).find('input[name^="about_clients"]').each(function() {
					var field = $(this).attr('name').replace(/about_clients\[\d+\]\[(name|image)\]/, '$1');
					$(this).attr('name', 'about_clients[' + i + '][' + field + ']');
				});
			});
		}
		updateRowNames();

		$('#about-client-add-row').on('click', function() {
			var i = $('#about-clients-repeater tbody tr').length;
			var row = '<tr class="about-client-row">' +
				'<td><input type="text" name="about_clients[' + i + '][name]" value="" style="width:100%;" /></td>' +
				'<td>' +
					'<input type="hidden" class="about-client-image-id" name="about_clients[' + i + '][image]" value="" />' +
					'<img class="about-client-image-preview" src="" style="max-width:80px;max-height:80px;display:none;" />' +
					'<button type="button" class="button about-client-image-upload"><?php echo esc_js( __( 'Select Image', 'kaneism' ) ); ?></button>' +
					'<button type="button" class="button about-client-image-remove" style="display:none;"><?php echo esc_js( __( 'Remove', 'kaneism' ) ); ?></button>' +
				'</td>' +
				'<td>' +
					'<button type="button" class="button about-client-move-up">&#8593;</button>' +
					'<button type="button" class="button about-client-move-down">&#8595;</button>' +
					'<button type="button" class="button about-client-delete"><?php echo esc_js( __( 'Delete', 'kaneism' ) ); ?></button>' +
				'</td>' +
			'</tr>';
			$('#about-clients-repeater tbody').append(row);
			updateRowNames();
		});

		$('#about-clients-repeater').on('click', '.about-client-delete', function() {
			$(this).closest('tr').remove();
			updateRowNames();
		});
		$('#about-clients-repeater').on('click', '.about-client-move-up', function() {
			var row = $(this).closest('tr');
			row.prev().before(row);
			updateRowNames();
		});
		$('#about-clients-repeater').on('click', '.about-client-move-down', function() {
			var row = $(this).closest('tr');
			row.next().after(row);
			updateRowNames();
		});

		var media_frame;
		$('#about-clients-repeater').on('click', '.about-client-image-upload', function(e) {
			e.preventDefault();
			var button = $(this);
			var row    = button.closest('tr');
			if (media_frame) media_frame.close();
			media_frame = wp.media({
				title: '<?php echo esc_js( __( 'Select Image', 'kaneism' ) ); ?>',
				button: { text: '<?php echo esc_js( __( 'Use this image', 'kaneism' ) ); ?>' },
				multiple: false
			});
			media_frame.on('select', function() {
				var attachment = media_frame.state().get('selection').first().toJSON();
				row.find('.about-client-image-id').val(attachment.id);
				row.find('.about-client-image-preview')
					.attr('src', attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url)
					.show();
				row.find('.about-client-image-remove').show();
				button.text('<?php echo esc_js( __( 'Change Image', 'kaneism' ) ); ?>');
			});
			media_frame.open();
		});
		$('#about-clients-repeater').on('click', '.about-client-image-remove', function(e) {
			e.preventDefault();
			var row = $(this).closest('tr');
			row.find('.about-client-image-id').val('');
			row.find('.about-client-image-preview').hide().attr('src', '');
			$(this).hide();
			row.find('.about-client-image-upload').text('<?php echo esc_js( __( 'Select Image', 'kaneism' ) ); ?>');
		});
	});
	</script>
	<?php
}

add_action( 'save_post_page', function ( int $post_id ): void {
	if ( ! isset( $_POST['about_clients_nonce_field'] )
		|| ! wp_verify_nonce( $_POST['about_clients_nonce_field'], 'about_clients_nonce' )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	$raw    = isset( $_POST['about_clients'] ) && is_array( $_POST['about_clients'] ) ? $_POST['about_clients'] : [];
	$clean  = [];

	foreach ( $raw as $row ) {
		$name  = trim( sanitize_text_field( wp_unslash( $row['name']  ?? '' ) ) );
		$image = (int) ( $row['image'] ?? 0 );
		if ( $name === '' && $image === 0 ) {
			continue;
		}
		$clean[] = [ 'name' => $name, 'image' => $image ];
	}

	update_post_meta( $post_id, '_about_clients', $clean );
} );
