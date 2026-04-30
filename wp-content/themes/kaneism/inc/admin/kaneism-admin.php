<?php
/**
 * Kaneism — Admin customisations.
 *
 * Project-specific admin tweaks: custom login logo, dashboard widgets,
 * admin columns, etc.
 *
 * Wire in functions.php:
 *   require_once __DIR__ . '/inc/admin/kaneism-admin.php';
 *
 * @package kaneism
 */
declare(strict_types=1);

namespace Kaneism\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Example: custom login logo
// add_filter( 'basecamp_login_logo_url', function(): string {
// 	return get_stylesheet_directory_uri() . '/assets/img/logo.svg';
// } );
