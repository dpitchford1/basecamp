<?php
/*------------------------------------ 
 *
 * Handles the admin area and functions for the Basecamp theme.
 * Uses a modern class-based structure for maintainability.
 *
 */

// Hide always all email address encoder notifications
define( 'EAE_DISABLE_NOTICES', apply_filters( 'air_helper_remove_eae_admin_bar', true ) );

/**
 * Class Basecamp_Admin
 *
 * Encapsulates all admin/backend customizations for the Basecamp theme.
 */
if ( ! class_exists( 'Basecamp_Admin' ) ) {
	class Basecamp_Admin {

		/**
		 * Register all admin hooks.
		 */
		public function __construct() {
			// Dashboard and login tweaks
			add_action( 'wp_dashboard_setup', [ $this, 'remove_dashboard_widgets' ] );
			add_action( 'login_enqueue_scripts', [ $this, 'login_css' ], 10 );
			add_action( 'login_enqueue_scripts', [ $this, 'wpb_login_logo' ] );
			add_action( 'admin_menu', [ $this, 'air_helper_wphidenag' ] );
			add_action( 'admin_menu', [ $this, 'hide_unnecessary_wordpress_menus' ], 999 );

			// Admin UI tweaks
			add_filter( 'login_headerurl', [ $this, 'login_url' ] );
			add_filter( 'login_headertitle', [ $this, 'login_title' ] );
			add_action( 'admin_bar_menu', [ $this, 'replace_howdy' ] );
			add_action( 'wp_before_admin_bar_render', [ $this, 'remove_comments_from_admin_bar' ] );
			add_filter( 'admin_footer_text', [ $this, 'custom_admin_footer' ] );
			add_filter( 'update_footer', '__return_empty_string', 11 );

			// Editor and autosave
			add_action( 'tiny_mce_before_init', [ $this, 'cleanup_mce' ] );
			add_action( 'wp_print_scripts', [ $this, 'disable_autosave' ] );
			add_filter('use_block_editor_for_post_type', [ $this, 'disable_block_editor_everywhere' ], 10, 2);

			// Post status and updates
			add_action( 'transition_post_status', [ $this, 'remove_transient_on_publish' ], 10, 3 );
			add_filter( 'auto_update_plugin', '__return_false' );
			add_filter( 'auto_update_theme', '__return_false' );

			// Admin CSS
			add_action( 'login_enqueue_scripts', [ $this, 'admin_css' ], 10 );
		}

		/**
		 * Remove unwanted dashboard widgets.
		 */
		public function remove_dashboard_widgets() {
			remove_meta_box('dashboard_quick_press','dashboard','side');
			remove_meta_box('dashboard_recent_drafts','dashboard','side');
			remove_meta_box('dashboard_primary','dashboard','side');
			remove_meta_box('dashboard_secondary','dashboard','side');
			remove_meta_box('dashboard_incoming_links','dashboard','normal');
			remove_meta_box('dashboard_plugins','dashboard','normal');
			remove_meta_box('dashboard_right_now','dashboard', 'normal');
			remove_meta_box('dashboard_recent_comments','dashboard','normal');
			remove_meta_box('icl_dashboard_widget','dashboard','normal');
			// remove_meta_box('dashboard_activity','dashboard', 'normal');
			// remove_action('welcome_panel','wp_welcome_panel');
		}

		/**
		 * Enqueue custom login CSS.
		 */
		public function login_css() {
			wp_enqueue_style( 'basecamp_login_css', get_basecamp_directory_uri() . '/inc/admin/assets/css/login.css', false );
		}

		/**
		 * Change login logo URL.
		 * @return string
		 */
		public function login_url() {
			return home_url();
		}

		/**
		 * Change login logo title.
		 * @return string
		 */
		public function login_title() {
			return get_option( 'blogname' );
		}

		/**
		 * Enqueue custom admin CSS.
		 */
		public function admin_css() {
			wp_enqueue_style( 'basecamp_admin_css', get_basecamp_directory_uri() . '/inc/admin/assets/css/admin.css', false );
		}

		/**
		 * Custom admin footer text.
		 */
		public function custom_admin_footer() {
			_e( '<span id="footer-thankyou">Developed by <a href="https://kaneism.com" target="_blank">Kaneism</a></span>. Built using <a href="https://studio.bio/basecamp" target="_blank">basecamp</a>.', 'basecamptheme' );
		}

		/**
		 * Replace the default Admin login logo.
		 */
		public function wpb_login_logo() { ?>
			<style type="text/css">
				#login h1 a, .login h1 a {
					background-image: url(/assets/img/logos/login_logo.png);
					height:150px;
					width:300px;
					background-size: 300px auto;
					background-repeat: no-repeat;
				}
			</style>
		<?php }

		/**
		 * Hide WP updates nag.
		 */
		public function air_helper_wphidenag() {
			remove_action( 'admin_notices', 'update_nag' );
		}

		/**
		 * Replace "Howdy" in the admin bar with "Logged in as".
		 * @param WP_Admin_Bar $wp_admin_bar
		 */
		public function replace_howdy( $wp_admin_bar ) {
			$my_account = $wp_admin_bar->get_node( 'my-account' );
			if ( isset( $my_account->title ) ) {
				$wp_admin_bar->add_node( [
					'id'    => 'my-account',
					'title' => str_replace( 'Howdy, ', __( 'Logged in as,', 'text_domain' ), $my_account->title ),
				] );
			}
		}

		/**
		 * Remove H1 from TinyMCE editor.
		 * @param array $args
		 * @return array
		 */
		public function cleanup_mce($args) {
			$args['block_formats'] = 'Paragraph=p;Heading 3=h3;Heading 4=h4; Heading 5=h5; Heading 6=h6';
			return $args;
		}

		/**
		 * Disable autosave script in admin.
		 */
		public function disable_autosave() {
			wp_deregister_script('autosave');
		}

		/**
		 * Remove comments from admin bar.
		 */
		public function remove_comments_from_admin_bar() {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu('comments');
		}

		/**
		 * Remove transients on post publish.
		 * @param string $new
		 * @param string $old
		 * @param WP_Post $post
		 */
		public function remove_transient_on_publish( $new, $old, $post ) {
			if( 'publish' == $new )
				delete_transient( 'recent_posts_query_results' );
		}

		/**
		 * Hide unnecessary menus and submenus in admin.
		 */
		public function hide_unnecessary_wordpress_menus() {
			global $submenu;
			global $current_user;
			wp_get_current_user();

			if ( isset( $submenu['themes.php'] ) ) {
				foreach($submenu['themes.php'] as $menu_index => $theme_menu){
					if(
						$theme_menu[0] == 'Header' || 
						$theme_menu[0] == 'Background' || 
						$theme_menu[0] == 'Customize' || 
						$theme_menu[0] == 'Theme File Editor' ||
						$theme_menu[0] == 'Patterns' ||
						$theme_menu[0] == 'Marketing' ||
						$theme_menu[0] == 'basecamp')
						unset($submenu['themes.php'][$menu_index]);
				}
			}
			remove_menu_page( 'edit-comments.php' );
			remove_submenu_page( 'options-general.php', 'options-discussion.php');
		}

		/**
		 * Disable the block editor for all post types (use classic editor everywhere).
		 * @param bool $use_block_editor
		 * @param string $post_type
		 * @return bool
		 */
		public function disable_block_editor_everywhere($use_block_editor, $post_type) {
			return false;
		}
	}
}

// Instantiate the admin class.
new Basecamp_Admin();
?>