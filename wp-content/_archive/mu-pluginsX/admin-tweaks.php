<?php
/*
Plugin Name:        Admin Tweaks
Plugin URI:         
Description:        Tweaks for the WP Admin
Version:            1.0.0
Author:             Dylan Pitchford
Author URI:         
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hide always all email address encoder notifications
define( 'EAE_DISABLE_NOTICES', apply_filters( 'air_helper_remove_eae_admin_bar', true ) );



if ( ! class_exists( 'admintweaks' ) ) :

	/**
	 * The main markup cleanup class
	 */
	class admintweaks {

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {

            add_action( 'login_enqueue_scripts', array( $this, 'wpb_login_logo' ) );
            add_action( 'admin_menu', array( $this, 'air_helper_wphidenag' ) );
            add_filter( 'update_footer', '__return_empty_string', 11 );
            add_action( 'admin_bar_menu', array( $this, 'replace_howdy' ) );
            add_action( 'admin_footer_text', array( $this, 'template_custom_admin_footer' ) );
            add_action( 'tiny_mce_before_init', array( $this, 'cleanup_mce' ) );
            add_action( 'wp_print_scripts', array( $this, 'DisableAutoSave' ) );
            add_action( 'transition_post_status', array( $this, 'remove_transient_on_publish' ), 10, 3 );
            add_action( 'admin_menu', array( $this, 'hide_unnecessary_wordpress_menus' ), 999 );

            add_filter( 'auto_update_plugin', '__return_false' );
            add_filter( 'auto_update_theme', '__return_false' );

            add_action('wp_before_admin_bar_render', array( $this, 'remove_comments_from_admin_bar' ) );

            add_action( 'woocommerce_admin_features', array( $this, 'disable_marketing_feature' ), 999 );
		}

/**
 * Replace the default Admin login logo
 * @since  0.1.0
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
 *
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_wphidenag' )`
 *
 * @since  0.1.0
 */
public function air_helper_wphidenag() {
    remove_action( 'admin_notices', 'update_nag' );
} // end air_helper_wphidenag

/*
*   Replace Howdy in LogIn menu
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

/*
*   Custom Backend Footer
*/
public function template_custom_admin_footer() {
    _e( '<span id="footer-thankyou">Kaneism Design', 'basecamp' );
}

/*
*   Remove H1 from editor
*/
public function cleanup_mce($args) {
    // Just omit h1 from the list
    $args['block_formats'] = 'Paragraph=p;Heading 3=h3;Heading 4=h4; Heading 5=h5; Heading 6=h6';
    return $args;
}

/*
*   Disable autosave
*/
public function DisableAutoSave() {
    wp_deregister_script('autosave');
}

/*
*   Remove comments from admin bar
*/
public function remove_comments_from_admin_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}


/*
*   Remove Transients on publish
*/
public function remove_transient_on_publish( $new, $old, $post ) {
    if( 'publish' == $new )
        delete_transient( 'recent_posts_query_results' );
}



/*
*   Hide Unnecessary Menus and Sub Menus
*/
function hide_unnecessary_wordpress_menus(){
    global $submenu;
    global $current_user;
    wp_get_current_user();

    foreach($submenu['themes.php'] as $menu_index => $theme_menu){
        if(
            $theme_menu[0] == 'Header' || 
            $theme_menu[0] == 'Background' || 
            $theme_menu[0] == 'Customize' || 
            $theme_menu[0] == 'Theme File Editor' ||
            $theme_menu[0] == 'Patterns' ||
            $theme_menu[0] == 'Marketing' ||
            //$theme_menu[0] == 'Discussion' || 
            $theme_menu[0] == 'basecamp')
            unset($submenu['themes.php'][$menu_index]);
    };
        // Hide Comments
        remove_menu_page( 'edit-comments.php' );
        remove_submenu_page( 'options-general.php', 'options-discussion.php');
}

function disable_marketing_feature($features){
    $key = array_search('marketing', $features);
    if ( ! empty($key) ) {
        unset($features[$key]);
    }
    return $features;
}

function hide_unnecessary_wordpress_menus2() {
  
    // Use this for specific user role. Change site_admin part accordingly
    if (current_user_can('site_admin')) {
      
    // Uncomment the part below if you need it to specific user. Change username "demouser"
    /* $user = wp_get_current_user();
    if($user && isset($user->user_login) && 'customer' == $user->user_login) { */
       
    /* DASHBOARD */
    // remove_menu_page( 'index.php' ); // Dashboard + submenus
    // remove_menu_page( 'about.php' ); // WordPress menu
    //remove_submenu_page( 'index.php', 'update-core.php');  // Update
             
    /* WP DEFAULT MENUS */
    remove_menu_page( 'edit-comments.php' ); //Comments
    //remove_menu_page( 'plugins.php' ); //Plugins
    //remove_menu_page( 'tools.php' ); //Tools
    //remove_menu_page( 'users.php' ); //Users
    // remove_menu_page( 'edit.php' ); //Posts
    // remove_menu_page( 'upload.php' ); //Media
    // remove_menu_page( 'edit.php?post_type=page' ); //Pages
    // remove_menu_page( 'themes.php' ); //Appearance
    // remove_menu_page( 'options-general.php' ); //Settings
    
    /* SETTINGS PAGE SUBMENUS */
    //remove_submenu_page( 'options-general.php', 'options-permalink.php');  // Permalinks
    //remove_submenu_page( 'options-general.php', 'options-writing.php');  // Writing
    //remove_submenu_page( 'options-general.php', 'options-reading.php');  // Reading
    //remove_submenu_page( 'options-general.php', 'options-discussion.php');  // Discussion
    //remove_submenu_page( 'options-general.php', 'options-media.php');  // Media
    //remove_submenu_page( 'options-general.php', 'options-general.php');  // General
    //remove_submenu_page( 'options-general.php', 'options-privacy.php');  // Privacy
      
    /* APPEARANCE SUBMENUS */
    // remove_submenu_page( 'themes.php', 'widgets.php' ); // hide Widgets
    // remove_submenu_page( 'themes.php', 'nav-menus.php' ); // hide Menus
    //remove_submenu_page( 'themes.php', 'themes.php' ); // hide the theme selection submenu
    //remove_submenu_page('themes.php', 'theme-editor.php'); // hide Theme editor
       
    /* HIDE CUSTOMIZER MENU */
    $customizer_url = add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'customize.php' );
    remove_submenu_page( 'themes.php', $customizer_url );
    
    /* Plugin related submenus under Settings page */
    //remove_submenu_page( 'options-general.php', 'webpc_admin_page' ); // WebP converter
    //remove_submenu_page( 'options-general.php', 'kadence_blocks' ); // Kadence Blocks
         
    /* 3rd party plugin menus */
    // remove_menu_page( 'snippets' ); // Code snippets
    // remove_menu_page( 'elementor' ); // Elementor
    // remove_menu_page( 'rank-math' ); // Rank Math
    // remove_menu_page( 'Wordfence' ); // Wordfence
    // remove_menu_page( 'WPML' ); // WPML
    // remove_menu_page( 'fluent_forms' ); // Fluent Forms
    // remove_menu_page( 'ct-dashboard' ); // Blocksy
        }
    }


}
endif;

return new admintweaks();


?>