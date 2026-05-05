<?php
declare(strict_types=1);
namespace MediaManager\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FolderRepository
 *
 * All reads and writes for mm_folder CPT posts and their post meta.
 * No direct $wpdb calls for post data — uses WP_Query / wp_insert_post /
 * wp_delete_post. Direct $wpdb is used only for postmeta lookups where
 * WP_Query would be unnecessarily expensive.
 */
final class FolderRepository {

	// -----------------------------------------------------------------------
	// Read
	// -----------------------------------------------------------------------

	/**
	 * Return all mm_folder posts ordered by menu_order then title.
	 *
	 * @return \WP_Post[]
	 */
	public static function get_all(): array {
		return get_posts( [
			'post_type'      => MM_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
		] );
	}

	/**
	 * Return direct children of a given folder (or top-level folders if 0).
	 *
	 * @return \WP_Post[]
	 */
	public static function get_children( int $parent_id ): array {
		return get_posts( [
			'post_type'      => MM_POST_TYPE,
			'post_status'    => 'publish',
			'post_parent'    => $parent_id,
			'posts_per_page' => -1,
			'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
		] );
	}

	/**
	 * Return a single folder post by its ID, or null if not found.
	 */
	public static function get( int $folder_id ): ?\WP_Post {
		$post = get_post( $folder_id );
		if ( $post && MM_POST_TYPE === $post->post_type ) {
			return $post;
		}
		return null;
	}

	/**
	 * Canonicalize a filesystem path for consistent storage and lookup.
	 * - Converts backslashes to forward slashes (wp_normalize_path)
	 * - Resolves /./  segments (e.g. paths built via ABSPATH . './subdir')
	 * - Strips trailing slashes
	 */
	private static function canonicalize_path( string $path ): string {
		// Let WP handle backslash→slash conversion first.
		$path = wp_normalize_path( $path );
		// Collapse /./  sequences (realpath not used — path may not exist yet).
		$path = preg_replace( '#/\./#', '/', $path );
		return rtrim( $path, '/' );
	}

