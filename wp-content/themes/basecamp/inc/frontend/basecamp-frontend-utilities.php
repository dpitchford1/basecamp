<?php
/**
 * Basecamp Frontend Utilities
 *
 * Breadcrumbs, excerpt, accessibility, widgets, and more.
 *
 * @package basecamp
 */

// Breadcrumb navigation
function basecamp_breadcrumbs() {
	echo '<nav class="breadcrumbs" aria-label="Breadcrumbs">';
	echo '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'basecamp') . '</a>';
	if (is_singular()) {
		echo ' &raquo; ';
		the_title();
	}
	// Extend for categories, archives, etc.
	echo '</nav>';
}

// Custom excerpt length and read more link
add_filter('excerpt_length', function() { return 30; }, 99);
add_filter('excerpt_more', function($more) {
	return '... <a class="read-more" href="' . get_permalink() . '">' . __('Read More', 'basecamp') . '</a>';
});

// Accessibility skip link
function basecamp_skip_link() {
	echo '<a class="skip-link screen-reader-text" href="#main-content">' . esc_html__('Skip to content', 'basecamp') . '</a>';
}
add_action('wp_body_open', 'basecamp_skip_link');

// Register a custom widget area
function basecamp_register_widgets() {
	register_sidebar([
		'name'          => __('Footer Widgets', 'basecamp'),
		'id'            => 'footer-widgets',
		'description'   => __('Widgets in the footer area', 'basecamp'),
		'before_widget' => '<div class="footer-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	]);
}
add_action('widgets_init', 'basecamp_register_widgets');
