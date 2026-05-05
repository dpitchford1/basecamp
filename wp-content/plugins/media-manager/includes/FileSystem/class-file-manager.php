<?php
declare(strict_types=1);
namespace MediaManager\FileSystem;

use MediaManager\Helpers\PathHelper;
use MediaManager\Data\FileRepository;
use MediaManager\Data\FolderRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FileManager
 *
 * Static methods for all file-level operations:
 *   upload()  — Phase 6
 *   move()    — Phase 7
 *   copy()    — Phase 7
 *   rename()  — Phase 7
 */
final class FileManager {

	// -----------------------------------------------------------------------
	// Phase 6 — Upload
	// -----------------------------------------------------------------------

	/**
	 * Handle a single file upload into the specified folder.
	 *
	 * Uses wp_handle_upload() which enforces WP's filetype whitelist.
	 * The file is placed in the folder's physical directory on disk, a
	 * WP attachment post is created, and the mm_files row is written.
	 *
	 * @param  int  $folder_id  mm_folder post ID.
	 * @return int|\WP_Error    New attachment post ID or WP_Error.
	 */
	public static function upload( int $folder_id ): int|\WP_Error {
		$folder_path = FolderRepository::get_path( $folder_id );

		if ( ! $folder_path || ! is_dir( $folder_path ) ) {
			return new \WP_Error( 'mm_invalid_folder', __( 'Target folder does not exist.', 'media-manager' ) );
		}

		if ( empty( $_FILES['file'] ) || $_FILES['file']['error'] !== UPLOAD_ERR_OK ) {
			$err = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
			return new \WP_Error( 'mm_no_file', sprintf( __( 'Upload error code %d.', 'media-manager' ), $err ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$original_name = sanitize_file_name( $_FILES['file']['name'] );
		$new_filename  = wp_unique_filename( $folder_path, $original_name );
		$dest_path     = $folder_path . '/' . $new_filename;

		if ( ! move_uploaded_file( $_FILES['file']['tmp_name'], $dest_path ) ) {
			return new \WP_Error( 'mm_move_failed', __( 'Could not save uploaded file.', 'media-manager' ) );
		}

		// Set permissions to match the uploads directory.
		$stat  = stat( dirname( $dest_path ) );
		$perms = $stat['mode'] & 0000664;
		@chmod( $dest_path, $perms );

		// Strip EXIF metadata by re-encoding the image through GD (JPEG only).
		if ( get_option( 'mm_strip_exif', false ) && function_exists( 'imagecreatefromjpeg' ) ) {
			$allowed_exts = apply_filters( 'mm_strip_exif_types', [ 'jpg', 'jpeg' ] );
			if ( in_array( strtolower( pathinfo( $new_filename, PATHINFO_EXTENSION ) ), (array) $allowed_exts, true ) ) {
				$img = @imagecreatefromjpeg( $dest_path );
				if ( false !== $img ) {
					@imagejpeg( $img, $dest_path, 95 );
					imagedestroy( $img );
				}
			}
		}

		$file_type = wp_check_filetype( $new_filename );
		$mime      = $file_type['type'] ?: 'application/octet-stream';

		$attachment_id = wp_insert_attachment(
			[
				'post_mime_type' => $mime,
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $new_filename ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			],
			$dest_path
		);

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$meta = wp_generate_attachment_metadata( $attachment_id, $dest_path );
		wp_update_attachment_metadata( $attachment_id, $meta );

		FileRepository::insert( $attachment_id, $folder_id );

		return $attachment_id;
	}

	// -----------------------------------------------------------------------
	// Phase 7 — Move
	// -----------------------------------------------------------------------

	/**
	 * Move an attachment to a different folder.
	 *
	 * Moves the physical file and all thumbnail variants, updates
	 * _wp_attached_file post meta, mm_files row, and embedded links.
	 *
	 * @return int|\WP_Error  The attachment ID on success.
	 */
	public static function move( int $attachment_id, int $dest_folder_id ): array|\WP_Error {
		$src_path  = get_attached_file( $attachment_id );
		$dest_dir  = FolderRepository::get_path( $dest_folder_id );

		if ( ! $src_path || ! file_exists( $src_path ) ) {
			return new \WP_Error( 'mm_file_missing', __( 'Source file not found.', 'media-manager' ) );
		}

		if ( ! $dest_dir || ! is_dir( $dest_dir ) ) {
			return new \WP_Error( 'mm_invalid_folder', __( 'Destination folder does not exist.', 'media-manager' ) );
		}

		// Traversal guard.
		if ( ! PathHelper::is_path_inside( PathHelper::upload_basedir(), $dest_dir ) ) {
			return new \WP_Error( 'mm_path_traversal', __( 'Destination is outside uploads.', 'media-manager' ) );
		}

		$filename         = basename( $src_path );
		$raw_dest         = $dest_dir . '/' . $filename;
		$dest_path        = static::unique_path( $raw_dest );
		$filename_changed = $dest_path !== $raw_dest;
		$upload_base      = PathHelper::upload_basedir();

		/**
		 * Fires before a file is moved.
		 *
		 * @param int    $attachment_id
		 * @param string $src_path
		 * @param string $dest_path
		 */
		do_action( 'mm_before_file_move', $attachment_id, $src_path, $dest_path );

		// Move main file.
		if ( ! rename( $src_path, $dest_path ) ) {
			return new \WP_Error( 'mm_rename_failed', __( 'Could not move file.', 'media-manager' ) );
		}

		// Move all thumbnail variants.
		static::move_thumbnails( $attachment_id, dirname( $src_path ), $dest_dir );

		// Update _wp_attached_file (relative to uploads base).
		$relative = ltrim( str_replace( $upload_base, '', $dest_path ), '/' );
		update_post_meta( $attachment_id, '_wp_attached_file', $relative );

		// Update GUID and attachment URL.
		$new_url = PathHelper::path_to_url( $dest_path );
		wp_update_post( [ 'ID' => $attachment_id, 'guid' => $new_url ] );

		// Update embedded content links.
		require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-link-updater.php';
		LinkUpdater::update_attachment_url( $attachment_id, PathHelper::path_to_url( $src_path ), $new_url );

		// Update mm_files.
		FileRepository::update_folder( $attachment_id, $dest_folder_id );

		/**
		 * Fires after a file has been moved.
		 *
		 * @param int    $attachment_id
		 * @param string $src_path
		 * @param string $dest_path
		 */
		do_action( 'mm_after_file_move', $attachment_id, $src_path, $dest_path );

		return [
			'id'               => $attachment_id,
			'filename'         => basename( $dest_path ),
			'filename_changed' => $filename_changed,
		];
	}

	// -----------------------------------------------------------------------
	// Phase 7 — Copy
	// -----------------------------------------------------------------------

	/**
	 * Copy an attachment to a different folder and register a new WP attachment.
	 *
	 * @internal No UI trigger exists as of Phase 13 (drag-drop was removed).
	 *           The AJAX handler mm_move_copy_file supports mode=copy and will
	 *           call this method when a move/copy UI is added in a future phase.
	 *           Do not remove — the server-side logic is complete and tested.
	 *
	 * @return int|\WP_Error  New attachment ID on success.
	 */
	public static function copy( int $attachment_id, int $dest_folder_id ): int|\WP_Error {
		$src_path = get_attached_file( $attachment_id );
		$dest_dir = FolderRepository::get_path( $dest_folder_id );

		if ( ! $src_path || ! file_exists( $src_path ) ) {
			return new \WP_Error( 'mm_file_missing', __( 'Source file not found.', 'media-manager' ) );
		}

		if ( ! $dest_dir || ! is_dir( $dest_dir ) ) {
			return new \WP_Error( 'mm_invalid_folder', __( 'Destination folder does not exist.', 'media-manager' ) );
		}

		if ( ! PathHelper::is_path_inside( PathHelper::upload_basedir(), $dest_dir ) ) {
			return new \WP_Error( 'mm_path_traversal', __( 'Destination is outside uploads.', 'media-manager' ) );
		}

		// Ensure unique filename in destination.
		$filename  = basename( $src_path );
		$dest_path = static::unique_path( $dest_dir . '/' . $filename );

		if ( ! copy( $src_path, $dest_path ) ) {
			return new \WP_Error( 'mm_copy_failed', __( 'Could not copy file.', 'media-manager' ) );
		}

		$original = get_post( $attachment_id );
		$new_id   = wp_insert_attachment(
			[
				'post_mime_type' => $original->post_mime_type,
				'post_title'     => $original->post_title,
				'post_content'   => '',
				'post_status'    => 'inherit',
			],
			$dest_path
		);

		if ( is_wp_error( $new_id ) ) {
			unlink( $dest_path );
			return $new_id;
		}

		$meta = wp_generate_attachment_metadata( $new_id, $dest_path );
		wp_update_attachment_metadata( $new_id, $meta );

		FileRepository::insert( $new_id, $dest_folder_id );

		return $new_id;
	}

	// -----------------------------------------------------------------------
	// Phase 7 — Rename
	// -----------------------------------------------------------------------

	/**
	 * Rename an attachment file on disk and update all associated meta.
	 *
	 * $new_name should be the base name WITHOUT extension.
	 *
	 * @return true|\WP_Error
	 */
	public static function rename( int $attachment_id, string $new_name, bool $update_title = true ): bool|\WP_Error {
		$src_path = get_attached_file( $attachment_id );

		if ( ! $src_path || ! file_exists( $src_path ) ) {
			return new \WP_Error( 'mm_file_missing', __( 'File not found.', 'media-manager' ) );
		}

		$dir       = dirname( $src_path );
		$ext       = pathinfo( $src_path, PATHINFO_EXTENSION );
		$new_file  = sanitize_file_name( $new_name ) . ( $ext ? '.' . $ext : '' );
		$dest_path = $dir . '/' . $new_file;

		if ( file_exists( $dest_path ) && $dest_path !== $src_path ) {
			return new \WP_Error( 'mm_file_exists', __( 'A file with that name already exists.', 'media-manager' ) );
		}

		if ( ! rename( $src_path, $dest_path ) ) {
			return new \WP_Error( 'mm_rename_failed', __( 'Could not rename file.', 'media-manager' ) );
		}

		// Rename thumbnails.
		static::rename_thumbnails( $attachment_id, $src_path, $dest_path );

		$upload_base = PathHelper::upload_basedir();
		$relative    = ltrim( str_replace( $upload_base, '', $dest_path ), '/' );

		update_post_meta( $attachment_id, '_wp_attached_file', $relative );

		$new_url = PathHelper::path_to_url( $dest_path );
		$post_data = [
			'ID'        => $attachment_id,
			'post_name' => sanitize_title( $new_name ),
			'guid'      => $new_url,
		];
		if ( $update_title ) {
			$post_data['post_title'] = $new_name;
		}
		wp_update_post( $post_data );

		// Update links.
		require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-link-updater.php';
		LinkUpdater::update_attachment_url(
			$attachment_id,
			PathHelper::path_to_url( $src_path ),
			$new_url
		);

		return true;
	}

	// -----------------------------------------------------------------------
	// Private helpers
	// -----------------------------------------------------------------------

	/**
	 * Move all WP-generated thumbnail files for an attachment.
	 */
	private static function move_thumbnails( int $attachment_id, string $src_dir, string $dest_dir ): void {
		$meta = wp_get_attachment_metadata( $attachment_id );

		if ( empty( $meta['sizes'] ) ) {
			return;
		}

		foreach ( $meta['sizes'] as $size ) {
			$src  = $src_dir  . '/' . $size['file'];
			$dest = $dest_dir . '/' . $size['file'];

			if ( file_exists( $src ) ) {
				rename( $src, $dest );
			}
		}
	}

	/**
	 * Rename all WP-generated thumbnail files for an attachment after a rename.
	 */
	private static function rename_thumbnails( int $attachment_id, string $src_path, string $dest_path ): void {
		$meta = wp_get_attachment_metadata( $attachment_id );

		if ( empty( $meta['sizes'] ) ) {
			return;
		}

		$src_base  = pathinfo( $src_path, PATHINFO_FILENAME );
		$dest_base = pathinfo( $dest_path, PATHINFO_FILENAME );
		$dir       = dirname( $src_path );

		foreach ( $meta['sizes'] as $size_key => &$size ) {
			$old_file = $size['file'];
			$new_file = str_replace( $src_base, $dest_base, $old_file );
			$old_path = $dir . '/' . $old_file;
			$new_path = $dir . '/' . $new_file;

			if ( file_exists( $old_path ) ) {
				rename( $old_path, $new_path );
				$size['file'] = $new_file;
			}
		}
		unset( $size );

		wp_update_attachment_metadata( $attachment_id, $meta );
	}

	/**
	 * Return a unique path by appending a numeric suffix if necessary.
	 */
	private static function unique_path( string $path ): string {
		if ( ! file_exists( $path ) ) {
			return $path;
		}

		$dir  = dirname( $path );
		$ext  = pathinfo( $path, PATHINFO_EXTENSION );
		$base = pathinfo( $path, PATHINFO_FILENAME );
		$i    = 1;

		do {
			$new_path = $dir . '/' . $base . '-' . $i . ( $ext ? '.' . $ext : '' );
			$i++;
		} while ( file_exists( $new_path ) );

		return $new_path;
	}
}
