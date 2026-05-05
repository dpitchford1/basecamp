<?php
declare(strict_types=1);
namespace MediaManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activator
 *
 * Runs on plugin activation:
 *  - Creates the three custom DB tables via dbDelta (idempotent).
 *  - Seeds default options (add_option is a no-op if the key already exists).
 *  - Schedules the daily folder-scan cron event.
 *  - Stores the plugin version.
 */
final class Activator {

	/**
	 * Called via register_activation_hook in Plugin::run().
	 */
	public static function activate(): void {
		self::create_tables();
		self::seed_options();
		self::schedule_cron();
		self::scan_existing_uploads();
		update_option( 'mm_version', MM_VERSION );
	}

	// -----------------------------------------------------------------------
	// Tables
	// -----------------------------------------------------------------------

	private static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		// -------------------------------------------------------------------
		// {prefix}mm_files — maps each attachment to its folder
		// -------------------------------------------------------------------
		$files_table = $wpdb->prefix . 'mm_files';
		dbDelta( "CREATE TABLE {$files_table} (
  attachment_id bigint(20) UNSIGNED NOT NULL,
  folder_id     bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY  (attachment_id),
  KEY folder_id (folder_id)
) {$charset};" );

		// -------------------------------------------------------------------
		// {prefix}mm_protected — tracks files in the protected directory
		// -------------------------------------------------------------------
		$protected_table = $wpdb->prefix . 'mm_protected';
		dbDelta( "CREATE TABLE {$protected_table} (
  id            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  attachment_id bigint(20) UNSIGNED NOT NULL,
  created_at    timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY attachment_id (attachment_id)
) {$charset};" );

		// -------------------------------------------------------------------
		// {prefix}mm_blocked_ips — IP addresses denied access to protected content
		// varchar(45) supports full IPv6 notation
		// -------------------------------------------------------------------
		$ips_table = $wpdb->prefix . 'mm_blocked_ips';
		dbDelta( "CREATE TABLE {$ips_table} (
  id         bigint(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
  ip_address varchar(45) NOT NULL,
  created_at timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY ip_address (ip_address)
) {$charset};" );
	}

	// -----------------------------------------------------------------------
	// Default options
	// -----------------------------------------------------------------------

	private static function seed_options(): void {
		// add_option() is a no-op when the key already exists — safe to re-run.
		// autoload=true (default) ensures all these are fetched in WP's bulk
		// options query at boot — zero per-option DB hits on the frontend.
		add_option( 'mm_items_per_page',         500,      '', true );
		add_option( 'mm_move_or_copy',           'move',   '', true );
		add_option( 'mm_disable_scaling',        false,    '', true );
		add_option( 'mm_skip_webp',              false,    '', true );
		add_option( 'mm_bda_enabled',            false,    '', true );
		add_option( 'mm_bda_prevent_listing',    false,    '', true );
		add_option( 'mm_bda_prevent_hotlinking', false,    '', true );
		add_option( 'mm_ip_blocking_enabled',    false,    '', true );
		add_option( 'mm_bda_user_role',          'admins', '', true );
		add_option( 'mm_bda_no_access_page_id',  0,        '', true );
		add_option( 'mm_upload_folder_name',     'uploads','', true );
		add_option( 'mm_upload_folder_id',       0,        '', true );
	}

	// -----------------------------------------------------------------------
	// Cron
	// -----------------------------------------------------------------------

	private static function schedule_cron(): void {
		if ( ! wp_next_scheduled( 'mm_folder_scan' ) ) {
			wp_schedule_event( time(), 'daily', 'mm_folder_scan' );
		}
	}

	// -----------------------------------------------------------------------
	// Initial uploads scan (fresh install only)
	// -----------------------------------------------------------------------

