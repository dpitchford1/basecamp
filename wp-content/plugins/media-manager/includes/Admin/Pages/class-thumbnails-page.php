<?php
declare(strict_types=1);
namespace MediaManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ThumbnailsPage
 *
 * Render callback for the Thumbnails (regeneration) screen.
 * Phase 3: stub wrapper only. Phase 7 will fill in the regen UI.
 */
final class ThumbnailsPage {

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
		?>
		<div class="wrap mm-page-wrap" id="mm-thumbnails">
			<h1><?php esc_html_e( 'Media Manager — Thumbnails', 'media-manager' ); ?></h1>

			<div class="mm-section">
				<h2><?php esc_html_e( 'Regenerate Thumbnails', 'media-manager' ); ?></h2>
				<p><?php esc_html_e( 'Regenerate WordPress thumbnail sizes for all images in your library, or limit to a specific folder.', 'media-manager' ); ?></p>

				<div class="mm-field-row">
					<label for="mm-regen-folder"><?php esc_html_e( 'Folder (optional)', 'media-manager' ); ?></label>
					<div>
						<select id="mm-regen-folder" name="folder_id">
							<option value="0"><?php esc_html_e( '— All folders —', 'media-manager' ); ?></option>
							<?php
							$folders = get_posts( [
								'post_type'      => MM_POST_TYPE,
								'post_status'    => 'publish',
								'posts_per_page' => -1,
								'orderby'        => 'title',
								'order'          => 'ASC',
							] );
							foreach ( $folders as $folder ) {
								printf(
									'<option value="%d">%s</option>',
									(int) $folder->ID,
									esc_html( $folder->post_title )
								);
							}
							?>
						</select>
					</div>
				</div>

				<button type="button" id="mm-regen-start" class="button button-primary">
					<?php esc_html_e( 'Start Regeneration', 'media-manager' ); ?>
				</button>

				<div id="mm-regen-progress" style="display:none; margin-top:16px; max-width:500px;">
					<div class="mm-progress-wrap">
						<div class="mm-progress-fill" id="mm-regen-bar" style="width:0%"></div>
					</div>
					<p class="mm-progress-label" id="mm-regen-label"></p>
				</div>
			</div><!-- .mm-section -->

		</div><!-- .wrap#mm-thumbnails -->
		<?php
	}
}
