<?php
declare(strict_types=1);
namespace MediaManager\Thumbnails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RegenManager
 *
 * Phase 11 — Regenerates attachment thumbnails in chunks.
 * Delegates all heavy lifting to wp_generate_attachment_metadata().
 */
final class RegenManager {

	/** Number of attachments to process per chunk. */
	const CHUNK_SIZE = 5;

	/** Transient key for the regen queue. */
	const TRANSIENT_KEY = 'mm_regen_queue';

	// -----------------------------------------------------------------------
	// Prepare
	// -----------------------------------------------------------------------

	/**
	 * Build a queue of all image attachment IDs that need regeneration.
	 * Stores queue in a transient and returns summary.
	 *
	 * @param  int[] $attachment_ids  Pass an explicit list, or omit/empty to
	 *                                 regenerate all media library images.
	 * @return array{ total: int, chunks: int }
	 */
	public function prepare( array $attachment_ids = [] ): array {
		if ( empty( $attachment_ids ) ) {
			$attachment_ids = $this->get_all_image_ids();
		}

		// Remove non-images.
		$attachment_ids = array_filter( $attachment_ids, fn( $id ) => wp_attachment_is_image( $id ) );
		$attachment_ids = array_values( $attachment_ids );

		$total  = count( $attachment_ids );
		$chunks = $total > 0 ? (int) ceil( $total / self::CHUNK_SIZE ) : 0;

		set_transient( self::TRANSIENT_KEY, $attachment_ids, DAY_IN_SECONDS );

		return [ 'total' => $total, 'chunks' => $chunks ];
	}

	// -----------------------------------------------------------------------
	// Process one chunk
	// -----------------------------------------------------------------------

	/**
	 * Regenerate thumbnails for one chunk of the prepared queue.
	 *
	 * @return array{ processed: int, remaining: int, errors: string[] }|\WP_Error
	 */
	public function process_chunk( int $chunk_index ): array|\WP_Error {
		$queue = get_transient( self::TRANSIENT_KEY );

		if ( false === $queue ) {
			return new \WP_Error( 'mm_no_regen_queue', __( 'Regen queue expired. Please restart.', 'media-manager' ) );
		}

		$offset    = $chunk_index * self::CHUNK_SIZE;
		$slice     = array_slice( $queue, $offset, self::CHUNK_SIZE );
		$processed = 0;
		$errors    = [];

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		foreach ( $slice as $attachment_id ) {
			$file = get_attached_file( $attachment_id );

			if ( ! $file || ! file_exists( $file ) ) {
				$errors[] = sprintf( __( 'File not found for attachment %d.', 'media-manager' ), $attachment_id );
				continue;
			}

			$metadata = wp_generate_attachment_metadata( $attachment_id, $file );

			if ( is_wp_error( $metadata ) ) {
				$errors[] = $metadata->get_error_message();
				continue;
			}

			wp_update_attachment_metadata( $attachment_id, $metadata );
			$processed++;
		}

		$remaining = max( 0, count( $queue ) - ( $offset + self::CHUNK_SIZE ) );

		if ( $remaining === 0 ) {
			delete_transient( self::TRANSIENT_KEY );
		}

		return [ 'processed' => $processed, 'remaining' => $remaining, 'errors' => $errors ];
	}

	// -----------------------------------------------------------------------
	// Private helpers
	// -----------------------------------------------------------------------

	/** @return int[] */
	private function get_all_image_ids(): array {
		return get_posts( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ],
			'numberposts'    => -1,
			'fields'         => 'ids',
		] );
	}
}
