<?php
declare(strict_types=1);
namespace MediaManager\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IpRepository
 *
 * All reads and writes for the {prefix}mm_blocked_ips table.
 *
 * Table schema (created by Activator):
 *   id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
 *   ip_address VARCHAR(45) NOT NULL UNIQUE
 *   created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
 *
 * Performance note:
 *   The full IP block list is cached in the WordPress object cache under the
 *   key 'mm_blocked_ips' (group 'media-manager'). Any write method flushes
 *   this cache entry so the next request rebuilds it from the DB.
 *   With a persistent object cache (Redis / Memcached), the DB is hit only
 *   once per write, regardless of traffic volume. Without a persistent cache,
 *   the list is re-fetched once per page load per PHP process — still better
 *   than one query per is_blocked() call.
 *
 * Note: VARCHAR(45) accommodates full IPv6 addresses.
 */
final class IpRepository {

	private const CACHE_KEY   = 'mm_blocked_ips';
	private const CACHE_GROUP = 'media-manager';
	private const CACHE_TTL   = HOUR_IN_SECONDS;

	// -------------------------------------------------------------------------
	// Read
	// -------------------------------------------------------------------------

	/**
	 * Return all blocked IP rows as associative arrays.
	 *
	 * @return array<int, array{id:int, ip_address:string, created_at:string}>
	 */
	public static function get_all(): array {
		return self::get_cached_list();
	}

	/**
	 * Return true if the given IP address is currently blocked.
	 * Uses the in-memory cached list — zero DB queries after first call.
	 */
	public static function is_blocked( string $address ): bool {
		foreach ( self::get_cached_list() as $row ) {
			if ( $row['ip_address'] === $address ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Return count of all blocked IPs.
	 */
	public static function count(): int {
		return count( self::get_cached_list() );
	}

	// -------------------------------------------------------------------------
	// Write
	// -------------------------------------------------------------------------

	/**
	 * Block an IP address.
	 *
	 * @param  string $address IPv4 or IPv6 address (max 45 chars).
	 * @return bool  True on success, false if the address is already blocked or invalid.
	 */
	public static function insert( string $address ): bool {
		$address = sanitize_text_field( $address );

		if ( ! filter_var( $address, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'mm_blocked_ips';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$rows_affected = $wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO {$table} (ip_address) VALUES (%s)",
			$address
		) );

		if ( $rows_affected > 0 ) {
			self::flush_cache();
		}

		// INSERT IGNORE returns 0 rows affected on duplicate.
		return $rows_affected > 0;
	}

	/**
	 * Unblock an IP by its row ID.
	 */
	public static function delete( int $id ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_blocked_ips';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
		self::flush_cache();
	}

	/**
	 * Bulk-delete blocked IPs by their row IDs.
	 *
	 * @param int[] $ids Row IDs to remove.
	 */
	public static function delete_by_ids( array $ids ): void {
		if ( empty( $ids ) ) {
			return;
		}

		global $wpdb;

		$table        = $wpdb->prefix . 'mm_blocked_ips';
		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$table} WHERE id IN ({$placeholders})",
			array_map( 'intval', $ids )
		) );

		self::flush_cache();
	}

	/**
	 * Remove a blocked IP by its address string.
	 */
	public static function delete_by_address( string $address ): void {
		global $wpdb;

		$table = $wpdb->prefix . 'mm_blocked_ips';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->delete( $table, [ 'ip_address' => $address ], [ '%s' ] );
		self::flush_cache();
	}

	// -------------------------------------------------------------------------
	// Cache
	// -------------------------------------------------------------------------

	/**
	 * Return the full IP block list from the object cache, falling back to DB.
	 *
	 * @return array<int, array{id:int, ip_address:string, created_at:string}>
	 */
	private static function get_cached_list(): array {
		$cached = wp_cache_get( self::CACHE_KEY, self::CACHE_GROUP );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'mm_blocked_ips';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results(
			"SELECT id, ip_address, created_at FROM {$table} ORDER BY id ASC",
			ARRAY_A
		);

		$result = [];
		foreach ( (array) $rows as $row ) {
			$result[] = [
				'id'         => (int) $row['id'],
				'ip_address' => $row['ip_address'],
				'created_at' => $row['created_at'],
			];
		}

		wp_cache_set( self::CACHE_KEY, $result, self::CACHE_GROUP, self::CACHE_TTL );

		return $result;
	}

	/**
	 * Invalidate the cached IP list. Called after any write operation.
	 */
	public static function flush_cache(): void {
		wp_cache_delete( self::CACHE_KEY, self::CACHE_GROUP );
	}
}
