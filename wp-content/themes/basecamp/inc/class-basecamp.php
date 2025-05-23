<?php
/**
 * Basecamp Class
 *
 * @since    2.0.0
 * @package  basecamp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'basecamp' ) ) :

	/**
	 * The main basecamp class
	 */
	class basecamp {

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'setup' ) );
            add_filter( 'body_class', array( $this, 'body_classes' ) );

			//add_action( 'widgets_init', array( $this, 'widgets_init' ) );
			add_filter( 'wp_page_menu_args', array( $this, 'page_menu_args' ) );
		}

		/**
		 * Sets up theme defaults and registers support for various WordPress features.
		 *
		 * Note that this function is hooked into the after_setup_theme hook, which
		 * runs before the init hook. The init hook is too late for some features, such
		 * as indicating support for post thumbnails.
		 */
		public function setup() {
			/*
			 * Load Localisation files.
			 *
			 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
			 */

			// Loads wp-content/languages/themes/basecamp-it_IT.mo.
			load_theme_textdomain( 'basecamp', trailingslashit( WP_LANG_DIR ) . 'themes' );

			// Loads wp-content/themes/child-theme-name/languages/it_IT.mo.
			load_theme_textdomain( 'basecamp', get_stylesheet_directory() . '/languages' );

			// Loads wp-content/themes/basecamp/languages/it_IT.mo.
			load_theme_textdomain( 'basecamp', get_template_directory() . '/languages' );

            /**
			 * Register menu locations.
			 */
			register_nav_menus(
				apply_filters(
					'basecamp_register_nav_menus',
					array(
						'primary'   => __( 'Primary Menu', 'basecamp' ),
						'utility' => __( 'Secondary Menu', 'basecamp' ),
						'footer'  => __( 'Footer Menu', 'basecamp' ),
                        'social'  => __( 'Social Menu', 'basecamp' )
					)
				)
			);

            /**
			 * Declare support for title theme feature.
			 */
			add_theme_support( 'title-tag' );

            /** 
             * Add support for page excerpts.
             */
            add_post_type_support( 'page', 'excerpt' );

            /*
			 * Switch default core markup for search form, galleries, captions and widgets
			 * to output valid HTML5.
			 */
			add_theme_support(
				'html5',
				apply_filters(
					'basecamp_html5_args',
					array(
						'search-form',
						'gallery',
						'caption',
						'widgets',
						'style',
						'script',
					)
				)
			);

			/*
			 * Enable support for Post Thumbnails on posts and pages.
			 *
			 * @link https://developer.wordpress.org/reference/functions/add_theme_support/#Post_Thumbnails
			 */
			//add_theme_support( 'post-thumbnails' );
            add_theme_support( 'post-thumbnails', array( 'post', 'page' ) );
            set_post_thumbnail_size( 600, 9999 );

            add_image_size( 'basecamp-img-xl', 1400, 800, false );
            add_image_size( 'basecamp-img-lg', 1280, 720, false );
            add_image_size( 'basecamp-img-m', 980, 560, false );
            add_image_size( 'basecamp-img-sm', 600, 343, false );
            add_image_size( 'basecamp-img-s', 400, 229, false );

            add_filter( 'image_size_names_choose', 'template_custom_image_sizes' );

            function template_custom_image_sizes( $sizes ) {
                return array_merge( $sizes, array(
                    'basecamp-img-xl' => __('1400px by 800px', 'basecamp'),
                    'basecamp-img-lg' => __('1280 by 720', 'basecamp'),
                    'basecamp-img-m' => __('980 by 560', 'basecamp'),
                    'basecamp-img-sm' => __('600 by 343', 'basecamp'),
                    'basecamp-img-s' => __('400 by 229', 'basecamp'),
                ) );
            }

			/**
			 * Add support for editor styles.
			 */
			//add_theme_support( 'editor-styles' );

			/**
			 * Enqueue editor styles.
			 */
			//add_editor_style( array( 'assets/css/base/gutenberg-editor.css', $this->google_fonts() ) );

			/**
			 * Add support for responsive embedded content.
			 */
			//add_theme_support( 'responsive-embeds' );

            remove_theme_support( 'widgets-block-editor' );

		}

        /**
		 * Enqueue scripts and styles.
		 *
		 * @since  1.0.0
		 */
		public function scripts() {
			global $basecamp_version;

			/**
			 * Scripts
			 */

		}

        /**
		 * Adds custom classes to the array of body classes.
		 *
		 * @param array $classes Classes for the body element.
		 * @return array
		 */
		public function body_classes( $classes ) {
			// Adds a class to blogs with more than 1 published author.
			if ( is_multi_author() ) {
				$classes[] = 'group-blog';
			}

            if ( is_page('Contact') ) {
				$classes[] = 'is--contact';
			}

			/**
			 * Adds a class when WooCommerce is not active.
			 *
			 * @todo Refactor child themes to remove dependency on this class.
			 */
            if ( is_page('Shop') ) {
				$classes[] = 'has--breadcrumb';
			}
            
			return $classes;
		}

		/**
		 * Register widget area.
		 *
		 * @link https://codex.wordpress.org/Function_Reference/register_sidebar
		 */
		public function widgets_init() {
			$sidebar_args['sidebar'] = array(
				'name'        => __( 'Sidebar', 'basecamp' ),
				'id'          => 'sidebar-1',
				'description' => '',
			);

			$sidebar_args['header'] = array(
				'name'        => __( 'Below Header', 'basecamp' ),
				'id'          => 'header-1',
				'description' => __( 'Widgets added to this region will appear beneath the header and above the main content.', 'basecamp' ),
			);

			$rows    = intval( apply_filters( 'basecamp_footer_widget_rows', 1 ) );
			$regions = intval( apply_filters( 'basecamp_footer_widget_columns', 4 ) );

			for ( $row = 1; $row <= $rows; $row++ ) {
				for ( $region = 1; $region <= $regions; $region++ ) {
					$footer_n = $region + $regions * ( $row - 1 ); // Defines footer sidebar ID.
					$footer   = sprintf( 'footer_%d', $footer_n );

					if ( 1 === $rows ) {
						/* translators: 1: column number */
						$footer_region_name = sprintf( __( 'Footer Column %1$d', 'basecamp' ), $region );

						/* translators: 1: column number */
						$footer_region_description = sprintf( __( 'Widgets added here will appear in column %1$d of the footer.', 'basecamp' ), $region );
					} else {
						/* translators: 1: row number, 2: column number */
						$footer_region_name = sprintf( __( 'Footer Row %1$d - Column %2$d', 'basecamp' ), $row, $region );

						/* translators: 1: column number, 2: row number */
						$footer_region_description = sprintf( __( 'Widgets added here will appear in column %1$d of footer row %2$d.', 'basecamp' ), $region, $row );
					}

					$sidebar_args[ $footer ] = array(
						'name'        => $footer_region_name,
						'id'          => sprintf( 'footer-%d', $footer_n ),
						'description' => $footer_region_description,
					);
				}
			}

			$sidebar_args = apply_filters( 'basecamp_sidebar_args', $sidebar_args );

			foreach ( $sidebar_args as $sidebar => $args ) {
				$widget_tags = array(
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<span class="gamma widget-title">',
					'after_title'   => '</span>',
				);

				/**
				 * Dynamically generated filter hooks. Allow changing widget wrapper and title tags. See the list below.
				 *
				 * 'basecamp_header_widget_tags'
				 * 'basecamp_sidebar_widget_tags'
				 *
				 * 'basecamp_footer_1_widget_tags'
				 * 'basecamp_footer_2_widget_tags'
				 * 'basecamp_footer_3_widget_tags'
				 * 'basecamp_footer_4_widget_tags'
				 */
				$filter_hook = sprintf( 'basecamp_%s_widget_tags', $sidebar );
				$widget_tags = apply_filters( $filter_hook, $widget_tags );

				if ( is_array( $widget_tags ) ) {
					register_sidebar( $args + $widget_tags );
				}
			}
		}

		/**
		 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
		 *
		 * @param array $args Configuration arguments.
		 * @return array
		 */
		public function page_menu_args( $args ) {
			$args['show_home'] = true;
			return $args;
		}

	}
endif;

return new basecamp();
