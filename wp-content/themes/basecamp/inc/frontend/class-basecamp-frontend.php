<?php
/**
 * Frontend functionality for Basecamp theme.
 *
 * @package basecamp
 */

class Basecamp_Frontend {

	/**
	 * Initialize hooks.
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'start_output_buffer' ] );

		// Frontend hooks and filters
		add_filter('nav_menu_link_attributes', [ __CLASS__, 'menu_selected_class' ], 99, 4);
		add_filter('wp_resource_hints', '__return_empty_array', 99);
		add_filter('wp_img_tag_add_auto_sizes', '__return_false');
		add_filter('wp_speculation_rules_configuration', '__return_null');
		add_filter('is_active_sidebar', [ __CLASS__, 'remove_sidebar' ], 10, 2);

		// Social icons in menu (Basecamp SVG system)
		if ( class_exists( 'Basecamp_SVG_Icons' ) ) {
			add_filter( 'walker_nav_menu_start_el', [ __CLASS__, 'basecamp_nav_menu_social_icons' ], 10, 4 );
		}
	}

	/**
	 * Start output buffering to clean up self-closing tags in HTML output.
	 */
	public function start_output_buffer() {
		ob_start( [ $this, 'remove_trailing_slash_on_html_tags' ] );
	}

	/**
	 * Remove trailing slashes and spaces from self-closing HTML tags (e.g., <br />, <img />).
	 *
	 * @param string $content HTML content.
	 * @return string
	 */
	public function remove_trailing_slash_on_html_tags( $content ) {
		return preg_replace( '/<(img|br|hr|input|meta|link)([^>]*)\s+\/>/', '<$1$2>', $content );
	}

	/**
	 * Add schema.org markup to the html tag.
	 */
	public static function html_schema() {
		$schema = 'http://schema.org/';
		if ( is_single() ) {
			$type = "Article";
		} elseif ( is_home() || is_archive() || is_category() ) {
			$type = "WebPage";
		} elseif ( is_front_page() ) {
			$type = "WebPage";
		} else {
			$type = 'WebPage';
		}
		echo 'itemscope="itemscope" itemtype="' . $schema . $type . '"';
	}

	/**
	 * Gets a nicely formatted string for the published date.
	 */
	public static function template_time_link() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		$time_string = sprintf(
			$time_string,
			get_the_date( DATE_W3C ),
			get_the_date(),
			get_the_modified_date( DATE_W3C ),
			get_the_modified_date()
		);
		return sprintf(
			/* translators: %s: post date */
			__( '<span class="hide-text">Posted on</span> %s', 'basecamp' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);
	}

