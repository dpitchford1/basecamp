<?php
/**
 * Basecamp Theme — Bootstrap
 *
 * Loads all theme modules in dependency order:
 *   Core → Frontend → Admin → SEO → Theme Functions → Optimization → REST → Cron → Dev → Ecommerce
 *
 * To disable a module, comment out its require_once line.
 *
 * @package basecamp
 */

// ---------------------------------------------------------------------------
// Theme version + global object
// ---------------------------------------------------------------------------

$theme            = wp_get_theme( 'basecamp' );
$basecamp_version = $theme['Version'];

$basecamp = (object) array(
	'version' => $basecamp_version,
	'main'    => require_once __DIR__ . '/inc/class-basecamp.php',
);

// ---------------------------------------------------------------------------
// Frontend
// ---------------------------------------------------------------------------

require_once __DIR__ . '/inc/frontend/class-basecamp-svg-icons.php';
require_once __DIR__ . '/inc/frontend/class-basecamp-frontend.php';
require_once __DIR__ . '/inc/frontend/remove-bloat.php';
require_once __DIR__ . '/inc/frontend/class-basecamp-cookie-consent.php';
require_once __DIR__ . '/inc/frontend/class-basecamp-video-carousel-metabox.php';
$basecamp_frontend = new Basecamp_Frontend();

// ---------------------------------------------------------------------------
// Admin
// ---------------------------------------------------------------------------

if ( is_admin() ) {
	$basecamp->admin = require_once __DIR__ . '/inc/admin/class-basecamp-admin.php';
	require_once __DIR__ . '/inc/admin/basecamp-admin-helpers.php';
	require_once __DIR__ . '/inc/admin/class-basecamp-docs.php';
}

// ---------------------------------------------------------------------------
// SEO
// ---------------------------------------------------------------------------

require_once __DIR__ . '/inc/seo/class-basecamp-seo.php';

// ---------------------------------------------------------------------------
// Theme Functions
// ---------------------------------------------------------------------------

require_once __DIR__ . '/inc/theme-functions/basecamp-meta-link-list.php';
require_once __DIR__ . '/inc/theme-functions/basecamp-analytics.php';

// CPT scaffold — copy/rename for each project CPT, then uncomment and call ::init()
// require_once __DIR__ . '/inc/theme-functions/basecamp-cpt-scaffold.php';
// Basecamp_CPT_Scaffold::init();

// ---------------------------------------------------------------------------
// Image Optimization (WebP)
// ---------------------------------------------------------------------------

require_once __DIR__ . '/inc/img-optimization/basecamp-webp-functions.php';
require_once __DIR__ . '/inc/img-optimization/basecamp-webp-conversion.php';
require_once __DIR__ . '/inc/img-optimization/webp-test-admin.php';

// ---------------------------------------------------------------------------
// REST API
// ---------------------------------------------------------------------------

require_once __DIR__ . '/inc/rest/basecamp-rest-endpoints.php';

// ---------------------------------------------------------------------------
// Scheduled Events (Cron)
// ---------------------------------------------------------------------------

require_once __DIR__ . '/inc/core/basecamp-scheduled-events.php';

// ---------------------------------------------------------------------------
// Development Tools (local only)
// ---------------------------------------------------------------------------

if ( in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] ) ) {
	require_once __DIR__ . '/inc/development/class-basecamp-development.php';
	$basecamp_development = new Basecamp_Development();
}

// ---------------------------------------------------------------------------
// Ecommerce — WooCommerce (toggle: uncomment to activate)
// Requires WooCommerce plugin. The file handles activation checks internally.
// ---------------------------------------------------------------------------

// require_once __DIR__ . '/inc/woocommerce/woocommerce-functions.php';
