<?php
declare(strict_types=1);
namespace MediaManager\Security;

use MediaManager\Data\ProtectedRepository;
use MediaManager\Data\FolderRepository;
use MediaManager\Helpers\PathHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BdaManager (Broken Direct Access)
 *
 * Phase 12 — Manages server-level protection for folders marked as
 * "broken direct access" by writing/removing .htaccess rules and
 * maintaining the mm_protected DB table.
 *
 * Works on Apache (mod_rewrite) only; Nginx sites must add rules manually.
 */
final class BdaManager {

	/** Contents written to a protected folder's .htaccess. */
	const HTACCESS_BLOCK = <<<'HTACCESS'
# Media Manager — Broken Direct Access
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ - [F,L]
</IfModule>
HTACCESS;

	// -----------------------------------------------------------------------
	// Protect
	// -----------------------------------------------------------------------

	/**
	 * Write .htaccess to a folder and record it in the DB.
	 *
	 * @return true|\WP_Error
	 */
	public function protect_folder( int $folder_id, string $path ): bool|\WP_Error {
		if ( ! PathHelper::is_path_inside( PathHelper::upload_basedir(), $path ) ) {
			return new \WP_Error( 'mm_path_traversal', __( 'Path is outside uploads directory.', 'media-manager' ) );
		}

		if ( ! is_dir( $path ) ) {
			return new \WP_Error( 'mm_no_dir', __( 'Folder directory not found.', 'media-manager' ) );
		}

		$htaccess = trailingslashit( $path ) . '.htaccess';

		if ( file_put_contents( $htaccess, self::HTACCESS_BLOCK ) === false ) {
			return new \WP_Error( 'mm_write_failed', __( 'Could not write .htaccess file.', 'media-manager' ) );
		}

		ProtectedRepository::insert( $folder_id );

		return true;
	}

	// -----------------------------------------------------------------------
	// Unprotect
	// -----------------------------------------------------------------------

	/**
	 * Remove the .htaccess from a folder and remove its DB record.
	 *
	 * @return true|\WP_Error
	 */
	public function unprotect_folder( int $folder_id, string $path ): bool|\WP_Error {
		$htaccess = trailingslashit( $path ) . '.htaccess';

		if ( file_exists( $htaccess ) ) {
			// Only remove if it looks like ours.
			$contents = file_get_contents( $htaccess );
			if ( is_string( $contents ) && str_contains( $contents, 'Media Manager' ) ) {
				unlink( $htaccess );
			}
		}

		ProtectedRepository::delete( $folder_id );

		return true;
	}

	// -----------------------------------------------------------------------
	// Status
	// -----------------------------------------------------------------------

	/**
	 * Return whether a folder is currently protected.
	 */
	public function is_protected( int $folder_id ): bool {
		return ProtectedRepository::is_protected( $folder_id );
	}

	/**
	 * Return the full list of protected folder records shaped for the BDA UI.
	 *
	 * @return array<int, array{folder_id:int, folder_path:string, protected_at:string}>
	 */
	public function get_all_protected(): array {
		$rows   = ProtectedRepository::get_all_rows();
		$result = [];

		foreach ( $rows as $row ) {
			$folder_id = (int) $row['attachment_id']; // stored as attachment_id in this table
			$result[]  = [
				'folder_id'    => $folder_id,
				'folder_path'  => FolderRepository::get_path( $folder_id ),
				'protected_at' => $row['created_at'],
			];
		}

		return $result;
	}

	// -----------------------------------------------------------------------
	// Bulk operations
	// -----------------------------------------------------------------------

	/**
	 * Audit every protected folder: check .htaccess is still present.
	 * Returns a list of folders where the file is missing.
	 *
	 * @return int[] Folder IDs with missing .htaccess.
	 */
	public function audit(): array {
		$folder_ids = ProtectedRepository::get_all();
		$missing    = [];

		foreach ( $folder_ids as $folder_id ) {
			$path = FolderRepository::get_path( $folder_id );
			if ( ! $path ) {
				continue;
			}
			$htaccess = trailingslashit( $path ) . '.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				$missing[] = $folder_id;
			}
		}

		return $missing;
	}
}
