<?php
/**
 * Conditional Google Analytics loader.
 *
 * Goal:
 *  - Do not send pageviews/events from non-production hosts (e.g. dev.basecamp.com)
 *  - Seamless go-live: when DNS/domain switches to production host, tracking auto-starts.
 */

if (!defined('ABSPATH')) exit;

// Define your GA4 Measurement ID (change here if needed, not in templates)
if (!defined('BASECAMP_GA_MEASUREMENT_ID')) {
    define('BASECAMP_GA_MEASUREMENT_ID', 'G-NK0DBLFEBM');
}

// List of hostnames treated as production (adjust if needed)
if (!defined('BASECAMP_GA_PROD_HOSTS')) {
    define('BASECAMP_GA_PROD_HOSTS', serialize([
        'basecampdesign.com',
        'www.basecampdesign.com'
    ]));
}

/**
 * Simple host environment check.
 */
function basecamp_is_prod_host(): bool {
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
    $allowed = @unserialize(BASECAMP_GA_PROD_HOSTS) ?: [];
    return in_array($host, $allowed, true);
}

/**
 * Output GA snippet (minimal) – only fully activates on production hosts.
 */
function basecamp_output_ga_snippet() {
    // Fail fast if no ID
    $id = BASECAMP_GA_MEASUREMENT_ID;
    if (!$id) return;

    $is_prod = basecamp_is_prod_host();

    // NEW: resource hints (prod only unless filter overrides)
    if ( $is_prod || apply_filters('basecamp_ga_hints_on_nonprod', false) ) {
        ?>
        <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
        <link rel="dns-prefetch" href="//www.googletagmanager.com">
        <link rel="preconnect" href="https://www.google-analytics.com" crossorigin>
        <link rel="dns-prefetch" href="//www.google-analytics.com">
        <?php
        // Optional preload of gtag.js if explicitly enabled
        if ( apply_filters('basecamp_ga_preload_enabled', false) && $is_prod ) : ?>
            <link rel="preload" as="script" href="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($id); ?>">
        <?php
        endif;
    }
    ?>
    <!-- Google Analytics (conditional) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($id); ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}

    gtag('js', new Date());

    <?php if ($is_prod): ?>
        // Production: enable normal pageview tracking
        gtag('config', '<?php echo esc_js($id); ?>', {
            transport_type: 'beacon'
        });
    <?php else: ?>
        // Dev / Non-prod: GA script loaded but config intentionally skipped
        console.info('[basecamp][GA] Loaded in non-production mode (no hits sent). Host:', location.host);
        // If you want to test sending to a debug view, uncomment below and create a second stream.
        // gtag('config', 'G-XXXXXXXXXX', { debug_mode: true });
    <?php endif; ?>
    </script>
    <?php
}
add_action('wp_head', 'basecamp_output_ga_snippet', 5);
