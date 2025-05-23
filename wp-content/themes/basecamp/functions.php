<?php
/**
 * Basecamp Theme Engine Room
 *
 * @package basecamp
 */

// Theme version
$theme = wp_get_theme( 'basecamp' );
$basecamp_version = $theme['Version'];

// Theme object
$basecamp = (object) array(
	'version' => $basecamp_version,
	'main'    => require_once __DIR__ . '/inc/class-basecamp.php'
);

// === Load Core Theme Modules ===

// Frontend (template tags, helpers, etc.)
require_once __DIR__ . '/inc/frontend/class-basecamp-svg-icons.php';
require_once __DIR__ . '/inc/frontend/class-basecamp-frontend.php';
$basecamp_frontend = new Basecamp_Frontend();

// Theme customization (admin)
require_once __DIR__ . '/inc/frontend/basecamp-meta-link-list.php';
require_once __DIR__ . '/inc/admin/basecamp-admin-helpers.php';

// Admin-only modules
if ( is_admin() ) {
	$basecamp->admin = require_once __DIR__ . '/inc/admin/class-basecamp-admin.php';
}

// === Development Tools ===
if ( in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] ) ) {
	require_once __DIR__ . '/inc/development/class-basecamp-development.php';
	$basecamp_development = new Basecamp_Development();
}

// === Optional/Development Modules ===
// require_once __DIR__ . '/inc/development/template.php';

// === Optional Frontend Modules ===
// require_once __DIR__ . '/inc/basecamp-frontend/basecamp-bloat.php';
// require_once __DIR__ . '/inc/basecamp-frontend/basecamp-meta-description-functions.php';
// require_once __DIR__ . '/inc/basecamp-frontend/basecamp-social-meta-functions.php';
// require_once __DIR__ . '/inc/basecamp-frontend/basecamp-title-functions.php';

// === Optional Kane Functions ===
// require_once __DIR__ . '/inc/basecamp-functions/bits-metabox.php';

// === Optional WebP/Image Optimization ===
require_once __DIR__ . '/inc/img-optimization/basecamp-webp-functions.php';
require_once __DIR__ . '/inc/img-optimization/basecamp-webp-conversion.php';
require_once __DIR__ . '/inc/img-optimization/webp-test-admin.php';

// === WooCommerce Support ===
// if ( basecamp_is_woocommerce_activated() ) {
//     require_once __DIR__ . '/inc/woocommerce/class-basecamp-woocommerce.php';
//     require_once __DIR__ . '/inc/woocommerce/woocommerce-functions.php';
//     add_action( 'init', function () {
//         remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
//     } );
// }
// add_action( 'after_setup_theme', function() {
//     add_theme_support( 'woocommerce' );
// });

// === SEO Modules ===
require_once __DIR__ . '/inc/seo/class-basecamp-seo.php';

// === REST API Endpoints ===
require_once __DIR__ . '/inc/rest/basecamp-rest-endpoints.php';

// === Scheduled Events ===
require_once __DIR__ . '/inc/core/basecamp-scheduled-events.php';

// === Theme Customization Notice ===
// Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
// https://github.com/woocommerce/theme-customisations
