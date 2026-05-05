<?php
declare(strict_types=1);
namespace MediaManager\FileSystem;

use MediaManager\Data\FileRepository;
use MediaManager\Data\FolderRepository;
use MediaManager\Helpers\PathHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SyncManager
 *
 * Phase 9: scans a folder on disk and ensures every file inside is tracked
 * in wp_mm_files with a corresponding WP attachment post.
 *
 * Design:
 *   - prepare()        → scans, builds queue, stores in transient, returns info.
 *   - process_chunk()  → imports one chunk of queued files.
 *   - full_scan()      → convenience wrapper used by the cron scheduler.
 */
final class SyncManager {

	/** Number of files processed per chunk. */
	const CHUNK_SIZE = 20;

	/** Transient prefix. Key = mm_sync_{folder_id}. */
	const TRANSIENT_PREFIX = 'mm_sync_';

	// -----------------------------------------------------------------------
	// Phase 9 — Chunked (AJAX-driven) sync
	// -----------------------------------------------------------------------

	/**
	 * Scan a folder's disk directory, find files not yet tracked, store a
	 * queue transient, and return metadata so the JS can start chunking.
	 *
	 * @return array{ queue: string[], total: int, chunks: int }|\WP_Error
	 */
	public function prepare( int $folder_id ): array|\WP_Error {
		$path = FolderRepository::get_path( $folder_id );

		if ( ! $path || ! is_dir( $path ) ) {
			return new \WP_Error( 'mm_no_dir', __( 'Folder directory not found.', 'media-manager' ) );
		}

		$untracked = $this->find_untracked_files( $folder_id, $path );
		$total     = count( $untracked );
		$chunks    = $total > 0 ? (int) ceil( $total / self::CHUNK_SIZE ) : 0;

		set_transient( self::TRANSIENT_PREFIX . $folder_id, $untracked, HOUR_IN_SECONDS );

		return [ 'queue' => $untracked, 'total' => $total, 'chunks' => $chunks ];
	}

	/**
	 * Process one chunk of previously queued files.
	 *
	 * @return array{ imported: int, remaining: int }|\WP_Error
	 */
	public function process_chunk( int $folder_id, int $chunk_index ): array|\WP_Error {
		$queue = get_transient( self::TRANSIENT_PREFIX . $folder_id );

		if ( false === $queue ) {
			return new \WP_Error( 'mm_no_queue', __( 'Sync queue expired. Please restart.', 'media-manager' ) );
		}

		$offset  = $chunk_index * self::CHUNK_SIZE;
		$slice   = array_slice( $queue, $offset, self::CHUNK_SIZE );
		$path    = FolderRepository::get_path( $folder_id );
		$imported = 0;

		foreach ( $slice as $abs_path ) {
			if ( $this->import_file( $abs_path, $folder_id, $path ) ) {
				$imported++;
			}
		}

		$remaining = max( 0, count( $queue ) - ( $offset + self::CHUNK_SIZE ) );

		// Clear transient when done.
		if ( $remaining === 0 ) {
			delete_transient( self::TRANSIENT_PREFIX . $folder_id );
		}

		return [ 'imported' => $imported, 'remaining' => $remaining ];
	}

	// -----------------------------------------------------------------------
	// Phase 9 — Full scan (used by cron)
	// -----------------------------------------------------------------------

	/**
	 * Synchronously scan all files in a folder directory and import any
	 * that are not yet tracked.  Safe to call from WP-Cron.
	 *
	 * @return int Number of files imported.
	 */
	public function full_scan( int $folder_id ): int {
		$path = FolderRepository::get_path( $folder_id );

		if ( ! $path || ! is_dir( $path ) ) {
			return 0;
		}

		$untracked = $this->find_untracked_files( $folder_id, $path );
		$imported  = 0;

		foreach ( $untracked as $abs_path ) {
			if ( $this->import_file( $abs_path, $folder_id, $path ) ) {
				$imported++;
			}
		}

		return $imported;
	}

	// -----------------------------------------------------------------------
	// Private helpers
	// -----------------------------------------------------------------------

	/**
	 * Return absolute paths of files in $dir that are not in mm_files.
	 *
	 * @return string[]
	 */
	private function find_untracked_files( int $folder_id, string $dir ): array {
		$entries = @scandir( $dir );
		if ( false === $entries ) {
			return [];
		}

		$upload_base = PathHelper::upload_basedir();
		$untracked   = [];

		foreach ( $entries as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$abs = $dir . '/' . $entry;

			if ( is_dir( $abs ) ) {
				continue; // folders handled separately
			}

			// Skip non-media files.
			if ( ! $this->is_media_file( $entry ) ) {
				continue;
			}

			$relative = ltrim( str_replace( $upload_base, '', $abs ), '/' );

			// Look up by relative path in wp_posts + postmeta.
			$attachment_id = $this->find_attachment_by_path( $relative );

			if ( $attachment_id && ! FileRepository::exists( $attachment_id ) ) {
				FileRepository::insert( $attachment_id, $folder_id );
			} elseif ( ! $attachment_id ) {
				// File exists on disk but has no attachment post yet.
				$untracked[] = $abs;
			}
		}

		return $untracked;
	}

	/**
	 * Create a WP attachment post for a raw file and track it.
	 */
	private function import_file( string $abs_path, int $folder_id, string $folder_dir ): bool {
		if ( ! file_exists( $abs_path ) || ! is_file( $abs_path ) ) {
			return false;
		}

		$upload_base = PathHelper::upload_basedir();
		$relative    = ltrim( str_replace( $upload_base, '', $abs_path ), '/' );
		$url         = PathHelper::path_to_url( $abs_path );
		$filetype    = wp_check_filetype( basename( $abs_path ), null );

		if ( ! $filetype['type'] ) {
			return false;
		}

		$attachment = [
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $abs_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		$attachment_id = wp_insert_attachment( $attachment, $abs_path );

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return false;
		}

		// Generate metadata (thumbnails etc.)
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $abs_path );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		FileRepository::insert( $attachment_id, $folder_id );

		return true;
	}

	/**
	 * Find an attachment post ID by its relative upload path.
	 *
	 * @return int  0 if not found.
	 */
	private function find_attachment_by_path( string $relative ): int {
		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attached_file'
			   AND meta_value = %s
			 LIMIT 1",
			$relative
		) );

		return $id ? (int) $id : 0;
	}

	/**
	 * Very lightweight check — is this filename a common media file?
	 */
	private function is_media_file( string $filename ): bool {
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		return in_array( $ext, [
			'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif',
			'svg', 'pdf', 'mp4', 'mp3', 'mov', 'ogg', 'webm',
			'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
			'zip', 'gz',
		], true );
	}
}
