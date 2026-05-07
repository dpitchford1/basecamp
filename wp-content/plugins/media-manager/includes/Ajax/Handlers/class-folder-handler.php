<?php
declare(strict_types=1);
namespace MediaManager\Ajax\Handlers;

use MediaManager\FileSystem\FolderManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FolderHandler
 *
 * AJAX handlers for folder mutations and sync:
 *   Phase 8 — create, delete, hide, refresh
 *   Phase 9 — sync (prepare + chunked processing)
 */
final class FolderHandler {

	use AjaxHelpers;

	private FolderManager $folder_manager;

	public function __construct( FolderManager $folder_manager ) {
		$this->folder_manager = $folder_manager;
	}

	// -----------------------------------------------------------------------
	// Phase 8
	// -----------------------------------------------------------------------

	public function create_folder(): void {
		$this->verify();

		$name      = isset( $_POST['name'] )
			? sanitize_file_name( wp_unslash( $_POST['name'] ) )
			: '';
		$parent_id = isset( $_POST['parent_id'] ) ? (int) wp_unslash( $_POST['parent_id'] ) : 0;

		if ( ! $name ) {
			wp_send_json_error( [ 'message' => __( 'Folder name is required.', 'media-manager' ) ], 400 );
		}

		$result = $this->folder_manager->create_folder( $name, $parent_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		if ( ! $result ) {
			wp_send_json_error( [ 'message' => __( 'Could not create folder.', 'media-manager' ) ] );
		}

		$node = $this->folder_manager->get_node_for_js( $result );
		wp_send_json_success( [ 'folder_id' => $result, 'node' => $node ] );
	}

	public function delete_folder(): void {
		$this->verify();

		$folder_id = isset( $_POST['folder_id'] ) ? (int) wp_unslash( $_POST['folder_id'] ) : 0;
		if ( ! $folder_id ) {
			wp_send_json_error( [ 'message' => __( 'No folder specified.', 'media-manager' ) ], 400 );
		}

		$result = $this->folder_manager->delete_folder( $folder_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [ 'folder_id' => $folder_id ] );
	}

	public function hide_folder(): void {
		$this->verify();

		$folder_id = isset( $_POST['folder_id'] ) ? (int) wp_unslash( $_POST['folder_id'] ) : 0;
		$hidden    = isset( $_POST['hidden'] ) ? (bool) $_POST['hidden'] : true;

		if ( ! $folder_id ) {
			wp_send_json_error( [ 'message' => __( 'No folder specified.', 'media-manager' ) ], 400 );
		}

		$this->folder_manager->set_hidden( $folder_id, $hidden );
		wp_send_json_success( [ 'folder_id' => $folder_id, 'hidden' => $hidden ] );
	}

	public function refresh_folders(): void {
		$this->verify();

		$result = $this->folder_manager->refresh();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [
			'tree'    => $this->folder_manager->get_tree_for_js(),
			'new'     => $result['new']     ?? 0,
			'removed' => $result['removed'] ?? 0,
		] );
	}

	// -----------------------------------------------------------------------
	// Phase 9
	// -----------------------------------------------------------------------

	public function sync_folder(): void {
		$this->verify();

		$folder_id = isset( $_POST['folder_id'] ) ? (int) wp_unslash( $_POST['folder_id'] ) : 0;
		if ( ! $folder_id ) {
			wp_send_json_error( [ 'message' => __( 'No folder specified.', 'media-manager' ) ], 400 );
		}

		if ( ! class_exists( '\MediaManager\FileSystem\SyncManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-sync-manager.php';
		}

		$result = ( new \MediaManager\FileSystem\SyncManager() )->prepare( $folder_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( $result );
	}

	public function sync_chunk(): void {
		$this->verify();

		$folder_id   = isset( $_POST['folder_id'] ) ? (int) wp_unslash( $_POST['folder_id'] ) : 0;
		$chunk_index = isset( $_POST['chunk_index'] ) ? (int) wp_unslash( $_POST['chunk_index'] ) : 0;

		if ( ! class_exists( '\MediaManager\FileSystem\SyncManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-sync-manager.php';
		}

		$result = ( new \MediaManager\FileSystem\SyncManager() )->process_chunk( $folder_id, $chunk_index );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( $result );
	}
}
