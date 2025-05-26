<?php
/**
 * Frontend hooks and filters for Basecamp theme.
 *
 * @package basecamp
 */

// Add 'menu--selected' class to anchor tags for active menu items.
add_filter('nav_menu_link_attributes', [ 'Basecamp_Frontend', 'menu_selected_class' ], 99, 4);

// Disable resource hints.
add_filter( 'wp_resource_hints', '__return_empty_array', 99 );

// Disable the auto sizes attribute for images.
add_filter('wp_img_tag_add_auto_sizes', '__return_false');

// Disable speculative loading completely.
// add_filter( 'wp_speculation_rules_configuration', '__return_null' );

// Remove the sidebar from the main query.
function mb_remove_sidebar() { return false; }
add_filter( 'is_active_sidebar', 'mb_remove_sidebar', 10, 2 );

// Social icons in menu (if you use TwentyTwenty SVG system).
if ( class_exists( 'TwentyTwenty_SVG_Icons' ) ) {
	function twentytwenty_nav_menu_social_icons( $item_output, $item, $depth, $args ) {
		if ( 'social' === $args->theme_location ) {
			$svg = TwentyTwenty_SVG_Icons::get_social_link_svg( $item->url );
			if ( empty( $svg ) ) {
				$svg = twentytwenty_get_theme_svg( 'link' );
			}
			$item_output = str_replace( $args->link_after, '</span>' . $svg, $item_output );
		}
		return $item_output;
	}
	add_filter( 'walker_nav_menu_start_el', 'twentytwenty_nav_menu_social_icons', 10, 4 );
}