	/**
	 * Display post thumbnail.
	 *
	 * @param string $size The post thumbnail size.
	 */
	public static function post_thumbnail( $size = 'full' ) {
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( $size );
		}
	}

	/**
	 * Output critical CSS inline in the head.
	 * @param string $css_file_path Absolute path to the critical CSS file.
	 * @param string $transient_key Unique key for the transient.
	 */
	public static function output_critical_css( $css_file_path, $transient_key = 'basecamp_critical_css' ) {
		$css = get_transient( $transient_key );
		$file_mtime = file_exists( $css_file_path ) ? filemtime( $css_file_path ) : 0;

		if ( false === $css || get_transient( $transient_key . '_mtime' ) !== $file_mtime ) {
			$css = file_exists( $css_file_path ) ? file_get_contents( $css_file_path ) : '';
			$css = preg_replace( '/\s+/', ' ', $css ); // Simple minification
			set_transient( $transient_key, $css, DAY_IN_SECONDS );
			set_transient( $transient_key . '_mtime', $file_mtime, DAY_IN_SECONDS );
		}

		if ( $css ) {
			echo '<style id="critical-css">' . $css . '</style>';
		}
	}

	/**
	 * Add 'menu--selected' class to anchor tags for active menu items.
	 */
	public static function menu_selected_class($atts, $item, $args, $depth) {
		$item_classes = is_array($item->classes) ? $item->classes : array();
		$active_classes = array(
			'current-menu-item',
			'current-menu-ancestor',
			'current-menu-parent',
			'current_page_item',
			'current_page_parent',
			'current_page_ancestor'
		);
		$is_active = false;
		foreach ($active_classes as $class) {
			if (in_array($class, $item_classes)) {
				$is_active = true;
				break;
			}
		}
		if (!isset($atts['class'])) {
			$atts['class'] = '';
		}
		if ($is_active) {
			$atts['class'] .= ' menu--selected';
			$atts['class'] = trim($atts['class']);
		} else {
			$atts['class'] = trim($atts['class']);
		}
		return $atts;
	}

	/**
	 * Remove the sidebar from the main query.
	 */
	public static function remove_sidebar() {
		return false;
	}

	/**
	 * Displays SVG icons in social links menu (for Basecamp).
	 */
	public static function basecamp_nav_menu_social_icons( $item_output, $item, $depth, $args ) {
		if ( 'social' === $args->theme_location ) {
			$svg = \Basecamp_SVG_Icons::get_social_link_svg( $item->url );
			if ( empty( $svg ) && function_exists( 'basecamp_get_theme_svg' ) ) {
				$svg = \basecamp_get_theme_svg( 'link' );
			}
			$item_output = str_replace( $args->link_after, '</span>' . $svg, $item_output );
		}
		return $item_output;
	}

	/**
	 * Numeric Page Navigation.
	 *
	 * @param WP_Query|null $query Optionally pass a custom query object.
	 * @param array $args Optional. Additional paginate_links args.
	 */
	public static function page_navi( $query = null, $args = [] ) {
		$query = $query instanceof WP_Query ? $query : $GLOBALS['wp_query'];
		$total_pages = isset( $query->max_num_pages ) ? (int) $query->max_num_pages : 1;
		if ( $total_pages <= 1 ) {
			return;
		}

		$bignum = 999999999;
		$defaults = [
			'base'      => str_replace( $bignum, '%#%', esc_url( get_pagenum_link( $bignum ) ) ),
			'format'    => '',
			'current'   => max( 1, get_query_var( 'paged' ) ),
			'total'     => $total_pages,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'type'      => 'list',
			'end_size'  => 3,
			'mid_size'  => 3,
			'add_args'  => false,
		];
		$paginate_args = wp_parse_args( $args, $defaults );

		$links = paginate_links( $paginate_args );
		if ( $links ) {
			echo '<nav class="paged--pagination" aria-label="' . esc_attr__( 'Pagination', 'basecamp' ) . '">';
			echo $links;
			echo '</nav>';
		}
	}

	/**
	 * Display related posts based on shared tags.
	 */
	public static function related_posts( $args = [] ) {
		global $post;
		if ( ! $post ) {
			return;
		}

		$default_args = [
			'posts_per_page' => apply_filters( 'basecamp_related_posts_count', 5 ),
			'post__not_in'   => [ $post->ID ],
			'tag__in'        => [],
			'fields'         => 'ids',
			'ignore_sticky_posts' => true,
			'no_found_rows'  => true,
		];

		$tags = wp_get_post_tags( $post->ID, [ 'fields' => 'ids' ] );
		if ( empty( $tags ) ) {
			echo '<ul id="related--posts"><li class="not--related">' . esc_html__( 'No Related Posts Yet!', 'basecamp' ) . '</li></ul>';
			return;
		}

		$query_args = wp_parse_args( [
			'tag__in' => $tags,
		], $default_args, $args );

		$related_query = new WP_Query( $query_args );

		echo '<ul id="related--posts">';
		if ( $related_query->have_posts() ) {
			foreach ( $related_query->posts as $related_post_id ) {
				$title = get_the_title( $related_post_id );
				$permalink = get_permalink( $related_post_id );
				echo '<li class="is--related"><a class="related" href="' . esc_url( $permalink ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</a></li>';
			}
		} else {
			echo '<li class="not--related">' . esc_html__( 'No Related Posts Yet!', 'basecamp' ) . '</li>';
		}
		echo '</ul>';

		wp_reset_postdata();
	}
}
