<?php
declare(strict_types=1);
namespace MediaManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BdaPage
 *
 * Render callback for the Security screen.
 * Covers Block Direct Access (BDA) folder protection and IP address blocking.
 */
final class BdaPage {

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

		$bda_enabled         = (bool) get_option( 'mm_bda_enabled', false );
		$prevent_listing     = (bool) get_option( 'mm_bda_prevent_listing', false );
		$prevent_hotlinking  = (bool) get_option( 'mm_bda_prevent_hotlinking', false );
		$ip_blocking         = (bool) get_option( 'mm_ip_blocking_enabled', false );
		$no_access_page      = (int)  get_option( 'mm_bda_no_access_page_id', 0 );
		?>
		<div class="wrap mm-page-wrap" id="mm-bda-wrap">
			<h1><?php esc_html_e( 'Media Manager — Security', 'media-manager' ); ?></h1>

			<div id="mm-bda-notice"></div>

			<?php
			$is_apache = ( isset( $_SERVER['SERVER_SOFTWARE'] ) && false !== stripos( $_SERVER['SERVER_SOFTWARE'], 'apache' ) )
			           || function_exists( 'apache_get_version' );
			if ( ! $is_apache ) :
			?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( 'Your server does not appear to be running Apache. Block Direct Access rules use .htaccess files, which Apache reads automatically. On Nginx or other web servers these rules will not apply — equivalent deny directives must be added manually to your server configuration.', 'media-manager' ); ?></p>
			</div>
			<?php endif; ?>

			<form id="mm-bda-settings-form">
				<?php wp_nonce_field( MM_NONCE, 'nonce' ); ?>
				<input type="hidden" name="action" value="mm_save_bda_settings">

				<!-- =============================================================
				     Section 1 — Block Direct Access
				============================================================= -->
				<div class="mm-section">
					<h2><?php esc_html_e( 'Block Direct Access', 'media-manager' ); ?></h2>
					<p><?php esc_html_e( 'When enabled, folders you protect via the Library right-click menu will have an .htaccess rule added that blocks direct URL access to their files.', 'media-manager' ); ?></p>

					<div class="mm-field-row">
						<label for="mm-bda-enabled"><?php esc_html_e( 'Enable BDA', 'media-manager' ); ?></label>
						<div>
							<input type="checkbox" id="mm-bda-enabled" name="mm_bda_enabled" value="1" <?php checked( $bda_enabled ); ?>>
							<p class="mm-field-desc"><?php esc_html_e( 'Activate .htaccess-based folder protection. Folders must still be protected individually via the Library.', 'media-manager' ); ?></p>
						</div>
					</div>

					<div class="mm-field-row">
						<label for="mm-bda-prevent-listing"><?php esc_html_e( 'Prevent directory listing', 'media-manager' ); ?></label>
						<div>
							<input type="checkbox" id="mm-bda-prevent-listing" name="mm_bda_prevent_listing" value="1" <?php checked( $prevent_listing ); ?>>
							<p class="mm-field-desc"><?php esc_html_e( 'Add "Options -Indexes" to protected folder .htaccess files to block directory browsing.', 'media-manager' ); ?></p>
						</div>
					</div>

					<div class="mm-field-row">
						<label for="mm-bda-prevent-hotlinking"><?php esc_html_e( 'Prevent hotlinking', 'media-manager' ); ?></label>
						<div>
							<input type="checkbox" id="mm-bda-prevent-hotlinking" name="mm_bda_prevent_hotlinking" value="1" <?php checked( $prevent_hotlinking ); ?>>
							<p class="mm-field-desc"><?php esc_html_e( 'Block external sites from embedding your media files directly via RewriteCond on Referer.', 'media-manager' ); ?></p>
						</div>
					</div>

