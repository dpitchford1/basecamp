<?php
declare(strict_types=1);
namespace MediaManager\CPT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MediaManager\Core\Loader;

/**
 * FolderPost
 *
 * Registers the mm_folder custom post type. Each physical folder on disk is
 * represented as one CPT post. The WP post hierarchy (post_parent) encodes
 * folder nesting. post_name stores the folder's physical directory name.
 *
 * Post meta:
 *   _mm_folder_path  (string)  Absolute server path to the folder.
 *   _mm_hidden       (bool)    Whether the folder is hidden from the tree.
 */
final class FolderPost {

	public function __construct( Loader $loader ) {
		$loader->add_action( 'init', $this, 'register_post_type' );
	}

	// -----------------------------------------------------------------------

	public function register_post_type(): void {
		$args = apply_filters( 'mm_post_type_args', [
			'label'               => __( 'Media Folder', 'media-manager' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => false,
			'query_var'           => false,
			'hierarchical'        => true,
			'supports'            => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
			'capabilities'        => [
				'edit_post'          => 'manage_options',
				'read_post'          => 'edit_others_posts',
				'delete_post'        => 'manage_options',
				'edit_posts'         => 'edit_others_posts',
				'edit_others_posts'  => 'manage_options',
				'publish_posts'      => 'manage_options',
				'read_private_posts' => 'manage_options',
			],
		] );

		register_post_type( MM_POST_TYPE, $args );
	}
}
