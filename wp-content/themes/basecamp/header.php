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

		<div id="container">

			<?php // Customizer Header Image section. Uncomment to use. ?>
				<!-- <?php if( get_header_image() != "" ) { 

					if ( is_front_page() ) { ?>

            		<div id="banner">                
            			
            			<img class="header-image" src="<?php header_image(); ?>" alt="Header graphic" />                
            			
            		</div>

            	<?php }

            	} ?> -->

			<header class="header">

				<div id="inner-header" class="wrap">

					<?php // You can use text or a logo (or both) in your header. Uncomment the below to use text. ?>
					<!-- <div id="site-title" class="h1"><a href="<?php echo home_url(); ?>" rel="nofollow"><?php bloginfo('name'); ?></a></div> -->

					<div id="logo" itemscope itemtype="http://schema.org/Organization"><a href="<?php echo home_url(); ?>" rel="nofollow"><img src="<?php echo get_template_directory_uri(); ?>/library/images/template_logo.png" /></a></div>

					<nav class="header-nav" itemscope itemtype="http://schema.org/SiteNavigationElement">
					<?php // see all default args here: https://developer.wordpress.org/reference/functions/wp_nav_menu/ ?>
						<?php wp_nav_menu(array(
    					         'container' => false,                           // remove nav container
    					         'container_class' => 'menu',                 // class of container (should you choose to use it)
    					         'menu' => __( 'The Main Menu', 'templatetheme' ),  // nav name
    					         'menu_class' => 'nav top-nav main-menu',               // adding custom nav class
    					         'theme_location' => 'main-nav',                 // where it's located in the theme
						)); ?>

					</nav>

					<?php // if you'd like to use the site description un-comment the below <p></p>. If not, leave as-is or delete it. ?>
					<!-- <p class="site-description"><?php bloginfo('description'); ?></p> -->

				</div>

			</header>