	/**
	 * On a fresh activation (mm_upload_folder_id === 0), walk the uploads
	 * directory tree, create mm_folder CPT posts for every physical directory,
	 * then map all existing attachment posts to their discovered folder.
	 *
	 * Guard: if mm_upload_folder_id is already non-zero we have already run —
	 * skip silently so re-activations and updates never clobber existing data.
	 */
	private static function scan_existing_uploads(): void {
		if ( (int) get_option( 'mm_upload_folder_id', 0 ) !== 0 ) {
			return;
		}

		// Require the repository and helper classes — they are not yet
		// wired via load_modules() at activation time.
		require_once MM_PLUGIN_DIR . 'includes/Helpers/class-path-helper.php';
		require_once MM_PLUGIN_DIR . 'includes/Data/class-folder-repository.php';
		require_once MM_PLUGIN_DIR . 'includes/Data/class-file-repository.php';

		$upload_base = \MediaManager\Helpers\PathHelper::upload_basedir();

		if ( ! is_dir( $upload_base ) ) {
			return;
		}

		// Build the root uploads folder post.
		$root_name = basename( $upload_base );
		$root_id   = \MediaManager\Data\FolderRepository::insert( $root_name, 0, $upload_base );

		if ( ! $root_id ) {
			return;
		}

		update_option( 'mm_upload_folder_id', $root_id );

		// Walk every subdirectory and create CPT posts, building a path → ID map.
		$path_to_id = [ $upload_base => $root_id ];

		self::walk_directory( $upload_base, $root_id, $path_to_id );

		// Map all existing attachments to their folder.
		self::map_existing_attachments( $path_to_id, $upload_base, $root_id );
	}

	/**
	 * Recursively walk a directory, creating mm_folder posts for each
	 * subdirectory and populating $path_to_id by reference.
	 *
	 * WordPress date-based year/month directories (e.g. 2024/03) are included
	 * so every file has a proper home, but they are not hidden — the admin UI
	 * will present them alongside custom folders.
	 *
	 * @param array<string, int> &$path_to_id  Running map of absolute path → post ID.
	 */
	private static function walk_directory( string $dir, int $parent_id, array &$path_to_id ): void {
		$entries = @scandir( $dir );

		if ( false === $entries ) {
			return;
		}

		foreach ( $entries as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$full_path = wp_normalize_path( $dir . '/' . $entry );

			if ( ! is_dir( $full_path ) ) {
				continue;
			}

			// Skip hidden / dot directories.
			if ( str_starts_with( $entry, '.' ) ) {
				continue;
			}

			$folder_id = \MediaManager\Data\FolderRepository::insert( $entry, $parent_id, $full_path );

			if ( ! $folder_id ) {
				continue;
			}

			$path_to_id[ $full_path ] = $folder_id;

			self::walk_directory( $full_path, $folder_id, $path_to_id );
		}
	}

	/**
	 * Query all attachment posts, resolve their directory from _wp_attached_file
	 * meta, and insert a row into mm_files for each.
	 *
	 * @param array<string, int> $path_to_id  Path → folder post ID map.
	 */
	private static function map_existing_attachments(
		array $path_to_id,
		string $upload_base,
		int $root_id
	): void {
		global $wpdb;

		// Fetch all attachment IDs with their relative file paths in one query.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results(
			"SELECT p.ID as attachment_id, pm.meta_value as relative_path
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_wp_attached_file'
			 WHERE p.post_type = 'attachment'
			   AND p.post_status != 'trash'",
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return;
		}

		$map = []; // attachment_id => folder_id

		foreach ( (array) $rows as $row ) {
			$attachment_id = (int) $row['attachment_id'];
			$relative      = ltrim( $row['relative_path'], '/' );

			// The relative path includes the filename — get the parent dir.
			$file_dir = wp_normalize_path( $upload_base . '/' . dirname( $relative ) );

			if ( isset( $path_to_id[ $file_dir ] ) ) {
				$map[ $attachment_id ] = $path_to_id[ $file_dir ];
			} else {
				// File is in the root uploads dir or an unrecognised path.
				$map[ $attachment_id ] = $root_id;
			}
		}

		\MediaManager\Data\FileRepository::bulk_insert( $map );
	}
}