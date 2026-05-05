<?php
declare(strict_types=1);
namespace MediaManager\Core;

use MediaManager\FileSystem\SyncManager;
use MediaManager\Data\FolderRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scheduler
 *
 * Manages the cron job that periodically syncs the filesystem to the DB.
 */
final class Scheduler {

	/** Cron hook name. */
	const HOOK = 'mm_folder_scan';

	/**
	 * Register the cron hook callback.
	 * Called by the Loader, not directly.
	 */
	public function register( \MediaManager\Core\Loader $loader ): void {
		$loader->add_action( self::HOOK, $this, 'run_scan' );
	}

	/**
	 * Walk every known folder and import any files that are on disk but
	 * not yet tracked in mm_files.  Runs as a WP-Cron job.
	 */
	public function run_scan(): void {
		$sync    = new SyncManager();
		$folders = FolderRepository::get_all();

		foreach ( $folders as $post ) {
			$sync->full_scan( (int) $post->ID );
		}
	}

	// -----------------------------------------------------------------------
	// Static schedule helpers (called from Activator / Deactivator)
	// -----------------------------------------------------------------------

	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time(), 'hourly', self::HOOK );
		}
	}

	public static function unschedule(): void {
		$timestamp = wp_next_scheduled( self::HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK );
		}
	}
}
