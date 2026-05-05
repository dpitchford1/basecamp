<?php
declare(strict_types=1);
namespace MediaManager\Ajax\Handlers;

use MediaManager\Data\FileRepository;
use MediaManager\Data\FolderRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LibraryHandler
 *
 * AJAX handlers for read-only library operations:
 *   Phase 4  — folder tree + folder meta
 *   Phase 5  — folder contents + sort preference
 *   Phase 13 — folder thumbnails, recent files, orphan scan
 */
final class LibraryHandler {

	use AjaxHelpers;

	const SORT_FIELDS    = [ 'date', 'title' ];
	const SORT_DIRS      = [ 'ASC', 'DESC' ];
	const META_SORT_FIELD = 'mm_sort_field';
	const META_SORT_DIR   = 'mm_sort_direction';

	// -----------------------------------------------------------------------
	// Phase 4
	// -----------------------------------------------------------------------

	public function folder_tree(): void {
		$this->verify();

		if ( ! class_exists( '\MediaManager\FileSystem\FolderManager' ) ) {
			require_once MM_PLUGIN_DIR . 'includes/FileSystem/class-folder-manager.php';
		}

		$manager = new \MediaManager\FileSystem\FolderManager();
		wp_send_json_success( $manager->get_tree_for_js() );
	}

	public function load_folder(): void {
		$this->verify();

		$folder_id = isset( $_POST['folder_id'] ) ? (int) $_POST['folder_id'] : 0;

		if ( ! $folder_id ) {
			wp_send_json_error( [ 'message' => __( 'No folder specified.', 'media-manager' ) ], 400 );
		}

		$folder = get_post( $folder_id );
		if ( ! $folder || MM_POST_TYPE !== $folder->post_type ) {
			wp_send_json_error( [ 'message' => __( 'Folder not found.', 'media-manager' ) ], 404 );
		}

		wp_send_json_success( [
			'id'         => $folder_id,
			'name'       => $folder->post_title,
			'path'       => FolderRepository::get_path( $folder_id ),
			'file_count' => FileRepository::count_by_folder( $folder_id ),
		] );
	}

	// -----------------------------------------------------------------------
	// Phase 5
	// -----------------------------------------------------------------------

	public function folder_contents(): void {
		$this->verify();

		$folder_id = isset( $_POST['folder_id'] ) ? (int) $_POST['folder_id'] : 0;
		if ( ! $folder_id ) {
			wp_send_json_error( [ 'message' => __( 'No folder specified.', 'media-manager' ) ], 400 );
		}

		$sort        = $this->get_sort_params();
		$per_page    = (int) get_option( 'mm_items_per_page', 500 );
		$page        = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;
		$offset      = ( $page - 1 ) * $per_page;
		$orderby     = 'date' === $sort['field'] ? 'post_date' : 'post_title';
		$total_count = FileRepository::get_count_by_folder( $folder_id );
		$sorted_ids  = FileRepository::get_by_folder_sorted( $folder_id, $orderby, $sort['direction'], $per_page, $offset );

		wp_send_json_success( [
			'files'          => $this->build_file_data( $sorted_ids ),
			'sort_field'     => $sort['field'],
			'sort_direction' => $sort['direction'],
			'total'          => $total_count,
			'page'           => $page,
			'per_page'       => $per_page,
		] );
	}

	public function sort_contents(): void {
		$this->verify();

		$sort    = $this->get_sort_params();
		$user_id = get_current_user_id();
		update_user_meta( $user_id, self::META_SORT_FIELD, $sort['field'] );
		update_user_meta( $user_id, self::META_SORT_DIR,   $sort['direction'] );

		// Delegate — sort preference now stored so folder_contents picks it up.
		$this->folder_contents();
	}

	// -----------------------------------------------------------------------
	// Phase 13
	// -----------------------------------------------------------------------

	/**
	 * Return up to 4 thumbnail URLs for a folder (hover preview strip).
	 */
	public function folder_thumbs(): void {
		$this->verify();

		$folder_id = isset( $_POST['folder_id'] ) ? (int) $_POST['folder_id'] : 0;
		if ( ! $folder_id ) {
			wp_send_json_success( [ 'thumbs' => [] ] );
		}

		$ids    = FileRepository::get_by_folder_sorted( $folder_id, 'post_date', 'DESC', 20, 0 );
		$thumbs = [];
		foreach ( $ids as $id ) {
			if ( ! wp_attachment_is_image( $id ) ) { continue; }
			$url = wp_get_attachment_image_url( $id, 'thumbnail' );
			if ( $url ) { $thumbs[] = $url; }
			if ( count( $thumbs ) >= 4 ) { break; }
		}

		wp_send_json_success( [ 'thumbs' => $thumbs ] );
	}

	/**
	 * Return a paginated list of attachments uploaded within the last N days.
	 */
	public function recent_files(): void {
		$this->verify();

		$days     = isset( $_POST['days'] ) ? max( 1, (int) $_POST['days'] ) : 7;
		$per_page = (int) get_option( 'mm_items_per_page', 50 );
		$page     = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;

		$query = new \WP_Query( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'no_found_rows'  => false,
			'fields'         => 'ids',
			'date_query'     => [ [ 'after' => $days . ' days ago', 'inclusive' => true ] ],
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		$files = $this->build_file_data( array_map( 'intval', (array) $query->posts ) );

		wp_send_json_success( [
			'files'    => $files,
			'total'    => (int) $query->found_posts,
			'page'     => $page,
			'per_page' => $per_page,
		] );
	}

	/**
	 * Return attachments that have no row in mm_files (not assigned to any folder).
	 */
	public function get_orphans(): void {
		$this->verify();

		global $wpdb;
		$per_page    = (int) get_option( 'mm_items_per_page', 50 );
		$page        = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;
		$files_table = $wpdb->prefix . 'mm_files';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$all_ids = $wpdb->get_col(
			"SELECT p.ID FROM {$wpdb->posts} p
			 LEFT JOIN {$files_table} mf ON mf.attachment_id = p.ID
			 WHERE p.post_type = 'attachment'
			   AND p.post_status = 'inherit'
			   AND mf.attachment_id IS NULL
			 ORDER BY p.post_date DESC"
		);

		$total    = count( (array) $all_ids );
		$page_ids = array_slice( (array) $all_ids, ( $page - 1 ) * $per_page, $per_page );
		$files    = $this->build_file_data( array_map( 'intval', $page_ids ) );

		wp_send_json_success( [
			'files'    => $files,
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		] );
	}

	// -----------------------------------------------------------------------
	// Private
	// -----------------------------------------------------------------------

	/**
	 * Read + sanitise sort params from $_POST, falling back to user preference.
	 *
	 * @return array{ field: string, direction: string }
	 */
	private function get_sort_params(): array {
		$user_id = get_current_user_id();

		$field = isset( $_POST['sort_field'] )
			? sanitize_text_field( wp_unslash( $_POST['sort_field'] ) )
			: get_user_meta( $user_id, self::META_SORT_FIELD, true );

		$dir = isset( $_POST['sort_direction'] )
			? strtoupper( sanitize_text_field( wp_unslash( $_POST['sort_direction'] ) ) )
			: get_user_meta( $user_id, self::META_SORT_DIR, true );

		if ( ! in_array( $field, self::SORT_FIELDS, true ) ) { $field = 'date'; }
		if ( ! in_array( $dir,   self::SORT_DIRS,   true ) ) { $dir   = 'DESC'; }

		return [ 'field' => $field, 'direction' => $dir ];
	}
}
