<?php
declare(strict_types=1);
namespace MediaManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivator
 *
 * Runs on plugin deactivation.
 * Clears the scheduled cron job. Does NOT delete any data — that is handled
 * exclusively by uninstall.php when the plugin is deleted.
 */
final class Deactivator {

	/**
	 * Called via register_deactivation_hook in Plugin::run().
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'mm_folder_scan' );
	}
}
