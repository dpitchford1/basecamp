<!doctype html>
<html class="no-js dark" dir="ltr" <?php language_attributes(); ?> <?php Basecamp_Frontend::html_schema(); ?> data-off-canvas="" id="site-body">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php /* Mobile */ ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php /* service worker */ ?>
<script>if (navigator && navigator.serviceWorker) { navigator.serviceWorker.register('/worker.kaneism.min.js'); }</script>
<script>var doc = window.document; doc.documentElement.className = document.documentElement.className.replace(/\bno-js\b/g, '') + 'has-js enhanced';</script>

<link rel="preload" href="/assets/kaneism/fonts/proxima-thin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/kaneism/fonts/proxima-reg.woff" as="font" type="font/woff2" crossorigin>
<link rel="preload" as="image" href="/assets/kaneism/img/bg/header-bubbles-dark.svg">
<link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
<script src="/assets/kaneism/js/core/themer.min.js" async></script>
<?php /* css injector */ ?>
<?php Basecamp_Frontend::output_critical_css( ABSPATH . 'assets/kaneism/css/build/kaneism-inline-head.min.css', 'kaneism_critical_css' ); ?>

<?php /* css files 
<link rel="stylesheet" href="/assets/kaneism/css/build/01-theme-clean.min.css?v=1" media="screen" as="style">*/ ?>
<link rel="stylesheet" href="/assets/kaneism/css/build/kaneism-base-layout.min.css?v=1" media="screen">
<link rel="stylesheet" href="/assets/kaneism/css/build/kaneism-global-layout.min.css?v=1" media="print" onload="this.onload=null;this.removeAttribute('media');" fetchpriority="high">

<?php /* css files 
<link rel="stylesheet" href="/assets/kaneism/css/build/kaneism-shop-layout.min.css" media="screen">*/ ?>
<?php if ( is_page('about') || is_singular('work') || is_tax('work_category', array('canvases', 'designs', 'videos')) ) : ?>
    <link rel="stylesheet" href="/assets/kaneism/css/build/swiper.min.css" media="screen">
<?php endif; ?>
<?php if (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_product())) : ?>

    <link rel="stylesheet" href="/assets/kaneism/css/build/kaneism-checkout.min.css" media="screen">
    <?php endif; ?>
<noscript><link rel="stylesheet" href="/assets/kaneism/css/build/kaneism-global-layout.min.css" media="screen"></noscript>

<?php /*<link rel="manifest" href="/kaneism.json"> */ ?>
<?php /* favicon */ ?>
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/assets/kaneism/img/icon/safari-pinned-tab.svg" type="image/svg+xml">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/kaneism/img/icon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/kaneism/img/icon/favicon-16x16.png">
<link rel="mask-icon" href="/assets/kaneism/img/icon/safari-pinned-tab.svg" color="#12034a">
<?php /* Theme */ ?>
<link rel="apple-touch-icon" href="/assets/kaneism/img/icon/apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/kaneism/img/icon/apple-touch-icon.png">

<?php /* APPLE SPECIFIC */ ?>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="Kaneism">

<?php /* COPYRIGHTS */ ?>
<meta name="author" content="Kane">
<meta name="copyright" content="© Kaneism designs. All right reserved. <?php echo date('Y'); ?>">
<?php /* SEARCH AND SEO */ ?>
<meta name="robots" content="index, follow, NOODP, noydir">
<meta name="google-site-verification" content="p8odFAK7U2bjGt8jRKBHhKZs7wM-KZ5lkTsQ9kNHGoY">
<?php if ( is_front_page() ) : ?><link rel="home" title="Home page" href="/"><?php endif ?>

<?php wp_head(); ?>

<?php /* WOOCommerce scripts */ ?>
<?php if (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_product())) : ?>
    <?php /* WOOCommerce scripts
    
    <script src="/assets/kaneism/js/resources/jquery.blockUI.min.js" id="jquery-blockui-js" defer data-wp-strategy="defer"></script>
    <script src="/assets/kaneism/js/resources/js.cookie.min.js" id="js-cookie-js" defer data-wp-strategy="defer"></script>
    <script src="/wp-content/plugins/woocommerce/assets/kaneism/js/frontend/add-to-cart.min.js"></script>

     
    <script src="/wp-includes/js/jquery/jquery.min.js" id="jquery-core-js"></script>
    
    <script id="wc-add-to-cart-js-extra">var wc_add_to_cart_params = {"ajax_url":"\/wp-admin\/admin-ajax.php","wc_ajax_url":"\/?wc-ajax=%%endpoint%%","i18n_view_cart":"View cart","cart_url":"https:\/\/live.local\/cart\/","is_cart":"","cart_redirect_after_add":"no"};</script>

    <script src="/assets/kaneism/js/resources/jquery.blockUI.min.js" id="jquery-blockui-js" defer data-wp-strategy="defer"></script>

    <script id="woocommerce-js-extra">var woocommerce_params = {"ajax_url":"\/wp-admin\/admin-ajax.php","wc_ajax_url":"\/?wc-ajax=%%endpoint%%","i18n_password_show":"Show password","i18n_password_hide":"Hide password"};</script>*/ ?>