	/**
	 * Find a folder whose _mm_folder_path meta matches $path exactly.
	 * Returns the post ID, or 0 if not found.
	 */
	public static function get_id_by_path( string $path ): int {
		global $wpdb;

		$path = self::canonicalize_path( $path );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$id = $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key = '_mm_folder_path'
			   AND meta_value = %s
			 LIMIT 1",
			$path
		) );

		return $id ? (int) $id : 0;
	}

	/**
	 * Return the absolute server path stored in _mm_folder_path for a folder.
	 */
	public static function get_path( int $folder_id ): string {
		return (string) get_post_meta( $folder_id, '_mm_folder_path', true );
	}

	/**
	 * Return true if the folder has the _mm_hidden flag set.
	 */
	public static function is_hidden( int $folder_id ): bool {
		return (bool) get_post_meta( $folder_id, '_mm_hidden', true );
	}

	// -----------------------------------------------------------------------
	// Write
	// -----------------------------------------------------------------------

	/**
	 * Create a new mm_folder CPT post and write its path meta.
	 *
	 * @param  string $name      Folder slug / physical directory name (no spaces).
	 * @param  int    $parent_id Parent folder post ID (0 for root level).
	 * @param  string $path      Absolute server path to the folder.
	 * @return int Post ID on success, 0 on failure.
	 */
	public static function insert( string $name, int $parent_id, string $path ): int {
		$path = self::canonicalize_path( $path );

		// Return existing ID if this exact path is already tracked.
		$existing = self::get_id_by_path( $path );
		if ( $existing > 0 ) {
			return $existing;
		}

		$post_id = wp_insert_post( [
			'post_type'   => MM_POST_TYPE,
			'post_status' => 'publish',
			'post_title'  => $name,
			'post_name'   => sanitize_title( $name ),
			'post_parent' => $parent_id,
		], true );

		if ( is_wp_error( $post_id ) ) {
			return 0;
		}

		update_post_meta( $post_id, '_mm_folder_path', $path ); // already canonicalized above
		update_post_meta( $post_id, '_mm_hidden', false );

		return $post_id;
	}

	/**
	 * Permanently delete a folder post and all its meta.
	 */
	public static function delete( int $folder_id ): bool {
		$result = wp_delete_post( $folder_id, true );
		return false !== $result && null !== $result;
	}

	/**
	 * Set or clear the _mm_hidden flag on a folder.
	 */
	public static function set_hidden( int $folder_id, bool $hidden ): void {
		update_post_meta( $folder_id, '_mm_hidden', $hidden );
	}

	/**
	 * Update the absolute path stored against a folder post.
	 * Used if a folder is ever moved (not exposed in UI but needed internally).
	 */
	public static function set_path( int $folder_id, string $path ): void {
		update_post_meta( $folder_id, '_mm_folder_path', self::canonicalize_path( $path ) );
	}

	/**
	 * Remove duplicate mm_folder CPT posts that share the same _mm_folder_path.
	 * Keeps the post with the lowest ID (the oldest insert); deletes all others.
	 * Also re-points any mm_files rows that referenced the deleted post IDs.
	 *
	 * Should be called once after plugin update / data-integrity check.
	 *
	 * @return int Number of duplicate posts removed.
	 */
	public static function deduplicate_folders(): int {
		global $wpdb;

		// First, canonicalize every stored path so ./  variants match clean ones.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$all = $wpdb->get_results(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_mm_folder_path'",
			ARRAY_A
		);
		foreach ( $all as $row ) {
			$clean = self::canonicalize_path( $row['meta_value'] );
			if ( $clean !== $row['meta_value'] ) {
				update_post_meta( (int) $row['post_id'], '_mm_folder_path', $clean );
			}
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$dupes = $wpdb->get_results(
			"SELECT meta_value AS path,
			        MIN(post_id) AS keep_id,
			        GROUP_CONCAT(post_id ORDER BY post_id ASC) AS all_ids
			 FROM {$wpdb->postmeta}
			 WHERE meta_key = '_mm_folder_path'
			 GROUP BY meta_value
			 HAVING COUNT(*) > 1",
			ARRAY_A
		);

		if ( empty( $dupes ) ) {
			return 0;
		}

		$removed = 0;

		foreach ( $dupes as $row ) {
			$keep_id = (int) $row['keep_id'];
			$all_ids = array_map( 'intval', explode( ',', $row['all_ids'] ) );
			$delete_ids = array_filter( $all_ids, fn( $id ) => $id !== $keep_id );

			foreach ( $delete_ids as $del_id ) {
				// Re-point any mm_files rows to the keeper before deleting.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->update(
					$wpdb->prefix . 'mm_files',
					[ 'folder_id' => $keep_id ],
					[ 'folder_id' => $del_id ],
					[ '%d' ],
					[ '%d' ]
				);

				wp_delete_post( $del_id, true );
				$removed++;
			}
		}

		return $removed;
	}

	// -----------------------------------------------------------------------
	// Tree helpers
	// -----------------------------------------------------------------------

	/**
	 * Return every folder post as a flat array keyed by post_id, with an
	 * added 'children' key (array of child post IDs) — suitable for building
	 * the jsTree payload without extra queries.
	 *
	 * @return array<int, array{id:int, name:string, parent:int, path:string, hidden:bool, children:int[]}>
	 */
	public static function get_tree(): array {
		$posts = self::get_all();
		$tree  = [];

		foreach ( $posts as $post ) {
			$tree[ $post->ID ] = [
				'id'       => $post->ID,
				'name'     => $post->post_title,
				'parent'   => (int) $post->post_parent,
				'path'     => (string) get_post_meta( $post->ID, '_mm_folder_path', true ),
				'hidden'   => (bool) get_post_meta( $post->ID, '_mm_hidden', true ),
				'children' => [],
			];
		}

		// Wire up children arrays.
		foreach ( $tree as $id => $node ) {
			if ( $node['parent'] > 0 && isset( $tree[ $node['parent'] ] ) ) {
				$tree[ $node['parent'] ]['children'][] = $id;
			}
		}

		return $tree;
	}
}
