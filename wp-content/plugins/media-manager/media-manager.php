<?php
declare(strict_types=1);
/**
 * Plugin Name:  Media Manager
 * Description:  Physical folder management for the WordPress Media Library.
 * Version:      1.0.0
 * Author:       Basecamp
 * Text Domain:  media-manager
 * Domain Path:  /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

define( 'MM_VERSION',    '1.0.0' );
define( 'MM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MM_NONCE',      'mm_nonce' );
define( 'MM_POST_TYPE',  'mm_folder' );

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

require_once MM_PLUGIN_DIR . 'includes/Core/class-loader.php';
require_once MM_PLUGIN_DIR . 'includes/Core/class-activator.php';
require_once MM_PLUGIN_DIR . 'includes/Core/class-deactivator.php';
require_once MM_PLUGIN_DIR . 'includes/Core/class-plugin.php';

MediaManager\Core\Plugin::run();
