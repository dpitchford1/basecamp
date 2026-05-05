<?php
declare(strict_types=1);
namespace MediaManager\FileSystem;

use MediaManager\Data\FolderRepository;
use MediaManager\Data\FileRepository;
use MediaManager\Helpers\PathHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FolderManager
 *
 * High-level folder operations combining CPT data with the filesystem.
 * No hooks — called by AjaxHandler directly.
 */
final class FolderManager {

	// -----------------------------------------------------------------------
	// jsTree helpers
	// -----------------------------------------------------------------------

	/**
	 * Return the full folder tree as a flat jsTree-compatible array.
	 * Hidden folders excluded.
	 *
	 * @return array<int, array{id:string, parent:string, text:string, state:array, data:array}>
	 */
	public function get_tree_for_js(): array {
		$raw   = FolderRepository::get_tree();
		$nodes = [];

		foreach ( $raw as $id => $node ) {
			if ( $node['hidden'] ) {
				continue;
			}

			$parent    = $node['parent'];
			$js_parent = ( $parent === 0 || ! isset( $raw[ $parent ] ) )
				? '#'
				: 'mm-' . $parent;

			$nodes[] = $this->build_node( $id, $node['name'], $js_parent, $node['path'] );
		}

		return $nodes;
	}

	/**
	 * Return a single jsTree node for a newly created folder.
	 */
	public function get_node_for_js( int $folder_id ): array {
		$post   = FolderRepository::get( $folder_id );
		$path   = FolderRepository::get_path( $folder_id );
		$parent = $post ? (int) $post->post_parent : 0;

		$js_parent = $parent > 0 ? 'mm-' . $parent : '#';

		return $this->build_node( $folder_id, $post ? $post->post_title : '', $js_parent, $path );
	}

	// -----------------------------------------------------------------------
	// Phase 8 — Create
	// -----------------------------------------------------------------------

	/**
	 * Create a new physical directory and register a mm_folder CPT post.
	 *
	 * @param  string $name       Directory name (will be sanitized).
	 * @param  int    $parent_id  Parent folder post ID (0 = root).
	 * @return int|\WP_Error      New folder post ID or WP_Error.
	 */
	public function create_folder( string $name, int $parent_id ): int|\WP_Error {
		$name = sanitize_file_name( $name );

		if ( ! $name ) {
			return new \WP_Error( 'mm_invalid_name', __( 'Invalid folder name.', 'media-manager' ) );
		}

		// Determine parent path.
		if ( $parent_id > 0 ) {
			$parent_path = FolderRepository::get_path( $parent_id );
			if ( ! $parent_path || ! is_dir( $parent_path ) ) {
				return new \WP_Error( 'mm_invalid_parent', __( 'Parent folder not found.', 'media-manager' ) );
			}
		} else {
			$parent_path = PathHelper::upload_basedir();
		}

		$new_path = $parent_path . '/' . $name;

		// Traversal guard.
		if ( ! PathHelper::is_path_inside( PathHelper::upload_basedir(), $new_path ) ) {
			return new \WP_Error( 'mm_path_traversal', __( 'Path is outside uploads directory.', 'media-manager' ) );
		}

		if ( file_exists( $new_path ) ) {
			return new \WP_Error( 'mm_folder_exists', __( 'A folder with that name already exists.', 'media-manager' ) );
		}

		/**
		 * Fires before a folder is created on disk.
		 *
		 * @param string $new_path
		 * @param int    $parent_id
		 */
		do_action( 'mm_before_folder_create', $new_path, $parent_id );

		if ( ! wp_mkdir_p( $new_path ) ) {
			return new \WP_Error( 'mm_mkdir_failed', __( 'Could not create directory.', 'media-manager' ) );
		}

		$folder_id = FolderRepository::insert( $name, $parent_id, $new_path );

		if ( ! $folder_id ) {
			rmdir( $new_path );
			return new \WP_Error( 'mm_insert_failed', __( 'Could not create folder record.', 'media-manager' ) );
		}

		/**
		 * Fires after a folder has been created.
		 *
		 * @param int    $folder_id
		 * @param string $new_path
		 */
		do_action( 'mm_after_folder_create', $folder_id, $new_path );

		return $folder_id;
	}

	// -----------------------------------------------------------------------
	// Phase 8 — Delete
	// -----------------------------------------------------------------------