<?php endif; ?>
</head>

<body <?php body_class(''); ?> data-off-screen="hidden" id="page-body" data-theme="light">
<a href="#global-header" id="exit-off-canvas" class="exit-offcanvas" aria-controls="global-header"><span class="hide-text">Hide Menu</span></a>
<?php /* accessibility nav */ ?>
<a class="quick-links" href="#main-content">Skip to Main Content</a>
<a class="quick-links" href="#global-footer">Skip to Footer</a>
<?php /* small screen header bar */ ?>
<div class="region is--fixed global-header--ss" id="global-header--ss"><span class="hide-text">Kane</span></div>

<?php /* Header Start */ ?>
<div class="region is--fixed global-header" data-nav-slide="slide" id="global-header">
	<header class="brand-header fluid ov cf">
		<?php if ( is_front_page() ) : ?>
			<h1 class="brand brand-fs" id="logo" itemscope itemtype="http://schema.org/Organization"><span class="is--logo">Kaneism Design</span></h1>
		<?php else : ?>
			<h1 class="brand brand-fs" id="logo" itemscope itemtype="http://schema.org/Organization"><a class="is--logo" href="/" rel="home">Kaneism Design</a></h1>
		<?php endif ?>
		<?php /* Theme Switcher */ ?>
		<div class="theme-switcher">
			<div class="theme-toggle theme-toggle--small">
				<input class="theme-checkbox" id="b" type="checkbox">
				<label class="theme-label" for="b">
					<span class="theme-toggle--label is--block">Dark Mode</span>
					<span class="theme-toggle--switch" data-checked="On" data-unchecked="Off"></span>
				</label>
			</div>
        </div>
        <?php /* Utility Nav */ ?>
	    <nav class="menu-utilities cf" itemscope itemtype="http://www.schema.org/SiteNavigationElement" aria-label="Utility Navigation">
	        <p class="hide-text">Submenu:</p>
		    <?php 
				wp_nav_menu( 
					array(
						'theme_location'  => 'utility',
						'menu_class' => 'utility-menu',
						'menu_id' => 'utility-menu',
						'container' => false
					)
				);
            ?>
	    </nav>
        <?php /* Site Search */ ?>
        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
		<?php get_product_search_form(); ?>
        <?php else : ?>
        <?php get_search_form(); ?>
        <?php endif ?>
    </header>
    <?php /* Global Menus */ ?>
    <div class="menu-global">
		<div class="fluid cf" role="navigation" itemscope itemtype="http://www.schema.org/SiteNavigationElement">
		    <h2 class="hide-text">Main Menu</h2>
		    
        <?php if ( is_front_page() ) : ?>
			<div class="menu-logo"></div>
		<?php else : ?>
			<a class="menu-logo" href="/" rel="home" aria-label="Home button"></a>
		<?php endif ?>
		    <?php 
				wp_nav_menu( 
					array(
						'theme_location'  => 'primary',
						'menu_class' => 'navigation-global',
						'menu_id' => 'primary-menu',
						'container' => 'ul'
					)
				);
			?>
            <?php if ( class_exists( 'WooCommerce' ) ) : ?><?php /* if WC is active */ ?>
            <?php if ( !WC()->cart->is_empty() ) : ?><?php /* cart not empty */ ?>
		    <p class="hide-text">Your Cart:</p>
            <p class="cart--bubble">
                <a class="bubble--contents <?php if (is_page('cart')) { echo 'is--selected'; } ?>" href="/cart/">Cart <span class="bubble--count"><?php echo sprintf ( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count() ), WC()->cart->get_cart_contents_count() ); ?></span>
			</a></p>
            <div class="header--cart">
                <a class="cart--content <?php if (is_page('cart')) { echo 'is--selected'; } ?>" href="<?php echo wc_get_cart_url(); ?>" title="View your shopping cart"><span class="cart--label">Cart:</span> <span class="count"><?php echo sprintf ( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count() ), WC()->cart->get_cart_contents_count() ); ?> - <?php echo WC()->cart->get_cart_total(); ?></span></a>
            </div>
            <?php endif ?><?php /* cart not empty */ ?>
            <?php endif ?><?php /* if WC is active */ ?>
		</div>
	</div>
</div>
<?php /* Header End */ ?>
<hr class="hide-divider">
<div id="page" class="hfeed fluid inner-content">
<?php /* Header End 
    <div id="inner-header" class="wrap cf">
        <p class="site-description"><?php bloginfo('description'); ?></p>
    </div> */ ?>
