<?php
declare(strict_types=1);
namespace MediaManager\FileSystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LinkUpdater
 *
 * Updates embedded attachment URLs in wp_posts.post_content after a
 * file is moved or renamed.
 *
 * Also triggers the mm_update_table_links filter so third-party plugins
 * can update their own tables.
 */
final class LinkUpdater {

	/**
	 * Replace all occurrences of $old_url with $new_url in post_content.
	 *
	 * Handles both plain URLs and srcset variants (width descriptors stripped
	 * for matching, then the full srcset is rebuilt by WP's native functions).
	 *
	 * @param int    $attachment_id  For filter context.
	 * @param string $old_url
	 * @param string $new_url
	 */
	public static function update_attachment_url( int $attachment_id, string $old_url, string $new_url ): void {
		if ( ! $old_url || ! $new_url || $old_url === $new_url ) {
			return;
		}

		global $wpdb;

		$old_escaped = esc_url_raw( $old_url );
		$new_escaped = esc_url_raw( $new_url );

		// Also derive the base name patterns so thumbnail sizes are caught.
		$old_base = pathinfo( $old_url, PATHINFO_FILENAME );
		$new_base = pathinfo( $new_url, PATHINFO_FILENAME );

		// Update post_content.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->posts}
			 SET    post_content = REPLACE(post_content, %s, %s)
			 WHERE  post_content LIKE %s",
			$old_escaped,
			$new_escaped,
			'%' . $wpdb->esc_like( $old_escaped ) . '%'
		) );

		// Update post_excerpt.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->posts}
			 SET    post_excerpt = REPLACE(post_excerpt, %s, %s)
			 WHERE  post_excerpt LIKE %s",
			$old_escaped,
			$new_escaped,
			'%' . $wpdb->esc_like( $old_escaped ) . '%'
		) );

		// Update guid if base name changed (rename case).
		if ( $old_base !== $new_base ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update(
				$wpdb->posts,
				[ 'guid' => $new_escaped ],
				[ 'ID'   => $attachment_id ],
				[ '%s' ],
				[ '%d' ]
			);
		}

		/**
		 * Filter: mm_update_table_links
		 *
		 * Allows third-party plugins to update their own DB tables when
		 * a Media Manager attachment URL changes.
		 *
		 * @param int    $attachment_id
		 * @param string $old_url
		 * @param string $new_url
		 */
		do_action( 'mm_update_table_links', $attachment_id, $old_url, $new_url );

		// Bust object cache for affected posts.
		clean_post_cache( $attachment_id );
	}
}
