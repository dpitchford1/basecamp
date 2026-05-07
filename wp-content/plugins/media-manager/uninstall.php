<?php
declare(strict_types=1);
/**
 * Media Manager — Uninstall
 *
 * Runs when the plugin is deleted via WP Admin → Plugins → Delete.
 * Deactivation does NOT run at this point — Deactivator::deactivate() has
 * already fired when the plugin was deactivated.
 *
 * This file:
 *  - Drops all three custom DB tables.
 *  - Deletes all mm_* options from wp_options.
 *  - Deletes all mm_folder CPT posts (and their meta).
 *  - Deletes mm_sort_field and mm_sort_direction user meta for all users.
 *  - Clears the mm_folder_scan cron event (safety net in case deactivate was skipped).
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// ---------------------------------------------------------------------------
// 1. Drop custom tables
// ---------------------------------------------------------------------------

$tables = [
	$wpdb->prefix . 'mm_files',
	$wpdb->prefix . 'mm_protected',
	$wpdb->prefix . 'mm_blocked_ips',
];

foreach ( $tables as $table ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// ---------------------------------------------------------------------------
// 2. Delete all mm_* options
// ---------------------------------------------------------------------------

$options = [
	'mm_version',
	'mm_items_per_page',
	'mm_disable_scaling',
	'mm_skip_webp',
        'mm_strip_exif',
	'mm_bda_no_access_page_id',
	'mm_upload_folder_name',
	'mm_upload_folder_id',
	'mm_integrity_check',
];

foreach ( $options as $option ) {
	delete_option( $option );
}

// ---------------------------------------------------------------------------
// 3. Remove BDA .htaccess files from protected folders
// ---------------------------------------------------------------------------

require_once plugin_dir_path( __FILE__ ) . 'includes/Data/class-protected-repository.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/Security/class-bda-manager.php';

$protected_ids = \MediaManager\Data\ProtectedRepository::get_all();
$bda           = new \MediaManager\Security\BdaManager();

foreach ( $protected_ids as $folder_id ) {
	$path = (string) get_post_meta( $folder_id, '_mm_folder_path', true );
	if ( $path ) {
		$bda->unprotect_folder( $folder_id, $path );
	}
}

// ---------------------------------------------------------------------------
// 4. Delete all mm_folder CPT posts (and their post meta)
// ---------------------------------------------------------------------------

$folder_ids = get_posts( [
	'post_type'      => 'mm_folder',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'fields'         => 'ids',
] );

foreach ( $folder_ids as $post_id ) {
	wp_delete_post( $post_id, true ); // $force_delete = true: bypass trash
}

// ---------------------------------------------------------------------------
// 5. Delete per-user sort preference meta
// ---------------------------------------------------------------------------

$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'mm_sort_field' ] );
$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => 'mm_sort_direction' ] );

// ---------------------------------------------------------------------------
// 6. Clear cron (safety net)
// ---------------------------------------------------------------------------

wp_clear_scheduled_hook( 'mm_folder_scan' );
