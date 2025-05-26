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
require_once __DIR__ . '/inc/frontend/remove-bloat.php';
$basecamp_frontend = new Basecamp_Frontend();

// Theme customization (admin)
require_once __DIR__ . '/inc/theme-functions/basecamp-meta-link-list.php';

// Admin-only modules
if ( is_admin() ) {
	$basecamp->admin = require_once __DIR__ . '/inc/admin/class-basecamp-admin.php';
    require_once __DIR__ . '/inc/admin/basecamp-admin-helpers.php';
}

// === Optional WebP/Image Optimization ===
require_once __DIR__ . '/inc/img-optimization/basecamp-webp-functions.php';
require_once __DIR__ . '/inc/img-optimization/basecamp-webp-conversion.php';
require_once __DIR__ . '/inc/img-optimization/webp-test-admin.php';

// === SEO Modules ===
require_once __DIR__ . '/inc/seo/class-basecamp-seo.php';

// === REST API Endpoints ===
require_once __DIR__ . '/inc/rest/basecamp-rest-endpoints.php';

// === Scheduled Events ===
require_once __DIR__ . '/inc/core/basecamp-scheduled-events.php';


// === Development Tools ===
if ( in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] ) ) {
	// require_once __DIR__ . '/inc/development/class-basecamp-development.php';
	// $basecamp_development = new Basecamp_Development();
}

// === Video Carousel Meta Box ===
//require_once __DIR__ . '/inc/frontend/class-basecamp-video-carousel-metabox.php';

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

// === Theme Customization Notice ===
// Note: Do not add any custom code here, try and keep things clean.
