<?php
declare(strict_types=1);
namespace MediaManager\Admin;

use MediaManager\Core\Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin
 *
 * Orchestrates all admin-side modules:
 *   - Instantiates the four Page classes.
 *   - Instantiates Assets and Menu.
 *   - Hooks into admin_menu at priority 11 (after Menu at default 10) to
 *     capture the hook suffixes returned by add_*_page() and pass them to
 *     Assets so enqueues are limited to MM screens only.
 *
 * This class runs only in the admin context; it is gated in class-plugin.php.
 */
final class Admin {

	private Loader       $loader;
	private LibraryPage    $library;
	private SettingsPage   $settings;
	private ThumbnailsPage $thumbnails;
	private BdaPage        $bda;
	private Assets         $assets;
	private Menu           $menu;

	// -----------------------------------------------------------------------

	public function __construct( Loader $loader ) {
		$this->loader = $loader;

		// Page instances — no hooks in constructors; they are render callbacks.
		$this->library    = new LibraryPage();
		$this->settings   = new SettingsPage();
		$this->thumbnails = new ThumbnailsPage();
		$this->bda        = new BdaPage();

		// Assets registers admin_enqueue_scripts via Loader.
		$this->assets = new Assets( $loader );

		// Menu registers admin_menu via Loader and receives page instances as
		// render callbacks. Hook suffixes are NOT available at construction time
		// — they come back from add_*_page() inside admin_menu itself.
		$this->menu = new Menu(
			$loader,
			$this->library,
			$this->settings,
			$this->thumbnails,
			$this->bda
		);

		// Capture hook suffixes after menus are registered (priority 11 > 10).
		$loader->add_action( 'admin_menu', $this, 'capture_hooks', 11 );
	}

	// -----------------------------------------------------------------------

	/**
	 * Hooked to admin_menu at priority 11.
	 *
	 * By this point WordPress has called our priority-10 register_menus()
	 * callback, so we can retrieve the hook suffixes via get_plugin_page_hookname()
	 * and hand them to Assets.
	 */
	public function capture_hooks(): void {
		$hooks = [];

		$library_hook    = get_plugin_page_hookname( Menu::SLUG_ROOT,       '' );
		$settings_hook   = get_plugin_page_hookname( Menu::SLUG_SETTINGS,   Menu::SLUG_ROOT );
		$thumbnails_hook = get_plugin_page_hookname( Menu::SLUG_THUMBNAILS, Menu::SLUG_ROOT );

		$hooks[] = $library_hook;
		$hooks[] = $settings_hook;
		$hooks[] = $thumbnails_hook;

		// Security page is always registered — no conditional gate.
		$bda_hook = get_plugin_page_hookname( Menu::SLUG_BDA, Menu::SLUG_ROOT );
		$hooks[]  = $bda_hook;

		$hooks = array_filter( $hooks );

		$this->assets->set_screen_hooks( $hooks );
		$this->assets->set_library_hook( $library_hook );

		// Register help tabs for each screen.
		$this->register_help_tabs( $library_hook, $settings_hook, $thumbnails_hook, $bda_hook ?? '' );
	}

	// -----------------------------------------------------------------------

	/**
	 * Attach contextual help tabs to each Media Manager admin screen.
	 * Must be called after hook suffixes are known (admin_menu priority 11).
	 */
	private function register_help_tabs(
		string $library_hook,
		string $settings_hook,
		string $thumbnails_hook,
		string $bda_hook
	): void {
		// Library.
		add_action( "load-{$library_hook}", static function (): void {
			$screen = get_current_screen();
			if ( ! $screen ) {
				return;
			}
			$screen->add_help_tab( [
				'id'      => 'mm-library-overview',
				'title'   => __( 'Overview', 'media-manager' ),
				'content' =>
					'<p>' . __( 'The <strong>Media Manager Library</strong> shows all WordPress media files organised into physical disk folders.', 'media-manager' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>Folder tree</strong> — Click any folder to load its files in the grid on the right.', 'media-manager' ) . '</li>' .
					'<li>' . __( '<strong>Right-click a folder</strong> — Create a subfolder, rename, hide, delete, or toggle direct-access protection.', 'media-manager' ) . '</li>' .
					'<li>' . __( '<strong>Upload Files</strong> — Drag files into the upload zone or click to browse. Files are placed into the selected folder.', 'media-manager' ) . '</li>' .
					'<li>' . __( '<strong>Sync Folder</strong> — Imports any files on disk that are not yet in the WordPress media library.', 'media-manager' ) . '</li>' .
					'<li>' . __( '<strong>Rename / Delete</strong> — Select one or more files then use the bulk actions bar.', 'media-manager' ) . '</li>' .
					'</ul>',
			] );
		} );

		// Settings.
		add_action( "load-{$settings_hook}", static function (): void {
			$screen = get_current_screen();
			if ( ! $screen ) {
				return;
			}
			$screen->add_help_tab( [
				'id'      => 'mm-settings-overview',
				'title'   => __( 'Overview', 'media-manager' ),
				'content' =>
					'<p>' . __( 'Configure global behaviour for the Media Manager plugin.', 'media-manager' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>Items per page</strong> — Maximum files displayed per folder in the library grid.', 'media-manager' ) . '</li>' .
					'<li>' . __( '<strong>Disable image scaling</strong> — Prevents WordPress from generating additional image sizes on upload.', 'media-manager' ) . '</li>' .
					'<li>' . __( '<strong>Skip WebP generation</strong> — Prevents WordPress from generating a WebP copy alongside the original on upload.', 'media-manager' ) . '</li>' .
					'</ul>',
			] );
		} );

		// Thumbnails.
		add_action( "load-{$thumbnails_hook}", static function (): void {
			$screen = get_current_screen();
			if ( ! $screen ) {
				return;
			}
			$screen->add_help_tab( [
				'id'      => 'mm-thumbnails-overview',
				'title'   => __( 'Overview', 'media-manager' ),
				'content' =>
					'<p>' . __( 'Regenerate image thumbnails for files in a specific folder or for all media in the library.', 'media-manager' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>Select folder</strong> — Choose a folder to regenerate thumbnails for only the images it contains. Leave unselected to process all images.', 'media-manager' ) . '</li>' .
					'<li>' . __( '<strong>Regenerate</strong> — Processes images in batches. Progress is shown in real time. The page can be left open while it runs.', 'media-manager' ) . '</li>' .
					'</ul>',
			] );
		} );

		// Security (BDA + IP Blocking) — hook is now always registered.
		if ( $bda_hook ) {
			add_action( "load-{$bda_hook}", static function (): void {
				$screen = get_current_screen();
				if ( ! $screen ) {
					return;
				}
				$screen->add_help_tab( [
					'id'      => 'mm-bda-overview',
					'title'   => __( 'Overview', 'media-manager' ),
					'content' =>
						'<p>' . __( 'The Security page controls two independent features that protect your media from unauthorised access.', 'media-manager' ) . '</p>' .
						'<ul>' .
						'<li>' . __( '<strong>Block Direct Access (BDA)</strong> — Writes Apache <code>.htaccess</code> rules that block direct URL access to files inside protected folders. Enable BDA, then right-click any folder in the Library to protect it. Works on Apache only; Nginx requires manual equivalent rules.', 'media-manager' ) . '</li>' .
						'<li>' . __( '<strong>IP Blocking</strong> — Blocks specific IP addresses from all direct media access, regardless of folder protection status. Enable IP blocking, then add addresses in the Blocked IP Addresses table.', 'media-manager' ) . '</li>' .
						'</ul>',
				] );
			} );
		}
	}
}
