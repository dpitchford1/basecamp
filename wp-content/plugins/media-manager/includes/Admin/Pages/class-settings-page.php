<?php
declare(strict_types=1);
namespace MediaManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SettingsPage
 *
 * Render callback for the Settings screen.
 * Phase 3: stub wrapper only. Phase 6 will fill in the full settings form.
 */
final class SettingsPage {

	private string $hook = '';

	public function set_hook( string $hook ): void {
		$this->hook = $hook;
	}

	public function get_hook(): string {
		return $this->hook;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'media-manager' ) );
		}

		$per_page        = (int) get_option( 'mm_items_per_page', 500 );
		$disable_scaling = (bool) get_option( 'mm_disable_scaling', false );
		$skip_webp       = (bool) get_option( 'mm_skip_webp', false );
		$strip_exif      = (bool) get_option( 'mm_strip_exif', false );
		?>
		<div class="wrap mm-page-wrap" id="mm-settings">
			<h1><?php esc_html_e( 'Media Manager — Settings', 'media-manager' ); ?></h1>

			<div id="mm-settings-notice"></div>

			<form id="mm-settings-form">
				<?php wp_nonce_field( MM_NONCE, 'nonce' ); ?>
				<input type="hidden" name="action" value="mm_save_settings">

				<div class="mm-section">
					<h2><?php esc_html_e( 'General', 'media-manager' ); ?></h2>

					<div class="mm-field-row">
						<label for="mm-per-page"><?php esc_html_e( 'Files per page', 'media-manager' ); ?></label>
						<div>
							<input type="number" id="mm-per-page" name="mm_items_per_page"
								value="<?php echo esc_attr( $per_page ); ?>" min="1" max="2000" class="small-text">
							<p class="mm-field-desc"><?php esc_html_e( 'Maximum files shown in the grid per folder (1–2000).', 'media-manager' ); ?></p>
						</div>
					</div>

				</div><!-- .mm-section -->

				<div class="mm-section">
					<h2><?php esc_html_e( 'Image Upload', 'media-manager' ); ?></h2>

					<div class="mm-field-row">
						<label for="mm-disable-scaling"><?php esc_html_e( 'Disable big image scaling', 'media-manager' ); ?></label>
						<div>
							<input type="checkbox" id="mm-disable-scaling" name="mm_disable_scaling"
								value="1" <?php checked( $disable_scaling ); ?>>
							<p class="mm-field-desc"><?php esc_html_e( 'Prevent WordPress from down-sampling images larger than 2560px.', 'media-manager' ); ?></p>
						</div>
					</div>

					<div class="mm-field-row">
						<label for="mm-skip-webp"><?php esc_html_e( 'Skip WebP generation', 'media-manager' ); ?></label>
						<div>
							<input type="checkbox" id="mm-skip-webp" name="mm_skip_webp"
								value="1" <?php checked( $skip_webp ); ?>>
							<p class="mm-field-desc"><?php esc_html_e( 'Do not generate .webp variants on upload (WordPress 5.8+).', 'media-manager' ); ?></p>
						</div>
					</div>

					<div class="mm-field-row">
						<label for="mm-strip-exif"><?php esc_html_e( 'Strip EXIF metadata', 'media-manager' ); ?></label>
						<div>
							<input type="checkbox" id="mm-strip-exif" name="mm_strip_exif"
								value="1" <?php checked( $strip_exif ); ?>>
							<p class="mm-field-desc"><?php esc_html_e( 'Re-encode JPEG uploads through GD to remove camera/GPS metadata. Requires PHP GD extension.', 'media-manager' ); ?></p>
						</div>
					</div>
				</div><!-- .mm-section -->

				<p>
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save Settings', 'media-manager' ); ?>
					</button>
				</p>
			</form>
		</div><!-- .wrap#mm-settings -->

		<script>
		jQuery( document ).ready( function ( $ ) {
			$( '#mm-settings-form' ).on( 'submit', function ( e ) {
				e.preventDefault();
				var data = $( this ).serializeArray();
				$.post( window.mm_ajax ? window.mm_ajax.url : ajaxurl, data, function ( r ) {
					var type    = r.success ? 'success' : 'error';
					var message = ( r.data && r.data.message ) ? r.data.message : ( r.success ? 'Saved.' : 'Error.' );
					$( '#mm-settings-notice' ).html(
						'<div class="mm-notice mm-notice-' + type + '">' + $( '<span>' ).text( message ).html() + '</div>'
					);
				} );
			} );
		} );
		</script>
		<?php
	}
}
