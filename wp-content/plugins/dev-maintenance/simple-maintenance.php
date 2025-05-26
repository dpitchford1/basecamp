<?php
/*
Plugin Name: Development mode
Description: Enables maintenance mode for all visitors except logged-in users and allows access to wp-admin. Robots told to go away for a year.
Version: 1.0
Author: Dylan Pitchford
*/

add_action('template_redirect', function() {
    // Allow logged-in users and login/admin pages
    if (is_user_logged_in() || is_admin() || preg_match('/(wp-login\.php|wp-admin)/i', $_SERVER['REQUEST_URI'])) {
        return;
    }

    // Set 503 header for search engines, go away for a year
    status_header(503);
    header('Retry-After: 31536000'); // 1 year in seconds

    // Output maintenance message
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Maintenance</title></head><body style="font-family:sans-serif;text-align:center;padding:4em;">
        <h1>Site Under Maintenance</h1>
        <p>We&rsquo;re performing scheduled maintenance.<br>Please check back soon.</p>
    </body></html>';
    exit;
});
