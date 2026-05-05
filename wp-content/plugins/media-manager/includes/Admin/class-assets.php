<?php
declare(strict_types=1);
namespace MediaManager\Admin;

use MediaManager\Core\Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets
 *
 * Enqueues plugin CSS and JS exclusively on Media Manager admin pages.
 * Localises the mm_ajax JS object so every page has nonce + AJAX URL
 * available without each page needing to repeat the call.
 *
 * Handle conventions:
 *   media-manager-admin   — main admin stylesheet
 *   media-manager-admin   — main admin script (deferred)
 *
 * Phase 3: stubs only. Real asset files are built in Phase 5 (JS/CSS).
 * The enqueue calls are structured now so Phase 5 simply drops the files
 * into place and nothing here needs to change.
 */
final class Assets {

	/** CSS/JS version — matches MM_VERSION but can be bumped independently. */
	private string $version;

	/** Screen hook suffixes returned by add_menu_page / add_submenu_page. */
	private array $screen_hooks = [];

	/** Hook suffix for the Library page — used to scope library-only assets. */
	private string $library_hook = '';

	// -----------------------------------------------------------------------

	public function __construct( Loader $loader ) {
		$this->version = MM_VERSION;
		$loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue' );
		$loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_post_media', 99 );
	}

	// -----------------------------------------------------------------------

	/**
	 * Register page screen hook suffixes so Assets can limit enqueues to
	 * MM pages only. Called by Admin after menus are registered.
	 *
	 * @param string[] $hooks  Array of screen hook suffixes from add_*_page().
	 */
	public function set_screen_hooks( array $hooks ): void {
		$this->screen_hooks = $hooks;
	}

	// -----------------------------------------------------------------------

	/**
	 * Store the Library page hook suffix separately so jsTree and the
	 * library script can be scoped to that screen only.
	 */
	public function set_library_hook( string $hook ): void {
		$this->library_hook = $hook;
	}

	// -----------------------------------------------------------------------

