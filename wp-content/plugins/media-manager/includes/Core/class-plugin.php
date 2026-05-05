<?php
declare(strict_types=1);
namespace MediaManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin
 *
 * Bootstrap class. Wires all modules to the Loader and fires the Loader once
 * everything is registered. This is the only place add_action / add_filter
 * calls are accumulated; individual class constructors must not call them
 * directly.
 */
final class Plugin {

	private Loader $loader;

	// -----------------------------------------------------------------------

	public function __construct() {
		$this->loader = new Loader();
		$this->register_hooks();
		$this->load_modules();
	}

	// -----------------------------------------------------------------------

	/**
	 * Activation / deactivation hooks must be registered before the Loader
	 * runs, so they are wired here directly rather than via the Loader.
	 */
	private function register_hooks(): void {
		register_activation_hook(
			MM_PLUGIN_DIR . 'media-manager.php',
			[ Activator::class, 'activate' ]
		);

		register_deactivation_hook(
			MM_PLUGIN_DIR . 'media-manager.php',
			[ Deactivator::class, 'deactivate' ]
		);

		// Run a one-time data-integrity cleanup (dedup folder posts) whenever
		// the stored stamp doesn't match MM_VERSION.  Cheap no-op once clean.
		$this->loader->add_action( 'admin_init', $this, 'maybe_run_integrity_check' );
	}

	// -----------------------------------------------------------------------

	/**
	 * Instantiate all feature modules and pass the Loader so each can
	 * accumulate its hooks. Modules are added here in dependency order.
	 *
	 * Phases 3–12 will add their module instantiations here as they are built.
	 */
	private function load_modules(): void {
		// -----------------------------------------------------------------------
		// Phase 2 — CPT & Data Layer
		// -----------------------------------------------------------------------

		// Helpers (no hooks, loaded for availability).
		require_once MM_PLUGIN_DIR . 'includes/Helpers/class-path-helper.php';

		// Repositories (no hooks, loaded for availability by other modules).
		require_once MM_PLUGIN_DIR . 'includes/Data/class-folder-repository.php';
		require_once MM_PLUGIN_DIR . 'includes/Data/class-file-repository.php';
		require_once MM_PLUGIN_DIR . 'includes/Data/class-protected-repository.php';
		require_once MM_PLUGIN_DIR . 'includes/Data/class-ip-repository.php';

		// CPT registration — registers mm_folder on 'init'.
		require_once MM_PLUGIN_DIR . 'includes/CPT/class-folder-post.php';
		new \MediaManager\CPT\FolderPost( $this->loader );

		// -----------------------------------------------------------------------
		// Phase 3 — Admin Shell (menu, pages, assets)
		// Admin is only needed in the WordPress admin context.
		// -----------------------------------------------------------------------
		if ( is_admin() ) {
			require_once MM_PLUGIN_DIR . 'includes/Admin/Pages/class-library-page.php';
			require_once MM_PLUGIN_DIR . 'includes/Admin/Pages/class-settings-page.php';
			require_once MM_PLUGIN_DIR . 'includes/Admin/Pages/class-thumbnails-page.php';
			require_once MM_PLUGIN_DIR . 'includes/Admin/Pages/class-bda-page.php';
			require_once MM_PLUGIN_DIR . 'includes/Admin/class-assets.php';
			require_once MM_PLUGIN_DIR . 'includes/Admin/class-menu.php';
			require_once MM_PLUGIN_DIR . 'includes/Admin/class-admin.php';

			new \MediaManager\Admin\Admin( $this->loader );
		}

		// -----------------------------------------------------------------------
		// Phase 4 — Folder Tree & Navigation
		// AJAX handlers run on both admin and admin-ajax contexts.
		// -----------------------------------------------------------------------
		require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-folder-manager.php';
		require_once MM_PLUGIN_DIR . 'includes/Ajax/class-ajax-handler.php';

		$folder_manager = new \MediaManager\FileSystem\FolderManager();
		new \MediaManager\Ajax\AjaxHandler( $this->loader, $folder_manager );

		// -----------------------------------------------------------------------
		// Phase 5 — FileManager + LinkUpdater
		// -----------------------------------------------------------------------
		require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-file-manager.php';
		require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-link-updater.php';

		// -----------------------------------------------------------------------
		// Phase 8 — Scheduler
		// -----------------------------------------------------------------------
		require_once MM_PLUGIN_DIR . 'includes/Core/class-scheduler.php';
		( new \MediaManager\Core\Scheduler() )->register( $this->loader );

		// -----------------------------------------------------------------------
		// Phase 9 — SyncManager
		// -----------------------------------------------------------------------
		require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-sync-manager.php';

		// -----------------------------------------------------------------------
		// Phase 11 — RegenManager
		// -----------------------------------------------------------------------
		require_once MM_PLUGIN_DIR . 'includes/Thumbnails/class-regen-manager.php';

		// -----------------------------------------------------------------------
		// Phase 12 — BDA + IP Blocking
		// -----------------------------------------------------------------------
		require_once MM_PLUGIN_DIR . 'includes/Security/class-bda-manager.php';
		require_once MM_PLUGIN_DIR . 'includes/Security/class-ip-blocker.php';
		( new \MediaManager\Security\IpBlocker() )->register( $this->loader );
	}

	// -----------------------------------------------------------------------

	/**
	 * Runs on admin_init. Executes deduplication once per plugin version
	 * (stored in mm_integrity_check option). Cheap query when already clean.
	 */
	public function maybe_run_integrity_check(): void {
		if ( get_option( 'mm_integrity_check' ) === MM_VERSION ) {
			return;
		}

		require_once MM_PLUGIN_DIR . 'includes/Data/class-folder-repository.php';
		\MediaManager\Data\FolderRepository::deduplicate_folders();

		update_option( 'mm_integrity_check', MM_VERSION );
	}

	// -----------------------------------------------------------------------

	/**
	 * Entry point. Called directly from the plugin root file.
	 */
	public static function run(): void {
		$plugin = new self();
		$plugin->loader->run();

		/**
		 * Fires after all Media Manager hooks have been registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'mm_loaded' );
	}
}