					<div class="mm-field-row">
						<label for="mm-bda-no-access"><?php esc_html_e( 'Redirect blocked users to', 'media-manager' ); ?></label>
						<div>
							<?php
							wp_dropdown_pages( [
								'name'             => 'mm_bda_no_access_page_id',
								'id'               => 'mm-bda-no-access',
								'selected'         => $no_access_page,
								'show_option_none' => __( '— Return 403 —', 'media-manager' ),
								'option_none_value' => 0,
							] );
							?>
							<p class="mm-field-desc"><?php esc_html_e( 'Leave blank to return a 403 header. Or choose a page (e.g. "Access Denied") to redirect blocked visitors to.', 'media-manager' ); ?></p>
						</div>
					</div>
				</div><!-- .mm-section -->

				<!-- =============================================================
				     Section 2 — IP Blocking
				============================================================= -->
				<div class="mm-section">
					<h2><?php esc_html_e( 'IP Address Blocking', 'media-manager' ); ?></h2>

					<div class="mm-field-row">
						<label for="mm-ip-blocking-enabled"><?php esc_html_e( 'Enable IP blocking', 'media-manager' ); ?></label>
						<div>
							<input type="checkbox" id="mm-ip-blocking-enabled" name="mm_ip_blocking_enabled" value="1" <?php checked( $ip_blocking ); ?>>
							<p class="mm-field-desc"><?php esc_html_e( 'When enabled, IPs in the list below are denied access to all media files, regardless of folder protection status.', 'media-manager' ); ?></p>
						</div>
					</div>
				</div><!-- .mm-section -->

			</form>

			<!-- =================================================================
			     Section 3 — Protected folders (read-only table, managed via Library)
			================================================================= -->
			<div class="mm-section">
				<h2><?php esc_html_e( 'Protected Folders', 'media-manager' ); ?></h2>
				<p><?php esc_html_e( 'Folders listed here have an active .htaccess block. Use the right-click menu in the Library to protect or unprotect folders.', 'media-manager' ); ?></p>

				<table id="mm-protected-folders" class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Folder Path', 'media-manager' ); ?></th>
							<th><?php esc_html_e( 'Protected Since', 'media-manager' ); ?></th>
							<th><?php esc_html_e( 'Action', 'media-manager' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td colspan="3"><?php esc_html_e( 'Loading…', 'media-manager' ); ?></td></tr>
					</tbody>
				</table>
			</div><!-- .mm-section -->

			<!-- =================================================================
			     Section 4 — IP address list
			================================================================= -->
			<div class="mm-section">
				<h2><?php esc_html_e( 'Blocked IP Addresses', 'media-manager' ); ?></h2>

				<div class="mm-field-row" style="margin-bottom:12px;">
					<label for="mm-ip-address"><?php esc_html_e( 'Add IP address', 'media-manager' ); ?></label>
					<div style="display:flex; gap:8px;">
						<input type="text" id="mm-ip-address" placeholder="e.g. 192.168.1.1" class="regular-text" pattern="[\d.:a-fA-F/]+">
						<button type="button" id="mm-add-ip" class="button"><?php esc_html_e( 'Add', 'media-manager' ); ?></button>
					</div>
				</div>

				<table id="mm-ip-table" class="widefat striped">
					<thead>
						<tr>
							<th style="width:30px;"><input type="checkbox" id="mm-ip-select-all"></th>
							<th><?php esc_html_e( 'IP Address', 'media-manager' ); ?></th>
							<th><?php esc_html_e( 'Blocked Since', 'media-manager' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td colspan="3"><?php esc_html_e( 'Loading…', 'media-manager' ); ?></td></tr>
					</tbody>
				</table>

				<div style="margin-top:10px;">
					<button type="button" id="mm-remove-ips" class="button button-link-delete">
						<?php esc_html_e( 'Remove selected', 'media-manager' ); ?>
					</button>
				</div>
			</div><!-- .mm-section -->

			<p class="submit">
				<button type="button" id="mm-bda-save-btn" class="button button-primary"><?php esc_html_e( 'Save Security Settings', 'media-manager' ); ?></button>
			</p>

		</div><!-- .wrap#mm-bda-wrap -->
		<?php
	}
}
