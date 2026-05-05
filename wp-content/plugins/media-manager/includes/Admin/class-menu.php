<?php
declare(strict_types=1);
namespace MediaManager\Admin;

use MediaManager\Core\Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu
 *
 * Registers the top-level "Media Manager" admin menu and its four submenus.
 * Page render callbacks delegate to the individual Page classes, which are
 * injected via the constructor so Menu itself stays decoupled from rendering.
 *
 * Capability gates (per PRD):
 *   Library     → edit_others_posts  (Editors+)
 *   Settings    → manage_options     (Admins only)
 *   Thumbnails  → manage_options
 *   BDA         → manage_options     (hidden when BDA disabled)
 */
final class Menu {

	/** Slug of the top-level menu page (doubles as the Library page slug). */
	const SLUG_ROOT       = 'media-manager';
	const SLUG_SETTINGS   = 'media-manager-settings';
	const SLUG_THUMBNAILS = 'media-manager-thumbnails';
	const SLUG_BDA        = 'media-manager-bda';

	private LibraryPage    $library;
	private SettingsPage   $settings;
	private ThumbnailsPage $thumbnails;
	private BdaPage        $bda;

	// -----------------------------------------------------------------------

	public function __construct(
		Loader $loader,
		LibraryPage $library,
		SettingsPage $settings,
		ThumbnailsPage $thumbnails,
		BdaPage $bda
	) {
		$this->library    = $library;
		$this->settings   = $settings;
		$this->thumbnails = $thumbnails;
		$this->bda        = $bda;

		$loader->add_action( 'admin_menu', $this, 'register_menus' );
	}

	// -----------------------------------------------------------------------

	/**
	 * Hooked to admin_menu.
	 */
	public function register_menus(): void {
		// Top-level entry — renders the Library page.
		add_menu_page(
			__( 'Media Manager', 'media-manager' ),  // page title
			__( 'Media Manager', 'media-manager' ),  // menu label
			'edit_others_posts',                      // minimum capability
			self::SLUG_ROOT,
			[ $this->library, 'render' ],
			'dashicons-format-gallery',
			58  // position — below Media (60), above Appearance (60)
		);

		// Submenu: Library (re-registers the top-level slug so the label reads
		// "Library" rather than repeating "Media Manager").
		add_submenu_page(
			self::SLUG_ROOT,
			__( 'Library', 'media-manager' ),
			__( 'Library', 'media-manager' ),
			'edit_others_posts',
			self::SLUG_ROOT,
			[ $this->library, 'render' ]
		);

		// Submenu: Settings.
		add_submenu_page(
			self::SLUG_ROOT,
			__( 'Settings', 'media-manager' ),
			__( 'Settings', 'media-manager' ),
			'manage_options',
			self::SLUG_SETTINGS,
			[ $this->settings, 'render' ]
		);

		// Submenu: Thumbnails.
		add_submenu_page(
			self::SLUG_ROOT,
			__( 'Thumbnails', 'media-manager' ),
			__( 'Thumbnails', 'media-manager' ),
			'manage_options',
			self::SLUG_THUMBNAILS,
			[ $this->thumbnails, 'render' ]
		);

		// Submenu: Security (BDA + IP blocking) — always visible.
		add_submenu_page(
			self::SLUG_ROOT,
			__( 'Security', 'media-manager' ),
			__( 'Security', 'media-manager' ),
			'manage_options',
			self::SLUG_BDA,
			[ $this->bda, 'render' ]
		);
	}
}

