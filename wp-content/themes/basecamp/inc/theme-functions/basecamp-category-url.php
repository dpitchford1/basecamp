<?php
/**
 * Category URL — remove /category/ base natively.
 *
 * Replicates the logic of the "Remove Category URL" plugin without any
 * third-party plugin dependency.
 *
 * How it works:
 *   1. Strips the category base from the permastruct so WP builds archive
 *      URLs as /news/ instead of /category/news/.
 *   2. Replaces the generated category rewrite rules with explicit per-slug
 *      rules (e.g. (news)/page/?([0-9]+)/?$). Hard-coded slugs take priority
 *      in the rule list over the generic page-hierarchy pattern that would
 *      otherwise hijack /news/page/2/ and return a 404.
 *   3. Adds a 'category_redirect' query var + request filter so that any
 *      request hitting the old /category/<slug>/ base is 301-redirected to
 *      the clean /<slug>/ URL.
 *   4. Flushes rewrite rules whenever a category is created, edited, or
 *      deleted, and also on theme activation/deactivation.
 *
 * To activate: uncomment the require_once line in functions.php.
 *
 * @package basecamp
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove the category base from WordPress's category permastruct so that
 * category archive URLs are served at /<slug>/ rather than /category/<slug>/.
 */
add_action( 'init', function (): void {
	global $wp_rewrite;
	$wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
} );

/**
 * Replace WordPress's generated category rewrite rules with explicit per-slug
 * rules so paginated archives resolve correctly.
 *
 * WordPress's generic page-hierarchy rule '(.+?)/page/([0-9]+)/?$' maps
 * incoming requests to 'pagename', not 'category_name'. Without explicit
 * rules, /news/page/2/ is treated as "a page named news, section page, id 2"
 * — no such page exists, so WordPress returns a 404.
 *
 * By registering '(news)/page/?([0-9]+)/?$' → category_name + paged as a
 * hard-coded rule, it is stored earlier in the rewrite rule list and matched
 * before the ambiguous page-hierarchy fallback ever fires.
 *
 * The old /category/<slug>/ base is also captured and queued for a 301
 * redirect via the request filter below.
 *
 * @param array<string,string> $rules WP-generated category rewrite rules.
 * @return array<string,string>
 */
add_filter( 'category_rewrite_rules', function ( array $rules ): array {
	$rules = [];

	$categories = get_categories( [ 'hide_empty' => false ] );
	foreach ( $categories as $cat ) {
		$slug = $cat->slug;

		// Build the full slug path including any parent hierarchy.
		if ( $cat->parent === $cat->cat_ID ) {
			$cat->parent = 0;
		}
		if ( 0 !== $cat->parent ) {
			$slug = get_category_parents( $cat->parent, false, '/', true ) . $slug;
		}

		$rules[ '(' . $slug . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
		$rules[ '(' . $slug . ')/page/?([0-9]{1,})/?$' ]                  = 'index.php?category_name=$matches[1]&paged=$matches[2]';
		$rules[ '(' . $slug . ')/?$' ]                                     = 'index.php?category_name=$matches[1]';
	}

	// Capture old /category/<slug>/ URLs for 301 redirect.
	$old_base              = get_option( 'category_base' ) ?: 'category';
	$old_base              = trim( $old_base, '/' );
	$rules[ $old_base . '/(.*)$' ] = 'index.php?category_redirect=$matches[1]';

	return $rules;
} );

/**
 * Register 'category_redirect' as a public query variable.
 *
 * @param string[] $vars Registered public query vars.
 * @return string[]
 */
add_filter( 'query_vars', function ( array $vars ): array {
	$vars[] = 'category_redirect';
	return $vars;
} );

/**
 * 301-redirect any request that hit the old /category/<slug>/ base to the
 * clean /<slug>/ URL.
 *
 * @param array<string,mixed> $query_vars Parsed query variables.
 * @return array<string,mixed>
 */
add_filter( 'request', function ( array $query_vars ): array {
	if ( isset( $query_vars['category_redirect'] ) ) {
		$url = trailingslashit( home_url() ) . user_trailingslashit( $query_vars['category_redirect'], 'category' );
		wp_redirect( esc_url_raw( $url ), 301 );
		exit;
	}
	return $query_vars;
} );

/**
 * Flush rewrite rules whenever categories change so the explicit per-slug
 * rules are kept in sync with the actual category list.
 */
foreach ( [ 'created_category', 'edited_category', 'delete_category' ] as $_basecamp_cat_hook ) {
	add_action( $_basecamp_cat_hook, function (): void {
		flush_rewrite_rules();
	} );
}
unset( $_basecamp_cat_hook );

// Flush on theme activation and deactivation.
add_action( 'after_switch_theme', 'flush_rewrite_rules' );
add_action( 'switch_theme',       'flush_rewrite_rules' );
