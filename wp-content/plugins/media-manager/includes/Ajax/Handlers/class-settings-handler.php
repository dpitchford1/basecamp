<?php
declare(strict_types=1);
namespace MediaManager\Ajax\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SettingsHandler
 *
 * Phase 10 — Plugin settings.
 * Requires manage_options: changing plugin settings is an admin action.
 */
final class SettingsHandler {

	use AjaxHelpers;

	public function save_settings(): void {
		$this->verify( 'manage_options' );

		// Boolean checkbox options: treat absence from POST as false (unchecked).
		$bool_options = [ 'mm_disable_scaling', 'mm_skip_webp', 'mm_strip_exif' ];
		foreach ( $bool_options as $key ) {
			update_option( $key, isset( $_POST[ $key ] ) && (bool) $_POST[ $key ] );
		}

		// Numeric options: only update when present.
		if ( isset( $_POST['mm_items_per_page'] ) ) {
			update_option( 'mm_items_per_page', max( 1, (int) $_POST['mm_items_per_page'] ) );
		}

		wp_send_json_success( [ 'message' => __( 'Settings saved.', 'media-manager' ) ] );
	}
}
