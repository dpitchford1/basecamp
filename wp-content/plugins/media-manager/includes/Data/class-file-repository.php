<?php
declare(strict_types=1);
namespace MediaManager\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FileRepository
 *
 * All reads and writes for the {prefix}mm_files table.
 *
 * Table schema (created by Activator):
 *   id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
 *   attachment_id BIGINT UNSIGNED NOT NULL UNIQUE
 *   folder_id     BIGINT UNSIGNED NOT NULL DEFAULT 0
 *   indexed_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
 */
final class FileRepository {

	// -----------------------------------------------------------------------
	// Read
	// -----------------------------------------------------------------------

	/**
	 * Return all attachment IDs assigned to a given folder.
	 *
	 * @return int[]
	 */
	public static function get_by_folder( int $folder_id ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT attachment_id FROM {$table} WHERE folder_id = %d ORDER BY attachment_id ASC",
			$folder_id
		) );

		return array_map( 'intval', (array) $ids );
	}

	/**
	 * Return attachment IDs for a folder, sorted via a JOIN against wp_posts.
	 *
	 * @param string $orderby  'date' or 'title'.
	 * @param string $order    'ASC' or 'DESC'.
	 * @param int    $limit    Max rows to return.
	 * @param int    $offset   Number of rows to skip.
	 * @return int[]
	 */
	public static function get_by_folder_sorted(
		int $folder_id,
		string $orderby = 'post_date',
		string $order = 'DESC',
		int $limit = 500,
		int $offset = 0
	): array {
		global $wpdb;

		$table   = $wpdb->prefix . 'mm_files';
		$posts   = $wpdb->posts;
		$orderby = 'post_date' === $orderby ? 'p.post_date' : 'p.post_title';
		$order   = 'ASC' === $order ? 'ASC' : 'DESC';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT f.attachment_id
			   FROM {$table} f
			   JOIN {$posts} p ON p.ID = f.attachment_id
			  WHERE f.folder_id = %d
			    AND p.post_status = 'inherit'
			  ORDER BY {$orderby} {$order}
			  LIMIT %d OFFSET %d",
			$folder_id,
			$limit,
			$offset
		) );

		return array_map( 'intval', (array) $ids );
	}

	/**
	 * Return the total number of files tracked in a folder.
	 *
	 * @param int $folder_id
	 * @return int
	 */
	public static function get_count_by_folder( int $folder_id ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';
		$posts = $wpdb->posts;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*)
			   FROM {$table} f
			   JOIN {$posts} p ON p.ID = f.attachment_id
			  WHERE f.folder_id = %d
			    AND p.post_status = 'inherit'",
			$folder_id
		) );

		return (int) $count;
	}

	/**
	 * Return the folder ID the given attachment belongs to.
	 * Returns 0 if the attachment is not tracked in mm_files.
	 */
	public static function get_folder_id( int $attachment_id ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$id = $wpdb->get_var( $wpdb->prepare(
			"SELECT folder_id FROM {$table} WHERE attachment_id = %d LIMIT 1",
			$attachment_id
		) );

		return $id !== null ? (int) $id : 0;
	}

	/**
	 * Return the total number of files in a folder.
	 */
	public static function count_by_folder( int $folder_id ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE folder_id = %d",
			$folder_id
		) );

		return (int) $count;
	}

	/**
	 * Return true if the attachment is already tracked.
	 */
	public static function exists( int $attachment_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$found = $wpdb->get_var( $wpdb->prepare(
			"SELECT 1 FROM {$table} WHERE attachment_id = %d LIMIT 1",
			$attachment_id
		) );

		return (bool) $found;
	}

	// -----------------------------------------------------------------------
	// Write
	// -----------------------------------------------------------------------

	/**
	 * Record an attachment → folder mapping.
	 * No-op if the attachment is already tracked (uses INSERT IGNORE).
	 */
	public static function insert( int $attachment_id, int $folder_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO {$table} (attachment_id, folder_id) VALUES (%d, %d)",
			$attachment_id,
			$folder_id
		) );
	}

	/**
	 * Move an attachment to a different folder.
	 * Inserts the row first if it does not exist yet.
	 */
	public static function update_folder( int $attachment_id, int $folder_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->replace(
			$table,
			[
				'attachment_id' => $attachment_id,
				'folder_id'     => $folder_id,
			],
			[ '%d', '%d' ]
		);
	}

	/**
	 * Remove a file row when an attachment is deleted.
	 */
	public static function delete( int $attachment_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->delete( $table, [ 'attachment_id' => $attachment_id ], [ '%d' ] );
	}

	/**
	 * Remove all file rows for a given folder (used before folder deletion).
	 */
	public static function delete_by_folder( int $folder_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->delete( $table, [ 'folder_id' => $folder_id ], [ '%d' ] );
	}

	/**
	 * Return a paginated list of attachment IDs uploaded within the last N days,
	 * plus the total found_posts count for pagination.
	 *
	 * @param  int $days     Number of days to look back.
	 * @param  int $per_page Number of items per page.
	 * @param  int $page     1-based page number.
	 * @return array{ ids: int[], total: int }
	 */
	public static function get_recent( int $days, int $per_page, int $page ): array {
		$query = new \WP_Query( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'no_found_rows'  => false,
			'fields'         => 'ids',
			'date_query'     => [ [ 'after' => $days . ' days ago', 'inclusive' => true ] ],
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		return [
			'ids'   => array_map( 'intval', (array) $query->posts ),
			'total' => (int) $query->found_posts,
		];
	}

	/**
	 * Find an attachment post ID by its relative upload path (_wp_attached_file meta).
	 *
	 * @param  string $relative  Path relative to the uploads directory.
	 * @return int               Post ID, or 0 if not found.
	 */
	public static function find_id_by_path( string $relative ): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
	 * Bulk-insert attachment → folder rows efficiently.
	 * Skips duplicates (INSERT IGNORE).
	 *
	 * @param array<int, int> $map  [ attachment_id => folder_id ]
	 */
	public static function bulk_insert( array $map ): void {
		if ( empty( $map ) ) {
			return;
		}

		global $wpdb;

		$table       = $wpdb->prefix . 'mm_files';
		$value_parts = [];
		$values      = [];

		foreach ( $map as $attachment_id => $folder_id ) {
			$value_parts[] = '(%d, %d)';
			$values[]      = (int) $attachment_id;
			$values[]      = (int) $folder_id;
		}

		$placeholders = implode( ', ', $value_parts );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders
		$wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO {$table} (attachment_id, folder_id) VALUES {$placeholders}",
			$values
		) );
	}
}
