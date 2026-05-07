<?php
declare(strict_types=1);
namespace MediaManager\Ajax\Handlers;

use MediaManager\Data\FolderRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SecurityHandler
 *
 * Phase 12 — Block Direct Access (BDA) and IP blocking.
 *
 * Capability notes:
 *   - get_protected_files: edit_others_posts — editors need this to colour
 *     the folder tree, no privileged data exposed.
 *   - All write operations (toggle, IPs, settings): manage_options.
 */
final class SecurityHandler {

	use AjaxHelpers;

	public function toggle_file_access(): void {
		$this->verify( 'manage_options' );

		$folder_id = isset( $_POST['folder_id'] ) ? (int) wp_unslash( $_POST['folder_id'] ) : 0;
		$protect   = isset( $_POST['protect'] ) ? (bool) $_POST['protect'] : true;
		$path      = FolderRepository::get_path( $folder_id );

		if ( ! $folder_id || ! $path ) {
			wp_send_json_error( [ 'message' => __( 'Folder ID and path are required.', 'media-manager' ) ], 400 );
		}

		if ( ! class_exists( '\MediaManager\Security\BdaManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/Security/class-bda-manager.php';
		}

		$bda    = new \MediaManager\Security\BdaManager();
		$result = $protect
			? $bda->protect_folder( $folder_id, $path )
			: $bda->unprotect_folder( $folder_id, $path );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [ 'folder_id' => $folder_id, 'protected' => $protect ] );
	}

	public function get_protected_files(): void {
		// Editors need this to mark protected folders in the tree — not a privileged read.
		$this->verify( 'edit_others_posts' );

		if ( ! class_exists( '\MediaManager\Security\BdaManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/Security/class-bda-manager.php';
		}

		$bda  = new \MediaManager\Security\BdaManager();
		$rows = $bda->get_all_protected();
		wp_send_json_success( [ 'folders' => $rows ] );
	}

	public function add_blocked_ip(): void {
		$this->verify( 'manage_options' );

		$address = isset( $_POST['ip_address'] )
			? sanitize_text_field( wp_unslash( $_POST['ip_address'] ) )
			: '';

		if ( ! $address ) {
			wp_send_json_error( [ 'message' => __( 'IP address is required.', 'media-manager' ) ], 400 );
		}

		if ( ! class_exists( '\MediaManager\Security\IpBlocker' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/Security/class-ip-blocker.php';
		}

		$result = ( new \MediaManager\Security\IpBlocker() )->block_ip( $address );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( \MediaManager\Data\IpRepository::get_all() );
	}

	public function remove_blocked_ips(): void {
		$this->verify( 'manage_options' );

		$ids = isset( $_POST['ids'] ) && is_array( $_POST['ids'] )
			? array_map( 'intval', $_POST['ids'] )
			: [];

		if ( empty( $ids ) ) {
			wp_send_json_error( [ 'message' => __( 'No IPs specified.', 'media-manager' ) ], 400 );
		}

		if ( ! class_exists( '\MediaManager\Security\IpBlocker' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/Security/class-ip-blocker.php';
		}

		\MediaManager\Data\IpRepository::delete_by_ids( $ids );
		wp_send_json_success( \MediaManager\Data\IpRepository::get_all() );
	}

	public function get_blocked_ips(): void {
		$this->verify( 'manage_options' );
		wp_send_json_success( \MediaManager\Data\IpRepository::get_all() );
	}

	public function save_bda_settings(): void {
		$this->verify( 'manage_options' );

		if ( ! class_exists( '\MediaManager\Security\BdaManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/Security/class-bda-manager.php';
		}

		$fields = [
			'mm_bda_enabled'            => 'bool',
			'mm_bda_prevent_listing'    => 'bool',
			'mm_bda_prevent_hotlinking' => 'bool',
			'mm_ip_blocking_enabled'    => 'bool',
			'mm_bda_user_role'          => 'text',
			'mm_bda_no_access_page_id'  => 'int',
		];

		foreach ( $fields as $key => $type ) {
			switch ( $type ) {
				case 'bool':
					update_option( $key, isset( $_POST[ $key ] ) && (bool) $_POST[ $key ], true );
					break;
				case 'int':
					if ( isset( $_POST[ $key ] ) ) {
						update_option( $key, (int) wp_unslash( $_POST[ $key ] ), true );
					}
					break;
				default:
					if ( isset( $_POST[ $key ] ) ) {
						update_option( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ), true );
					}
			}
		}

		( new \MediaManager\Security\BdaManager() )->audit();

		wp_send_json_success( [ 'message' => __( 'BDA settings saved.', 'media-manager' ) ] );
	}
}
