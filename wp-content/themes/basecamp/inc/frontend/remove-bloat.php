<?php
/**
 * Basecamp Bloat and cleanup.
 * 
 * Clean up the site and remove bloat.
 * @package kane
 */

if ( ! class_exists( 'cleanup' ) ) :

	/**
	 * The main markup cleanup class
	 */
	class cleanup { 

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {

			//add_action( 'language_attributes', array( $this, 'add_opengraph_doctype' ) );
			add_action( 'stop_heartbeat', array( $this, 'stop_heartbeat' ) );
			add_action( 'init', array( $this, 'disable_emojis' ) );
			add_action( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
			add_action( 'wp_resource_hints', array( $this, 'disable_emojis_remove_dns_prefetch' ), 10, 2 );
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
            remove_action('wp_head', 'start_post_rel_link', 10, 0);
            remove_action('wp_head', 'parent_post_rel_link', 10, 0);
            remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
            remove_action('wp_head', 'wp_shortlink_wp_head');
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action( 'wp_head', 'rsd_link' );

            // Remove junk from the head
            add_filter( 'feed_links_show_comments_feed', '__return_false' );
            add_filter( 'the_generator', 'ks_rss_version' );
            add_filter( 'xmlrpc_enabled', '__return_false' );

            add_filter('bloginfo_url', function($output, $property){
                error_log("====property=" . $property);
                return ($property == 'pingback_url') ? null : $output;
              }, 11, 2);

            //Remove REST API link tag
            remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

            // disable REST API link in HTTP headers
            remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );

            // disable oEmbed discovery links
            remove_action( 'wp_head', 'wp_oembed_add_host_js' );
            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

            // Disable converting :) to smileys
            remove_filter('the_content', 'convert_smilies');

            // Disable cookies for comments
            remove_action('set_comment_cookies', 'wp_set_comment_cookies');

            // Remove unwanted SVG filter injection WP
            remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
            remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );

			add_action( 'wp_enqueue_scripts', array( $this, 'remove_useless_styles' ), 20 );
                
            // If Woocommerce - Remove all stylesheets in one line
            //add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

		}

        /**
         * Add Opengraph to html tag
         *
         * @since 2.4.0
         */
        public function add_opengraph_doctype($output){
            return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
        }

        /**
         * Disable Heartbeat API
         */
        public function stop_heartbeat() {
            wp_deregister_script('heartbeat');
        }

        /****************************************
        * REMOVE WP EXTRAS *
        ****************************************/

        /**
         * Disable the emoji's
         */
        public function disable_emojis() {
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
            remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
            remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
            remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
            remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        }

        /**
         * Filter function used to remove the tinymce emoji plugin.
         * 
         * @param array $plugins 
         * @return array Difference betwen the two arrays
         */
        public function disable_emojis_tinymce( $plugins ) {
            if ( is_array( $plugins ) ) {
                return array_diff( $plugins, array( 'wpemoji' ) );
                } else {
                    return array();
                }
        }

        /**
         * Remove emoji CDN hostname from DNS prefetching hints.
         *
         * @param array $urls URLs to print for resource hints.
         * @param string $relation_type The relation type the URLs are printed for.
         * @return array Difference betwen the two arrays.
         */
        public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
            if ( 'dns-prefetch' == $relation_type ) {
                /** This filter is documented in wp-includes/formatting.php */
                $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

                $urls = array_diff( $urls, array( $emoji_svg_url ) );
            }
        return $urls;
        }		

        /**
         * Remove the standard useless WP Styles
         */

        public function remove_useless_styles() {
            wp_dequeue_style( 'classic-theme-styles' );
            wp_dequeue_style( 'global-styles' );

            wp_dequeue_style( 'wp-block-library' ); // Wordpress core styles.min

            wp_dequeue_style( 'select2' );

            wp_deregister_style( 'kaneism-style' );
            wp_dequeue_style( 'kaneism-style' );

            wp_deregister_style( 'kaneism-icons' );
            wp_dequeue_style( 'kaneism-icons' );

            wp_deregister_style( 'brands-styles' );
            wp_dequeue_style( 'brands-styles' );

            // if woocommerce - vendors and wc-blocks
            // wp_deregister_style( 'wc-blocks-style' );
            // wp_dequeue_style( 'wc-blocks-style' );

        }

       
}
endif;

//Remove jQuery migrate
function remove_jquery_migrate( $scripts ) {
    if ( !is_admin() && !empty( $scripts->registered['jquery'] ) ) {
    $scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, ['jquery-migrate'] );
    }
}
add_action('wp_default_scripts', 'remove_jquery_migrate');


// CF7 is loading extra baggage, disabling and loading oldschool in footer.
// add_filter( 'wpcf7_load_js', '__return_false' );
// add_filter( 'wpcf7_load_css', '__return_false' );

// add_action( 'wp_print_scripts', 'my_deregister_javascript', 100 );
// function my_deregister_javascript() {
//   if ( !is_page('Contact') ) {
//     wp_deregister_script( 'contact-form-7' );
//   }
// }

// add_action( 'wp_print_styles', 'my_deregister_styles', 100 );
// function my_deregister_styles() {
//   if ( !is_page('Contact') ) {
//     wp_deregister_style( 'contact-form-7' );
//   }
// }

/**
 * Disable the auto sizes attribute for images
 * 
 * @param array $sizes
 * @return array
 */
add_filter('wp_img_tag_add_auto_sizes', '__return_false');

// Disable Speculative Loading Completely
// add_filter( 'wp_speculation_rules_configuration', '__return_null' );

return new cleanup();

?>