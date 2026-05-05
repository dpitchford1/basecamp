<?php
declare(strict_types=1);
namespace MediaManager\Ajax\Handlers;

use MediaManager\Data\FileRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ThumbnailHandler
 *
 * Phase 11 — Thumbnail regeneration (chunked).
 */
final class ThumbnailHandler {

	use AjaxHelpers;

	public function regen_thumbnails(): void {
		$this->verify( 'edit_others_posts' );

		$folder_id = isset( $_POST['folder_id'] ) ? (int) $_POST['folder_id'] : 0;

		if ( ! class_exists( '\MediaManager\Thumbnails\RegenManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/Thumbnails/class-regen-manager.php';
		}

		$ids    = $folder_id ? FileRepository::get_by_folder( $folder_id ) : [];
		$result = ( new \MediaManager\Thumbnails\RegenManager() )->prepare( $ids );
		wp_send_json_success( $result );
	}

	public function regen_process(): void {
		$this->verify( 'edit_others_posts' );

		$chunk_index = isset( $_POST['chunk_index'] ) ? (int) $_POST['chunk_index'] : 0;

		if ( ! class_exists( '\MediaManager\Thumbnails\RegenManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/Thumbnails/class-regen-manager.php';
		}

		$result = ( new \MediaManager\Thumbnails\RegenManager() )->process_chunk( $chunk_index );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( $result );
	}
}
