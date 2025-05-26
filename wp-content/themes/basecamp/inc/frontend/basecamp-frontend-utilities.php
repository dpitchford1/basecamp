<?php
/**
 * Basecamp Frontend Utilities
 *
 * Breadcrumbs, excerpt, accessibility, widgets, and more.
 *
 * @package basecamp
 */

// Custom excerpt length and read more link
add_filter('excerpt_length', function() { return 30; }, 99);
add_filter('excerpt_more', function($more) {
	return '... <a class="read-more" href="' . get_permalink() . '">' . __('Read More', 'basecamp') . '</a>';
});
