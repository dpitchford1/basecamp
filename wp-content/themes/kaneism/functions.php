<?php
/**
 * Kaneism — Child Theme Bootstrap
 *
 * The Basecamp parent theme loads first and provides all core modules:
 *   - Settings, Frontend, RemoveBloat, SVG Icons, Cookie Consent, Toast, Subnav
 *   - Admin UX, Docs, Media helpers
 *   - SEO (titles, meta, social, schema)
 *   - Image optimisation (WebP, thumb regen)
 *   - REST endpoints, cron stubs, dev tools
 *
 * This file should ONLY contain:
 *   1. Project-specific module requires (CPTs, custom helpers)
 *   2. Filter/action hooks that customise parent behaviour
 *   3. Any global template functions specific to this project
 *
 * Do NOT:
 *   - Re-require any parent module files
 *   - Redefine any Basecamp\* namespaced class
 *   - Call Basecamp_Settings::init() — the parent already does this
 *
 * @package kaneism
 */

// ---------------------------------------------------------------------------
// Safety check
// ---------------------------------------------------------------------------

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Project modules
// ---------------------------------------------------------------------------

// Custom Post Types — copy basecamp-cpt-scaffold.php, rename, then require here.
// require_once __DIR__ . '/inc/theme-functions/kaneism-cpt-portfolio.php';

// Photo grid metabox + kaneism_get_photo_grid_images() / kaneism_display_photo_grid()
require_once __DIR__ . '/inc/theme-functions/kaneism-photo-grid.php';

/**
 * Global template tag — retrieve photo grid images for a page.
 *
 * @param int|null $post_id
 * @return array
 */
function kaneism_get_photo_grid_images( ?int $post_id = null ): array {
	return \Kaneism\ThemeFunctions\kaneism_get_photo_grid_images( $post_id );
}

/**
 * Global template tag — render the photo grid for a page.
 *
 * @param int|null $post_id
 * @return void
 */
function kaneism_display_photo_grid( ?int $post_id = null ): void {
	\Kaneism\ThemeFunctions\kaneism_display_photo_grid( $post_id );
}

// About page — Client List repeater metabox
require_once __DIR__ . '/inc/theme-functions/kaneism-meta-clients.php';

// About page — Link List metabox + kaneism_about_links_list()
require_once __DIR__ . '/inc/theme-functions/kaneism-meta-about-links.php';

/**
 * Global template tag — delegates to the namespaced helper.
 * Must live here (no namespace) so templates can call it directly.
 *
 * @param int|null $post_id
 * @return array
 */
function kaneism_about_links_list( ?int $post_id = null ): array {
	return \Kaneism\ThemeFunctions\kaneism_about_links_list( $post_id );
}

// Project-specific admin tweaks (login logo, custom columns, etc.)
// require_once __DIR__ . '/inc/admin/kaneism-admin.php';

// Project-specific frontend helpers
// require_once __DIR__ . '/inc/frontend/kaneism-helpers.php';

// ---------------------------------------------------------------------------
// WooCommerce support
// ---------------------------------------------------------------------------

add_action( 'after_setup_theme', function (): void {
	add_theme_support( 'woocommerce' );
} );

// ---------------------------------------------------------------------------
// Body classes — project page → class map
// Extend here rather than overriding the parent's body_class logic.
// ---------------------------------------------------------------------------

add_filter( 'basecamp_body_page_classes', function ( array $map ): array {
	// 'page-slug' => 'css-class-to-add-to-body'
	// $map['about']   = 'is--about';
	// $map['contact'] = 'is--contact';
	return $map;
} );

// ---------------------------------------------------------------------------
// Footer legal links
// ---------------------------------------------------------------------------

add_filter( 'basecamp_footer_legal_links', function ( array $links ): array {
	$links[] = [ 'label' => 'Privacy Policy',    'url' => '/privacy-policy/' ];
	$links[] = [ 'label' => 'Terms & Conditions', 'url' => '/terms-and-conditions/' ];
	return $links;
} );

// ---------------------------------------------------------------------------
// Header logo
// Override only the logo markup, not the entire header.php.
// Return a full HTML string. Receives the default markup as $markup.
// ---------------------------------------------------------------------------

// add_filter( 'basecamp_header_logo', function( string $markup ): string {
// 	$name = esc_html( get_bloginfo( 'name' ) );
// 	return is_front_page()
// 		? '<div class="brand" id="logo"><span class="is--logo">' . $name . '</span></div>'
// 		: '<div class="brand" id="logo"><a class="is--logo" href="/" rel="home">' . $name . '</a></div>';
// } );

// ---------------------------------------------------------------------------
// CF7 — opt pages into Contact Form 7 asset loading
// Parent loads CF7 assets on NO pages by default.
// ---------------------------------------------------------------------------

// add_filter( 'basecamp_cf7_page_slugs', function( array $slugs ): array {
// 	$slugs[] = 'contact';
// 	return $slugs;
// } );

// ---------------------------------------------------------------------------
// Schema — wire up org details for this project
// ---------------------------------------------------------------------------

// add_filter( 'basecamp_schema_email',     fn() => 'hello@kaneism.com' );
// add_filter( 'basecamp_schema_telephone', fn() => '+10000000000' );
// add_filter( 'basecamp_schema_same_as', function( array $urls ): array {
// 	$urls[] = 'https://www.linkedin.com/in/kaneism/';
// 	return $urls;
// } );

// ---------------------------------------------------------------------------
// Image class normalisation
// By default, size-* and attachment-* WP classes are stripped from <img> tags.
// Uncomment to keep them.
// ---------------------------------------------------------------------------

// add_filter( 'basecamp_keep_wp_image_size_classes', '__return_true' );

// ---------------------------------------------------------------------------
// Analytics — advanced overrides (optional)
// GA4 ID and cookie consent are configured at Appearance → Theme Settings.
// ---------------------------------------------------------------------------

// Fire resource hints on staging (for performance testing):
// add_filter( 'basecamp_ga_hints_on_nonprod', '__return_true' );