	/**
	 * Hooked to admin_enqueue_scripts.
	 * $hook_suffix is the current page's screen hook string.
	 */
	public function enqueue( string $hook_suffix ): void {
		if ( ! in_array( $hook_suffix, $this->screen_hooks, true ) ) {
			return;
		}

		// -----------------------------------------------------------------------
		// jsTree — enqueued on all MM pages so the tree is always available
		// (it only renders if #mm-folder-tree exists in the DOM).
		// -----------------------------------------------------------------------
		wp_enqueue_style(
			'jstree',
			MM_PLUGIN_URL . 'assets/vendor/jstree/style.min.css',
			[ 'dashicons' ],
			'3.3.16'
		);

		wp_enqueue_script(
			'jstree',
			MM_PLUGIN_URL . 'assets/vendor/jstree/jstree.min.js',
			[ 'jquery' ],
			'3.3.16',
			true
		);

		// -----------------------------------------------------------------------
		// Library JS — scoped to the Library screen only.
		// -----------------------------------------------------------------------
		if ( $hook_suffix === $this->library_hook ) {
			// Load the WP media modal stack (Backbone views, media-editor, etc.).
			wp_enqueue_media();

			// Bootstrap / namespace — must load first; defines window.mmLib.
			wp_enqueue_script(
				'media-manager-library',
				MM_PLUGIN_URL . 'assets/js/mm-library.js',
				[ 'jquery', 'jstree', 'media-editor' ],
				$this->version,
				true
			);

			// Sub-modules — each depends on the bootstrap handle.
			$sub_deps = [ 'jquery', 'jstree', 'media-editor', 'media-manager-library' ];

			wp_enqueue_script(
				'media-manager-library-tree',
				MM_PLUGIN_URL . 'assets/js/mm-library-tree.js',
				$sub_deps,
				$this->version,
				true
			);

			wp_enqueue_script(
				'media-manager-library-grid',
				MM_PLUGIN_URL . 'assets/js/mm-library-grid.js',
				$sub_deps,
				$this->version,
				true
			);

			wp_enqueue_script(
				'media-manager-library-files',
				MM_PLUGIN_URL . 'assets/js/mm-library-files.js',
				$sub_deps,
				$this->version,
				true
			);

			wp_localize_script(
				'media-manager-library',
				'mm_ajax',
				[
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( MM_NONCE ),
					'i18n'  => [
						'tree_error'           => __( 'Could not load folder tree.', 'media-manager' ),
						'hide_folder'          => __( 'Hide folder', 'media-manager' ),
						'delete_folder'        => __( 'Delete folder', 'media-manager' ),
						'new_folder'           => __( 'New folder', 'media-manager' ),
						'new_folder_name'      => __( 'New folder name:', 'media-manager' ),
						'confirm_delete_folder'=> __( 'Delete this folder? It must be empty.', 'media-manager' ),
						'confirm_delete_files' => __( 'Delete selected file(s)? This cannot be undone.', 'media-manager' ),
						'files'                => __( 'files', 'media-manager' ),
						'selected'             => __( 'selected', 'media-manager' ),
						'no_files'             => __( 'No files in this folder.', 'media-manager' ),
						'load_error'           => __( 'Could not load files.', 'media-manager' ),
						'error_create'         => __( 'Could not create folder.', 'media-manager' ),
						'error_delete'         => __( 'Could not delete.', 'media-manager' ),
						'protect_folder'       => __( 'Protect folder', 'media-manager' ),
						'unprotect_folder'     => __( 'Remove protection', 'media-manager' ),
						'error_protect'        => __( 'Could not update folder protection.', 'media-manager' ),
					'error_move'           => __( 'Some files could not be moved.', 'media-manager' ),
					'move_renamed'         => __( 'Renamed to avoid a conflict:', 'media-manager' ),
					'move_to_folder'       => __( '— Move to folder —', 'media-manager' ),
					'unassigned'           => __( 'Unassigned', 'media-manager' ),
					'orphans_heading'      => __( 'Unassigned files', 'media-manager' ),
					'recent_heading'       => __( 'Recent uploads', 'media-manager' ),
					'last_7_days'          => __( 'Last 7 days', 'media-manager' ),
					'last_30_days'         => __( 'Last 30 days', 'media-manager' ),
					'clear_filter'         => __( 'Clear filter', 'media-manager' ),
					'no_images'            => __( 'No images in this folder', 'media-manager' ),
				],
			]
		);
	}

		// -----------------------------------------------------------------------
		// Main admin stylesheet.
		// -----------------------------------------------------------------------
		$css_path = MM_PLUGIN_DIR . 'assets/css/admin.min.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'media-manager-admin',
				MM_PLUGIN_URL . 'assets/css/admin.min.css',
				[ 'jstree' ],
				$this->version
			);
		}

		// -----------------------------------------------------------------------
		// Page-specific JS files.
		// -----------------------------------------------------------------------
		$screen = get_current_screen();

		// Upload JS — Library screen only.
		if ( $screen && $screen->id === $this->library_hook ) {
			$upload_js = MM_PLUGIN_DIR . 'assets/js/mm-upload.js';
			if ( file_exists( $upload_js ) ) {
				wp_enqueue_script(
					'media-manager-upload',
					MM_PLUGIN_URL . 'assets/js/mm-upload.js',
					[ 'jquery', 'media-manager-library' ],
					$this->version,
					true
				);
			}
		}

		// Thumbnails JS.
		$thumbnails_hook = isset( $this->screen_hooks[1] ) ? $this->screen_hooks[1] : '';
		if ( $screen && $thumbnails_hook && $screen->id === $thumbnails_hook ) {
			$thumbnails_js = MM_PLUGIN_DIR . 'assets/js/mm-thumbnails.js';
			if ( file_exists( $thumbnails_js ) ) {
				wp_enqueue_script(
					'media-manager-thumbnails',
					MM_PLUGIN_URL . 'assets/js/mm-thumbnails.js',
					[ 'jquery' ],
					$this->version,
					true
				);
				wp_localize_script( 'media-manager-thumbnails', 'mm_ajax', [
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( MM_NONCE ),
					'i18n'  => [
						'preparing' => __( 'Preparing…', 'media-manager' ),
						'no_images' => __( 'No images to regenerate.', 'media-manager' ),
						'remaining' => __( 'remaining', 'media-manager' ),
						'done'      => __( 'Done!', 'media-manager' ),
						'error'     => __( 'Error', 'media-manager' ),
					],
				] );
			}
		}

		// BDA JS.
		$bda_hook = isset( $this->screen_hooks[2] ) ? $this->screen_hooks[2] : '';
		if ( $screen && $bda_hook && $screen->id === $bda_hook ) {
			$bda_js = MM_PLUGIN_DIR . 'assets/js/mm-bda.js';
			if ( file_exists( $bda_js ) ) {
				wp_enqueue_script(
					'media-manager-bda',
					MM_PLUGIN_URL . 'assets/js/mm-bda.js',
					[ 'jquery' ],
					$this->version,
					true
				);
				wp_localize_script( 'media-manager-bda', 'mm_ajax', [
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( MM_NONCE ),
					'i18n'  => [
						'no_protected' => __( 'No folders protected.', 'media-manager' ),
						'no_blocked'   => __( 'No blocked IPs.', 'media-manager' ),
						'unprotect'    => __( 'Remove', 'media-manager' ),
						'error_add_ip' => __( 'Could not add IP.', 'media-manager' ),
						'settings_saved' => __( 'Settings saved.', 'media-manager' ),
						'save_error'   => __( 'Save failed.', 'media-manager' ),
					],
				] );
			}
		}

		// -----------------------------------------------------------------------
		// Main admin script (phase ≥ 5 — was previously admin.min.js stub).
		// -----------------------------------------------------------------------
		$js_path = MM_PLUGIN_DIR . 'assets/js/admin.min.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'media-manager-admin',
				MM_PLUGIN_URL . 'assets/js/admin.min.js',
				[ 'jquery', 'jstree' ],
				$this->version,
				true
			);

			wp_localize_script(
				'media-manager-admin',
				'mm_ajax',
				[
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( MM_NONCE ),
				]
			);
		}
	}

	/**
	 * Enqueue the Media Manager folder browser on admin screens that use the
	 * WP media frame. Runs at priority 99 so earlier enqueue_media() calls are
	 * already registered before we check for them.
	 *
	 * Loads automatically on post/page edit screens. Also activates on any
	 * other admin screen where something else has already enqueued wp.media
	 * (Customizer controls, WooCommerce product images, etc.) — this provides
	 * the "nice to have" coverage without loading the media stack on unrelated
	 * pages.
	 *
	 * @param string $hook_suffix  Current admin page hook.
	 */
	public function enqueue_post_media( string $hook_suffix ): void {
		// Post/page edit screens always get the extension.
		$is_post_screen = in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true );

		// Other screens: only proceed if something else has already queued
		// wp.media (idempotent call below is fine, but no need to force-load
		// the media stack on irrelevant pages).
		$media_queued = wp_script_is( 'media-editor', 'enqueued' )
		             || wp_script_is( 'media-editor', 'to_do' );

		if ( ! $is_post_screen && ! $media_queued ) {
			return;
		}

		// Prime wp.media — idempotent, safe if already called.
		wp_enqueue_media();

		// jsTree — folder sidebar inside the modal.
		wp_enqueue_style(
			'jstree',
			MM_PLUGIN_URL . 'assets/vendor/jstree/style.min.css',
			[ 'dashicons' ],
			'3.3.16'
		);

		wp_enqueue_script(
			'jstree',
			MM_PLUGIN_URL . 'assets/vendor/jstree/jstree.min.js',
			[ 'jquery' ],
			'3.3.16',
			true
		);

		// Plugin styles.
		$css_min = MM_PLUGIN_DIR . 'assets/css/admin.min.css';
		$css_dev = MM_PLUGIN_DIR . 'assets/css/admin.css';
		$css_url = file_exists( $css_min )
			? MM_PLUGIN_URL . 'assets/css/admin.min.css'
			: MM_PLUGIN_URL . 'assets/css/admin.css';

		if ( file_exists( $css_min ) || file_exists( $css_dev ) ) {
			wp_enqueue_style(
				'media-manager-post',
				$css_url,
				[ 'jstree' ],
				$this->version
			);
		}

		// Folder browser frame extension.
		$pm_min = MM_PLUGIN_DIR . 'assets/js/mm-post-media.min.js';
		$pm_url = file_exists( $pm_min )
			? MM_PLUGIN_URL . 'assets/js/mm-post-media.min.js'
			: MM_PLUGIN_URL . 'assets/js/mm-post-media.js';

		wp_enqueue_script(
			'media-manager-post-media',
			$pm_url,
			[ 'jquery', 'media-editor', 'jstree' ],
			$this->version,
			true
		);

		wp_localize_script(
			'media-manager-post-media',
			'mm_ajax',
			[
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( MM_NONCE ),
			]
		);
	}
}
