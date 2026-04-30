<?php
/**
 * Kaneism — Custom Post Type scaffold.
 *
 * Copy this file for each project CPT. Replace all occurrences of:
 *   kaneism_portfolio  →  your post type key
 *   Portfolio          →  your post type label
 *
 * Wire in functions.php:
 *   require_once __DIR__ . '/inc/theme-functions/kaneism-cpt-portfolio.php';
 *   Kaneism\ThemeFunctions\PortfolioCPT::init();
 *
 * Then flush rewrite rules:
 *   WP Admin → Settings → Permalinks → Save
 *
 * @package kaneism
 */
declare(strict_types=1);

namespace Kaneism\ThemeFunctions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PortfolioCPT {

	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register' ] );
	}

	public static function register(): void {
		register_post_type( 'kaneism_portfolio', [
			'labels'       => [
				'name'          => __( 'Portfolio', 'kaneism' ),
				'singular_name' => __( 'Portfolio Item', 'kaneism' ),
				'add_new_item'  => __( 'Add New Portfolio Item', 'kaneism' ),
				'edit_item'     => __( 'Edit Portfolio Item', 'kaneism' ),
			],
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => false,
			'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
			'rewrite'      => [ 'slug' => 'portfolio' ],
			'menu_icon'    => 'dashicons-portfolio',
		] );
	}
}
