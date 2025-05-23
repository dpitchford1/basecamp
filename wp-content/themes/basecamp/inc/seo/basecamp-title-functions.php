<?php
/**
 * Basecamp Title System (Extensible)
 *
 * @package basecamp
 */

// Core fallback logic
class Basecamp_Title_Core {
	public static function maybe_title($title) {
		$site_name = get_bloginfo('name');
		// If title is empty and we're on a singular page, use the post title
		if (empty($title) && is_singular()) {
			$title = get_the_title();
		}
		// If still empty, fallback to site name
		if (empty($title)) {
			return $site_name;
		}
		if (strpos($title, $site_name) === false) {
			return "$title - $site_name";
		}
		return $title;
	}
}

// Example: Work extension
class Basecamp_Title_Work {
	public static function maybe_title($title) {
		if (is_singular('work') || is_tax('work_category') || is_post_type_archive('work')) {
			$site_name = get_bloginfo('name');
			if (is_singular('work')) {
				$post_title = get_the_title();
				return "$post_title - Work - $site_name";
			} elseif (is_tax('work_category')) {
				$term = get_queried_object();
				return "{$term->name} - Work - $site_name";
			} elseif (is_post_type_archive('work')) {
				return "Work - $site_name";
			}
		}
		return null;
	}
}

// Example: WooCommerce extension
class Basecamp_Title_Woo {
	public static function maybe_title($title) {
		if (function_exists('is_woocommerce') && (is_woocommerce() || is_shop() || is_product_category() || is_product_tag())) {
			$site_name = get_bloginfo('name');
			if (is_product()) {
				$post_title = get_the_title();
				return "$post_title - Shop - $site_name";
			} elseif (is_product_category() || is_product_tag()) {
				$term = get_queried_object();
				return "{$term->name} - Shop - $site_name";
			} elseif (is_shop()) {
				return "Shop - $site_name";
			}
		}
		return null;
	}
}

// Example: Recipe extension (for future use)
/*
class Basecamp_Title_Recipe {
	public static function maybe_title($title) {
		if (is_singular('recipe')) {
			$site_name = get_bloginfo('name');
			$post_title = get_the_title();
			return "$post_title - Recipe - $site_name";
		}
		return null;
	}
}
*/

// The global manager
class Basecamp_Title_Manager {
	protected static $extensions = [
		'Basecamp_Title_Work',
		'Basecamp_Title_Woo',
		// 'Basecamp_Title_Recipe', // Add more as needed
	];

	public static function init() {
		add_filter('pre_get_document_title', [__CLASS__, 'filter_title'], 1);
		add_filter('wp_title', [__CLASS__, 'filter_wp_title'], 1, 2);
	}

	public static function filter_title($title) {
		foreach (self::$extensions as $ext) {
			if (class_exists($ext) && is_callable([$ext, 'maybe_title'])) {
				$result = $ext::maybe_title($title);
				if (null !== $result) {
					return $result;
				}
			}
		}
		return Basecamp_Title_Core::maybe_title($title);
	}

	public static function filter_wp_title($title, $sep) {
		return self::filter_title($title);
	}
}

// Register the global manager
Basecamp_Title_Manager::init();
