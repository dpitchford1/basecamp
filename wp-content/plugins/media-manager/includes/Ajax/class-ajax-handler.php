<?php
declare(strict_types=1);
namespace MediaManager\Ajax;

use MediaManager\Core\Loader;
use MediaManager\FileSystem\FolderManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AjaxHandler
 *
 * Thin action-registration orchestrator.
 * Instantiates focused handler classes (one per functional area) and wires
 * them to their wp_ajax_mm_* hooks via the Loader.
 *
 * Handler classes live in includes/Ajax/Handlers/ and share the AjaxHelpers
 * trait for verify() and build_file_data().
 */
final class AjaxHandler {

	public function __construct( Loader $loader, FolderManager $folder_manager ) {

		$dir = MM_PLUGIN_DIR . 'includes/Ajax/Handlers/';

		require_once $dir . 'trait-ajax-helpers.php';
		require_once $dir . 'class-library-handler.php';
		require_once $dir . 'class-file-handler.php';
		require_once $dir . 'class-folder-handler.php';
		require_once $dir . 'class-settings-handler.php';
		require_once $dir . 'class-thumbnail-handler.php';
		require_once $dir . 'class-security-handler.php';

		$lib  = new Handlers\LibraryHandler();
		$file = new Handlers\FileHandler();
		$fold = new Handlers\FolderHandler( $folder_manager );
		$set  = new Handlers\SettingsHandler();
		$thm  = new Handlers\ThumbnailHandler();
		$sec  = new Handlers\SecurityHandler();

		// Phase 4 — Folder tree + folder meta.
		$loader->add_action( 'wp_ajax_mm_folder_tree',  $lib, 'folder_tree' );
		$loader->add_action( 'wp_ajax_mm_load_folder',  $lib, 'load_folder' );

		// Phase 5 — File grid + sort preference.
		$loader->add_action( 'wp_ajax_mm_folder_contents', $lib, 'folder_contents' );
		$loader->add_action( 'wp_ajax_mm_sort_contents',   $lib, 'sort_contents' );

		// Phase 6 — Upload.
		$loader->add_action( 'wp_ajax_mm_upload_file', $file, 'upload_file' );

		// Phase 7 — File mutations.
		$loader->add_action( 'wp_ajax_mm_move_copy_file', $file, 'move_copy_file' );
		$loader->add_action( 'wp_ajax_mm_rename_file',    $file, 'rename_file' );
		$loader->add_action( 'wp_ajax_mm_delete_files',   $file, 'delete_files' );

		// Phase 8 — Folder operations.
		$loader->add_action( 'wp_ajax_mm_create_folder',   $fold, 'create_folder' );
		$loader->add_action( 'wp_ajax_mm_delete_folder',   $fold, 'delete_folder' );
		$loader->add_action( 'wp_ajax_mm_hide_folder',     $fold, 'hide_folder' );
		$loader->add_action( 'wp_ajax_mm_refresh_folders', $fold, 'refresh_folders' );

		// Phase 9 — Sync.
		$loader->add_action( 'wp_ajax_mm_sync_folder', $fold, 'sync_folder' );
		$loader->add_action( 'wp_ajax_mm_sync_chunk',  $fold, 'sync_chunk' );

		// Phase 10 — Settings.
		$loader->add_action( 'wp_ajax_mm_save_settings', $set, 'save_settings' );

		// Phase 11 — Thumbnail regeneration.
		$loader->add_action( 'wp_ajax_mm_regen_thumbnails', $thm, 'regen_thumbnails' );
		$loader->add_action( 'wp_ajax_mm_regen_process',    $thm, 'regen_process' );

		// Phase 12 — Block Direct Access + IP blocking.
		$loader->add_action( 'wp_ajax_mm_toggle_file_access',  $sec, 'toggle_file_access' );
		$loader->add_action( 'wp_ajax_mm_get_protected_files', $sec, 'get_protected_files' );
		$loader->add_action( 'wp_ajax_mm_add_blocked_ip',      $sec, 'add_blocked_ip' );
		$loader->add_action( 'wp_ajax_mm_remove_blocked_ips',  $sec, 'remove_blocked_ips' );
		$loader->add_action( 'wp_ajax_mm_get_blocked_ips',     $sec, 'get_blocked_ips' );
		$loader->add_action( 'wp_ajax_mm_save_bda_settings',   $sec, 'save_bda_settings' );

		// Phase 13 — Folder thumbnails, recent files, orphan detection.
		$loader->add_action( 'wp_ajax_mm_folder_thumbs', $lib, 'folder_thumbs' );
		$loader->add_action( 'wp_ajax_mm_recent_files',  $lib, 'recent_files' );
		$loader->add_action( 'wp_ajax_mm_get_orphans',   $lib, 'get_orphans' );
	}
}
