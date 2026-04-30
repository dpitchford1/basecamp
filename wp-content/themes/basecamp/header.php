<!doctype html>
<html class="no-js" dir="ltr" <?php language_attributes(); ?> <?php Basecamp_Frontend::html_schema(); ?> id="site-body">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php /* Mobile */ ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php /* service worker - uncomment if using
<script>if (navigator && navigator.serviceWorker) { navigator.serviceWorker.register('/worker.min.js'); }</script> */ ?>
<script>var doc = window.document; doc.documentElement.className = document.documentElement.className.replace(/\bno-js\b/g, '') + 'has-js enhanced';</script>

<?php /* inject critical css inline */ ?>
<?php // Basecamp_Frontend::output_critical_css( get_template_directory() . '/assets/css/build/inline-head.min.css' ); ?>

<?php /* css files */ ?>
<link rel="stylesheet" href="/assets/css/build/normalize.min.css" media="screen">
<!-- <link rel="stylesheet" href="/assets/css/build/kaneism-base-layout.min.css" media="screen"> -->

<?php /* favicon */ ?>
<link rel="icon" href="/favicon.ico" sizes="any">
<!-- <link rel="icon" href="/assets/img/icon/safari-pinned-tab.svg" type="image/svg+xml">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/img/icon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/img/icon/favicon-16x16.png">
<link rel="mask-icon" href="/assets/img/icon/safari-pinned-tab.svg" color="#12034a"> -->
<?php /* Theme */ ?>
<!-- <link rel="apple-touch-icon" href="/assets/img/icon/apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/img/icon/apple-touch-icon.png"> -->

<?php /* APPLE SPECIFIC */ ?>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="<?php bloginfo( 'name' ); ?>">

<?php /* COPYRIGHTS */ ?>
<meta name="author" content="<?php bloginfo( 'name' ); ?>">
<meta name="copyright" content="© <?php bloginfo( 'name' ); ?>. All right reserved. <?php echo date('Y'); ?>">
<?php /* SEARCH AND SEO */ ?>
<meta name="robots" content="noindex, nofollow, NOODP, noydir">
<?php if ( is_front_page() ) : ?><link rel="home" title="Home page" href="/"><?php endif ?>

<?php wp_head(); ?>

</head>
<body <?php body_class(); ?>>
<?php /* accessibility nav */ ?>
<a class="quick-links" href="#main-content">Skip to Main Content</a>
<a class="quick-links" href="#global-footer">Skip to Footer</a>
<?php /* Header Start */ ?>
<div class="region is--fixed global-header" data-nav-slide="slide" id="global-header">
	<header class="brand-header fluid ov cf">
		<?php
		/**
		 * Header logo / brand.
		 * Override header.php in a child theme to change the header structure entirely.
		 * Use the 'basecamp_header_logo' filter to swap just the logo markup without a full override.
		 */
		$logo_markup = is_front_page()
			? '<h1 class="brand brand-fs" id="logo" itemscope itemtype="http://schema.org/Organization"><span class="is--logo">' . esc_html( get_bloginfo( 'name' ) ) . '</span></h1>'
			: '<h1 class="brand brand-fs" id="logo" itemscope itemtype="http://schema.org/Organization"><a class="is--logo" href="/" rel="home">' . esc_html( get_bloginfo( 'name' ) ) . '</a></h1>';
		echo apply_filters( 'basecamp_header_logo', $logo_markup );
		?>
        <?php /* Global Menus */ ?>
        <div class="menu-global">
            <div class="cf" role="navigation" itemscope itemtype="http://www.schema.org/SiteNavigationElement">
            <?php /*<h2 class="hide-text">Main Menu</h2>*/ ?>
            <?php if ( is_front_page() ) : ?>
                <!--<div class="menu-logo"></div>-->
            <?php else : ?>
                <!--<a class="menu-logo" href="/" rel="home" aria-label="Home button"></a>-->
            <?php endif ?>
                <?php 
                    wp_nav_menu( 
                        array(
                            'theme_location'  => 'primary',
                            'menu_class' => 'navigation-global is--flex-list',
                            'menu_id' => 'primary-menu',
                            'container' => 'ul'
                        )
                    );
                ?>
            </div>
        </div>
    </header>
</div>
<?php /* Header End */ ?>
<hr class="hide-divider">

