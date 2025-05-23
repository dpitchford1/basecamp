<?php
/**
 * Custom REST API endpoints for Basecamp theme.
 *
 * @package basecamp
 */

add_action('rest_api_init', function() {
    error_log('WordPress REST API initialization');
    register_rest_route('test/v1', '/ping', [
        'methods' => 'GET',
        'callback' => function() {
            return ['status' => 'ok'];
        },
        'permission_callback' => '__return_true'
    ]);
});
