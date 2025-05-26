<?php
/**
 * Plugin Name: Layout Tester (Frontend Standalone)
 * Description: Access a minimal layout tester at /layout-tester/ without admin or theme bloat, for testing responsive layouts on various device sizes.
 * Version: 1.0.1
 * Author: Dylan Pitchford
 */

if (!defined('ABSPATH')) exit;

// Register rewrite rule on activation
register_activation_hook(__FILE__, function() {
    add_rewrite_rule('^layout-tester/?$', 'index.php?layout_tester=1', 'top');
    flush_rewrite_rules();
});

// Remove rewrite rule on deactivation
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Add query var
add_filter('query_vars', function($vars) {
    $vars[] = 'layout_tester';
    return $vars;
});

// Template loader
add_action('template_redirect', function() {
    if (intval(get_query_var('layout_tester')) === 1) {
        $default_url = site_url();
        $test_url = isset($_POST['test_url']) ? esc_url_raw($_POST['test_url']) : $default_url;

        // Prevent recursion: don't allow /layout-tester/ as test_url
        $layout_tester_url = trailingslashit(site_url('layout-tester'));
        $is_recursive = (rtrim($test_url, '/') === rtrim($layout_tester_url, '/'));

        ?>
<!DOCTYPE html>
<html>
<head>
    <title>Layout Tester</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        html{
            padding:20px;
            overflow-y:scroll;
            font:1em/1.5 Georgia, serif;
            color:#333;
            background-color:#fff;
            }
        body{
            margin:0;
        }
        .grid-general{
            display: grid;
            gap: 0.5rem;
            margin-bottom: 1.3rem;
            }

           /* modifier template classes - mobile first, columns are added as the screen size increases */
            .grid--2col{
                grid-template-columns: repeat(2, 1fr);
            }
            .grid--3col{
                grid-template-columns: repeat(3, 1fr);
            }
            .grid--4col{
                grid-template-columns: repeat(4, 1fr);
            }

        .contentwrap{
            width: 100%;
            overflow: hidden;
            clear: both;
        }
        iframe{
            /* display:inline-block; */
            /* margin:0 24px 24px 0; */
            border:1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>Layout Tester</h1>
    <form method="post" action="">
        <label for="test_url"><strong>URL to test:</strong></label>
        <input type="url" id="test_url" name="test_url" value="<?php echo esc_attr($test_url); ?>" style="width:350px;" placeholder="Enter URL to test" required />
        <button type="submit">Update All iFrames</button>
    </form>
    <?php if ($is_recursive): ?>
        <div class="warning">Cannot test the Layout Tester page itself (no recursion allowed).</div>
    <?php endif; ?>
    <p>Enter the URL you want to test in various device sizes.</p>
    <div class="note">
        If the iframes are blank, the site you are testing may block embedding via <code>X-Frame-Options</code> or <code>Content-Security-Policy</code> headers.
    </div>
    <hr>
    <?php if (!$is_recursive): ?>
    <div class="contentwrap">
        <h2>Phone Sizes (Portrait)</h2>
        <div class="grid-general grid--4col">
            
            <iframe src="<?php echo esc_url($test_url); ?>" width="320" height="480"></iframe>
            <iframe src="<?php echo esc_url($test_url); ?>" width="360" height="640"></iframe>
            <iframe src="<?php echo esc_url($test_url); ?>" width="375" height="667"></iframe>
            <iframe src="<?php echo esc_url($test_url); ?>" width="414" height="736"></iframe>
        </div>
    </div>
    <div class="contentwrap">
        <h2>Phone Sizes (Landscape)</h2>
        <div class="grid-general grid--2col">
            <iframe src="<?php echo esc_url($test_url); ?>" width="667" height="375"></iframe>
            <iframe src="<?php echo esc_url($test_url); ?>" width="960" height="600"></iframe>
        </div>
    </div>

    <div class="contentwrap">
        <h2>Tablet Sizes</h2>
        <div class="grid-general grid--2col">
            <iframe src="<?php echo esc_url($test_url); ?>" width="600" height="960"></iframe>
            <iframe src="<?php echo esc_url($test_url); ?>" width="768" height="1024"></iframe>
        </div>
    </div>

    <div class="contentwrap">
        <h2>Tablet Sizes</h2>
        <div class="grid-general grid--2col">
            <iframe src="<?php echo esc_url($test_url); ?>" width="960" height="600"></iframe>
            <iframe src="<?php echo esc_url($test_url); ?>" width="1024" height="768"></iframe>
        </div>
    </div>

    <div class="contentwrap">
        <h2>Desktop Sizes</h2>
        <iframe src="<?php echo esc_url($test_url); ?>" width="1280" height="800"></iframe>
        <iframe src="<?php echo esc_url($test_url); ?>" width="1440" height="900"></iframe>
        <iframe src="<?php echo esc_url($test_url); ?>" width="1920" height="1080"></iframe>
    </div>

    <?php endif; ?>
</body>
</html>

        <?php
        exit;
    }
});
