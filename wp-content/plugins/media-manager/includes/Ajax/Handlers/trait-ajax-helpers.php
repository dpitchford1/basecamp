<?php
declare(strict_types=1);
namespace MediaManager\Ajax\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AjaxHelpers
 *
 * Shared methods used by all Ajax handler classes:
 *   - verify()         — nonce + capability gate
 *   - build_file_data() — attachment ID list → JS-ready array
 */
trait AjaxHelpers {

	/**
	 * Verify nonce and capability. Sends a JSON error and exits on failure.
	 */
	private function verify( string $capability = 'edit_others_posts' ): void {
		if ( ! check_ajax_referer( MM_NONCE, 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'media-manager' ) ] );
		}
		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'media-manager' ) ] );
		}
	}

	/**
	 * Build the JS-ready file-data array for a list of attachment IDs.
	 *
	 * @param  int[]   $ids
	 * @return array[]
	 */
	private function build_file_data( array $ids ): array {
		$files = [];

		foreach ( $ids as $id ) {
			$post = get_post( $id );
			if ( ! $post || 'attachment' !== $post->post_type ) {
				continue;
			}

			$mime     = $post->post_mime_type ?? '';
			$is_image = str_starts_with( $mime, 'image/' );

			if ( $is_image ) {
				$thumb = wp_get_attachment_image_url( $id, 'thumbnail' );
				if ( ! $thumb ) {
					$thumb = wp_get_attachment_url( $id );
				}
			} else {
				$thumb = '';
			}

			$files[] = [
				'id'        => $id,
				'title'     => $post->post_title,
				'filename'  => basename( get_attached_file( $id ) ?? '' ),
				'url'       => wp_get_attachment_url( $id ),
				'thumbnail' => $thumb,
				'mime'      => $mime,
				'is_image'  => $is_image,
				'edit_url'  => get_edit_post_link( $id, 'raw' ),
				'date'      => $post->post_date,
			];
		}

		return $files;
	}
}
