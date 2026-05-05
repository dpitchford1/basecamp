<?php
declare(strict_types=1);
namespace MediaManager\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProtectedRepository
 *
 * All reads and writes for the {prefix}mm_protected table.
 *
 * Table schema (created by Activator):
 *   id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
 *   attachment_id BIGINT UNSIGNED NOT NULL UNIQUE
 *   created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
 */
final class ProtectedRepository {

	// -----------------------------------------------------------------------
	// Read
	// -----------------------------------------------------------------------

	/**
	 * Return true if the attachment is protected (i.e. blocked from direct access).
	 */
	public static function is_protected( int $attachment_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_protected';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$found = $wpdb->get_var( $wpdb->prepare(
			"SELECT 1 FROM {$table} WHERE attachment_id = %d LIMIT 1",
			$attachment_id
		) );

		return (bool) $found;
	}

	/**
	 * Return all protected attachment IDs.
	 *
	 * @return int[]
	 */
	public static function get_all(): array {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_protected';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ids = $wpdb->get_col( "SELECT attachment_id FROM {$table} ORDER BY id ASC" );

		return array_map( 'intval', (array) $ids );
	}

	/**
	 * Return all rows as associative arrays (id, attachment_id, created_at).
	 *
	 * @return array<int, array{id:int, attachment_id:int, created_at:string}>
	 */
	public static function get_all_rows(): array {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_protected';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results( "SELECT id, attachment_id, created_at FROM {$table} ORDER BY id ASC", ARRAY_A );

		$result = [];
		foreach ( (array) $rows as $row ) {
			$result[] = [
				'id'            => (int) $row['id'],
				'attachment_id' => (int) $row['attachment_id'],
				'created_at'    => $row['created_at'],
			];
		}

		return $result;
	}

	/**
	 * Return count of all protected files.
	 */
	public static function count(): int {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_protected';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	// -----------------------------------------------------------------------
	// Write
	// -----------------------------------------------------------------------

	/**
	 * Mark an attachment as protected.
	 * No-op if it is already protected (INSERT IGNORE).
	 */
	public static function insert( int $attachment_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_protected';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO {$table} (attachment_id) VALUES (%d)",
			$attachment_id
		) );
	}

	/**
	 * Remove an attachment from the protected list.
	 */
	public static function delete( int $attachment_id ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_protected';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->delete( $table, [ 'attachment_id' => $attachment_id ], [ '%d' ] );
	}

	/**
	 * Remove multiple rows by their row IDs (not attachment IDs).
	 * Used for bulk un-protect UI actions.
	 *
	 * @param int[] $row_ids
	 */
	public static function delete_by_ids( array $row_ids ): void {
		if ( empty( $row_ids ) ) {
			return;
		}

		global $wpdb;

		$table        = $wpdb->prefix . 'mm_protected';
		$placeholders = implode( ', ', array_fill( 0, count( $row_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$table} WHERE id IN ({$placeholders})",
			array_map( 'intval', $row_ids )
		) );
	}
}
