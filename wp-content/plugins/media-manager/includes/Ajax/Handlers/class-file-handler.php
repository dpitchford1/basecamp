<?php
declare(strict_types=1);
namespace MediaManager\Ajax\Handlers;

use MediaManager\Data\FileRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FileHandler
 *
 * AJAX handlers for file mutations:
 *   Phase 6 — upload
 *   Phase 7 — move/copy, rename, delete
 */
final class FileHandler {

	use AjaxHelpers;

	public function upload_file(): void {
		$this->verify( 'upload_files' );

		$folder_id = isset( $_POST['folder_id'] ) ? (int) wp_unslash( $_POST['folder_id'] ) : 0;
		if ( ! $folder_id ) {
			wp_send_json_error( [ 'message' => __( 'No folder specified.', 'media-manager' ) ], 400 );
		}

		if ( ! class_exists( '\MediaManager\FileSystem\FileManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-file-manager.php';
		}

		$result = \MediaManager\FileSystem\FileManager::upload( $folder_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( $this->build_file_data( [ $result ] )[0] ?? [] );
	}

	public function move_copy_file(): void {
		$this->verify();

		$attachment_id  = isset( $_POST['attachment_id'] ) ? (int) wp_unslash( $_POST['attachment_id'] ) : 0;
		$dest_folder_id = isset( $_POST['dest_folder_id'] ) ? (int) wp_unslash( $_POST['dest_folder_id'] ) : 0;
		$mode           = isset( $_POST['mode'] ) && 'copy' === sanitize_key( $_POST['mode'] ) ? 'copy' : 'move';

		if ( ! $attachment_id || ! $dest_folder_id ) {
			wp_send_json_error( [ 'message' => __( 'Missing parameters.', 'media-manager' ) ], 400 );
		}

		if ( ! class_exists( '\MediaManager\FileSystem\FileManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-file-manager.php';
		}

		$result = 'move' === $mode
			? \MediaManager\FileSystem\FileManager::move( $attachment_id, $dest_folder_id )
			: \MediaManager\FileSystem\FileManager::copy( $attachment_id, $dest_folder_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		if ( 'move' === $mode ) {
			wp_send_json_success( [
				'attachment_id'    => $result['id'],
				'mode'             => $mode,
				'filename_changed' => $result['filename_changed'],
				'new_filename'     => $result['filename'],
			] );
		} else {
			wp_send_json_success( [ 'attachment_id' => $result, 'mode' => $mode ] );
		}
	}

	public function rename_file(): void {
		$this->verify();

		$attachment_id = isset( $_POST['attachment_id'] ) ? (int) wp_unslash( $_POST['attachment_id'] ) : 0;
		$new_name      = isset( $_POST['new_name'] )
			? sanitize_file_name( wp_unslash( $_POST['new_name'] ) )
			: '';

		if ( ! $attachment_id || ! $new_name ) {
			wp_send_json_error( [ 'message' => __( 'Missing parameters.', 'media-manager' ) ], 400 );
		}

		if ( ! class_exists( '\MediaManager\FileSystem\FileManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-file-manager.php';
		}

		$update_title = ! isset( $_POST['update_title'] ) || '1' === sanitize_key( $_POST['update_title'] );
		$result       = \MediaManager\FileSystem\FileManager::rename( $attachment_id, $new_name, $update_title );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		$file_data = $this->build_file_data( [ $attachment_id ] );
		wp_send_json_success( $file_data[0] ?? [] );
	}

	public function delete_files(): void {
		$this->verify();

		$ids = isset( $_POST['attachment_ids'] ) && is_array( $_POST['attachment_ids'] )
			? array_map( 'intval', $_POST['attachment_ids'] )
			: [];

		if ( empty( $ids ) ) {
			wp_send_json_error( [ 'message' => __( 'No files specified.', 'media-manager' ) ], 400 );
		}

		$deleted = [];
		$errors  = [];

		foreach ( $ids as $id ) {
			$result = wp_delete_attachment( $id, true );
			if ( false === $result || null === $result ) {
				$errors[] = $id;
			} else {
				FileRepository::delete( $id );
				$deleted[] = $id;
			}
		}

		wp_send_json_success( [ 'deleted' => $deleted, 'errors' => $errors ] );
	}
}
