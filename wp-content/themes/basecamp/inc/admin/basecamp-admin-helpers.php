<?php
/**
 * Admin and sanitization helpers for Basecamp theme.
 *
 * @package basecamp
 */




/**
 * Sanitizes choices (selects / radios)
 */
function kaneism_sanitize_choices( $input, $setting ) {
	$input = sanitize_key( $input );
	$choices = $setting->manager->get_control( $setting->id )->choices;
	return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

/**
 * Checkbox sanitization callback.
 */
function kaneism_sanitize_checkbox( $checked ) {
	return ( ( isset( $checked ) && true === $checked ) ? true : false );
}

/**
 * Increases PHP execution time limit for admin operations.
 */
function basecamp_increase_admin_timeout() {
	if (is_admin()) {
		global $pagenow;
		if (in_array($pagenow, array('post.php', 'post-new.php', 'edit.php'))) {
			@set_time_limit(300);
			if (defined('WP_DEBUG') && WP_DEBUG) {
				$post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'post';
				error_log('[TIMEOUT] Increased execution time limit for post type: ' . $post_type);
			}
		}
	}
}
add_action('admin_init', 'basecamp_increase_admin_timeout', 5);

/**
 * Add new mime types.
 */
function my_myme_types($mime_types){
	$mime_types['svg'] = 'image/svg+xml';
	return $mime_types;
}
add_filter('upload_mimes', 'my_myme_types');
