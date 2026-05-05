<?php
declare(strict_types=1);
namespace MediaManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LibraryPage
 *
 * Renders the two-pane Media Manager library screen:
 *   Left  — jsTree folder tree (#mm-folder-tree)
 *   Right — toolbar row + file grid (#mm-file-grid)
 *
 * Phase 4: tree + folder heading populated via JS/AJAX.
 * Phase 5: file grid populated.
 */
final class LibraryPage {

	private string $hook = '';

	public function set_hook( string $hook ): void {
		$this->hook = $hook;
	}

	public function get_hook(): string {
		return $this->hook;
	}

	// -----------------------------------------------------------------------

	public function render(): void {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'media-manager' ) );
		}
		?>
		<div class="wrap" id="mm-library">

			<h1 class="wp-heading-inline"><?php esc_html_e( 'Media Manager', 'media-manager' ); ?></h1>
			<hr class="wp-header-end">

			<div id="mm-layout">

				<!-- ============================================================
				     LEFT PANE — Folder tree
				============================================================ -->
				<div id="mm-tree-pane">

					<div id="mm-tree-header">
						<span class="mm-tree-title"><?php esc_html_e( 'Folders', 'media-manager' ); ?></span>
						<button type="button" id="mm-refresh-tree" class="button-link" title="<?php esc_attr_e( 'Refresh folders', 'media-manager' ); ?>">
							<span class="dashicons dashicons-update"></span>
						</button>
					</div>

					<!-- Recent-uploads quick filter -->
					<div id="mm-recent-filter">
						<button type="button" class="button-link mm-recent-btn" data-days="7"><?php esc_html_e( 'Last 7 days', 'media-manager' ); ?></button>
						<button type="button" class="button-link mm-recent-btn" data-days="30"><?php esc_html_e( 'Last 30 days', 'media-manager' ); ?></button>
						<button type="button" id="mm-recent-clear" class="button-link" style="display:none">&times; <?php esc_html_e( 'All files', 'media-manager' ); ?></button>
					</div>

					<div id="mm-folder-tree"></div>

					<!-- Folder thumbnail hover preview strip -->
					<div id="mm-folder-preview" style="display:none"></div>

				</div><!-- #mm-tree-pane -->

				<!-- ============================================================
				     RIGHT PANE — Toolbar + file grid
				============================================================ -->
				<div id="mm-content-pane">

					<div id="mm-content-header">

						<div class="mm-folder-title-row">
							<h2 id="mm-folder-heading"></h2>
							<span id="mm-folder-count" class="mm-count"></span>
						</div>

						<!-- Primary toolbar row -->
						<div id="mm-toolbar">

							<div class="mm-sort-controls">
								<button type="button" class="button mm-sort-btn mm-sort-active mm-sort-desc" data-field="date">
									<?php esc_html_e( 'Date', 'media-manager' ); ?>
									<span class="mm-sort-arrow dashicons dashicons-arrow-down-alt2"></span>
								</button>
								<button type="button" class="button mm-sort-btn" data-field="title">
									<?php esc_html_e( 'Name', 'media-manager' ); ?>
									<span class="mm-sort-arrow dashicons dashicons-arrow-down-alt2"></span>
								</button>
							</div>

							<label class="mm-select-all-wrap">
								<input type="checkbox" id="mm-select-all">
								<span><?php esc_html_e( 'Select all', 'media-manager' ); ?></span>
							</label>

							<?php if ( current_user_can( 'upload_files' ) ) : ?>
							<button type="button" id="mm-upload-btn" class="button button-primary">
								<?php esc_html_e( 'Upload Files', 'media-manager' ); ?>
							</button>
							<?php endif; ?>

							<button type="button" id="mm-sync-btn" class="button" title="<?php esc_attr_e( 'Import files added to this folder outside of WordPress (FTP, etc.)', 'media-manager' ); ?>" disabled>
								<span class="dashicons dashicons-update mm-sync-icon"></span>
								<?php esc_html_e( 'Sync Folder', 'media-manager' ); ?>
							</button>

						</div><!-- #mm-toolbar -->

						<!-- Bulk actions bar — hidden until files are selected -->
						<div id="mm-bulk-actions">

							<span id="mm-selection-count"></span>

							<button type="button" id="mm-bulk-rename" class="button" style="display:none">
								<?php esc_html_e( 'Rename', 'media-manager' ); ?>
							</button>

							<button type="button" id="mm-bulk-delete" class="button button-link-delete">
								<?php esc_html_e( 'Delete selected', 'media-manager' ); ?>
							</button>

							<span id="mm-bulk-move-wrap" style="display:none">
								<select id="mm-bulk-move-folder">
									<option value=""><?php esc_html_e( '&mdash; Move to folder &mdash;', 'media-manager' ); ?></option>
								</select>
								<button type="button" id="mm-bulk-move-apply" class="button"><?php esc_html_e( 'Move', 'media-manager' ); ?></button>
							</span>

						</div><!-- #mm-bulk-actions -->

						<!-- Inline rename form — shown when exactly one file is selected and Rename is clicked -->
						<div id="mm-rename-form" style="display:none">
							<label for="mm-rename-base" class="screen-reader-text"><?php esc_html_e( 'New file name', 'media-manager' ); ?></label>
							<input type="text" id="mm-rename-base" class="regular-text" placeholder="<?php esc_attr_e( 'New name (no extension)', 'media-manager' ); ?>">
							<span id="mm-rename-ext" class="mm-rename-ext-badge"></span>
							<label class="mm-rename-option">
								<input type="checkbox" id="mm-rename-update-title" checked>
								<?php esc_html_e( 'Update attachment title', 'media-manager' ); ?>
							</label>
							<button type="button" id="mm-rename-submit" class="button button-primary"><?php esc_html_e( 'Save', 'media-manager' ); ?></button>
							<button type="button" id="mm-rename-cancel" class="button"><?php esc_html_e( 'Cancel', 'media-manager' ); ?></button>
						</div><!-- #mm-rename-form -->

					</div><!-- #mm-content-header -->

					<div id="mm-file-grid">
						<p class="mm-placeholder">
							<?php esc_html_e( 'Select a folder to view its contents.', 'media-manager' ); ?>
						</p>
					</div><!-- #mm-file-grid -->

					<div id="mm-pagination"></div>

				</div><!-- #mm-content-pane -->

			</div><!-- #mm-layout -->

		</div><!-- .wrap#mm-library -->
		<?php
	}
}
