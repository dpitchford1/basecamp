<?php
/**
 * Scheduled events for Basecamp theme.
 *
 * @package basecamp
 */

function basecamp_register_scheduled_events() {
    // Removed webp batch processing hook as it's no longer used
}
add_action( 'init', 'basecamp_register_scheduled_events' );