	/**
	 * Delete an empty folder from disk and remove its CPT post.
	 *
	 * Returns WP_Error if the folder contains files or sub-folders.
	 *
	 * @return true|\WP_Error
	 */
	public function delete_folder( int $folder_id ): bool|\WP_Error {
		if ( FileRepository::count_by_folder( $folder_id ) > 0 ) {
			return new \WP_Error( 'mm_folder_not_empty', __( 'Folder contains files. Move or delete them first.', 'media-manager' ) );
		}

		$children = FolderRepository::get_children( $folder_id );
		if ( ! empty( $children ) ) {
			return new \WP_Error( 'mm_folder_has_children', __( 'Folder contains sub-folders. Remove them first.', 'media-manager' ) );
		}

		$path = FolderRepository::get_path( $folder_id );

		if ( $path && is_dir( $path ) ) {
			$entries = array_diff( scandir( $path ), [ '.', '..' ] );
			if ( ! empty( $entries ) ) {
				return new \WP_Error( 'mm_dir_not_empty', __( 'Directory is not empty on disk.', 'media-manager' ) );
			}
			rmdir( $path );
		}

		FolderRepository::delete( $folder_id );

		return true;
	}

	// -----------------------------------------------------------------------
	// Phase 8 — Hide
	// -----------------------------------------------------------------------

	/**
	 * Toggle the hidden flag on a folder.
	 * Also writes/removes a sentinel file in the directory so the
	 * cron scan knows to skip it.
	 */
	public function set_hidden( int $folder_id, bool $hidden ): void {
		FolderRepository::set_hidden( $folder_id, $hidden );

		$path    = FolderRepository::get_path( $folder_id );
		$sentinel = $path ? $path . '/mm-hidden' : '';

		if ( ! $path ) {
			return;
		}

		if ( $hidden && ! file_exists( $sentinel ) ) {
			file_put_contents( $sentinel, '' );
		} elseif ( ! $hidden && file_exists( $sentinel ) ) {
			unlink( $sentinel );
		}
	}

	// -----------------------------------------------------------------------
	// Phase 8 — Refresh
	// -----------------------------------------------------------------------

	/**
	 * Walk the uploads directory tree, import new folders as CPT posts,
	 * and remove CPT posts for directories that no longer exist.
	 *
	 * @return array{ new: int, removed: int }|\WP_Error
	 */
	public function refresh(): array|\WP_Error {
		$upload_base = PathHelper::upload_basedir();

		if ( ! is_dir( $upload_base ) ) {
			return new \WP_Error( 'mm_no_uploads', __( 'Uploads directory not found.', 'media-manager' ) );
		}

		$existing   = FolderRepository::get_all();
		$known_paths = [];

		foreach ( $existing as $post ) {
			$known_paths[ FolderRepository::get_path( $post->ID ) ] = $post->ID;
		}

		$new     = 0;
		$removed = 0;

		// Import new directories.
		$new += $this->import_new_directories( $upload_base, 0, $known_paths );

		// Remove stale CPT posts.
		foreach ( $known_paths as $path => $id ) {
			if ( ! $path || ! is_dir( $path ) ) {
				FolderRepository::delete( $id );
				FileRepository::delete_by_folder( $id );
				$removed++;
			}
		}

		return [ 'new' => $new, 'removed' => $removed ];
	}

	// -----------------------------------------------------------------------
	// Private helpers
	// -----------------------------------------------------------------------

	private function build_node( int $id, string $name, string $js_parent, string $path ): array {
		return [
			'id'     => 'mm-' . $id,
			'parent' => $js_parent,
			'text'   => esc_html( $name ),
			'type'   => 'folder',
			'state'  => [ 'opened' => ( '#' === $js_parent ) ],
			'data'   => [ 'folder_id' => $id, 'path' => $path ],
		];
	}

	/**
	 * Recursively scan $dir for subdirectories not already in $known_paths,
	 * creating CPT posts for any discovered.
	 *
	 * @param  array<string, int> &$known_paths  Running map of path → post ID.
	 * @return int  Count of newly inserted folders.
	 */
	private function import_new_directories( string $dir, int $parent_id, array &$known_paths ): int {
		$entries = @scandir( $dir );
		if ( false === $entries ) {
			return 0;
		}

		$new = 0;

		foreach ( $entries as $entry ) {
			if ( '.' === $entry || '..' === $entry || str_starts_with( $entry, '.' ) ) {
				continue;
			}

			$full_path = wp_normalize_path( $dir . '/' . $entry );

			if ( ! is_dir( $full_path ) ) {
				continue;
			}

			// Skip hidden (sentinel file present).
			if ( file_exists( $full_path . '/mm-hidden' ) ) {
				continue;
			}

			if ( ! isset( $known_paths[ $full_path ] ) ) {
				$id = FolderRepository::insert( $entry, $parent_id, $full_path );
				if ( $id ) {
					$known_paths[ $full_path ] = $id;
					$new++;
					$new += $this->import_new_directories( $full_path, $id, $known_paths );
				}
			} else {
				$new += $this->import_new_directories( $full_path, $known_paths[ $full_path ], $known_paths );
			}
		}

		return $new;
	}
}

// END OF FILE.
