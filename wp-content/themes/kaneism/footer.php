</div><?php /* Inner content page wrapper */ ?>
<?php /* Footer Start */ ?>
<div class="region global-footer cf" id="global-footer">
    <footer class="fluid cf">    
        <h2 class="hide-text">Additional Information</h2>
        <div class="footer-grid ra">
            <div class="footer-span-1">
                <div class="footer-logo"></div>
            </div>
            <div class="footer-area footer-span-2" itemscope itemtype="http://www.schema.org/SiteNavigationElement">
                <h3 class="xsm--m footer--heading sizes-LG">About</h3>
                <p>Artist, Designer, Web Developer based in Toronto. Painting murals since the 80's and building the web since 1999.</p>
                <p>For any inquiries please shoot over to the <a href="/contact/">contact page</a> and drop me a note.</p>
                
            </div>
            <div class="footer-area footer-span-3" itemscope itemtype="http://www.schema.org/SiteNavigationElement">
                <h3 class="xsm--m footer--heading sizes-LG">Browse <span class="hide-text">sections</span></h3>
                <?php /* Main Menu - different css */ ?>
                <?php
                    wp_nav_menu( 
                        array(
                            'theme_location'  => 'primary',
                            'menu_class' => 'no-bullet footer-lists',
                            'menu_id' => 'footer-menu',
                            'container' => 'ul'
                        )
                    );
                ?>
            </div>
            <div class="footer-area footer-span-4">
                <h3 class="xsm--m footer--heading sizes-LG">Work</h3>
                <?php
                    wp_nav_menu( 
                        array(
                            'theme_location'  => 'footer',
                            'menu_class' => 'no-bullet footer-lists',
                            'menu_id' => 'footer-menu-side',
                            'container' => 'ul'
                        )
                    );
                ?>
                <a class="js-BackToTop" href="#page-body" onclick="return false">Top of page</a>
            </div>
            <p class="source-org copyright footer-area footer-span-5">&copy; <?php echo date('Y'); ?> Kaneism Design</p>
        </div>

    </footer>
</div>
<script src="/assets/kaneism/js/core/base.min.js" async></script>
<?php /* Swiper init scripts */ ?>
<?php if ( is_singular('work') ) : ?>
    <script>!function(){"use strict";function initWorkGallery(){if("undefined"==typeof Swiper)return console.log("Swiper not loaded yet, retrying..."),void setTimeout(initWorkGallery,500);if(document.querySelectorAll(".swiper").length)try{new Swiper(".swiper",{loop:!0,keyboard:{enabled:!0,onlyInViewport:!1},navigation:{nextEl:".swiper-button-next",prevEl:".swiper-button-prev"},autoHeight:!1,speed:500,effect:"slide"});console.log("Work gallery initialized successfully")}catch(error){console.error("Error initializing work gallery:",error)}}"loading"===document.readyState?document.addEventListener("DOMContentLoaded",initWorkGallery):initWorkGallery(),window.addEventListener("load",initWorkGallery)}();</script>
<?php endif; ?>
<?php if ( is_tax('work_category', array('canvases', 'designs', 'videos')) ) : ?>
    <script>document.addEventListener("DOMContentLoaded",(function(){document.querySelectorAll(".swiper").forEach((function(el){new Swiper(el,{loop:!0,keyboard:{enabled:!0},freeMode:!0,scrollbar:{el:".swiper-scrollbar",hide:!0},spaceBetween:10,navigation:{nextEl:el.querySelector(".swiper-button-next"),prevEl:el.querySelector(".swiper-button-prev")}})}))}));</script>
<?php endif; ?>
<?php if ( is_page('about') ) : ?>
    <script src="/assets/kaneism/js/resources/swiper.min.js" defer></script>
<?php endif; ?>

<?php /* Google tag (gtag.js) */ ?>
<?php if( !in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) ) ) { ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RW03VLJX2Y"></script>
<script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-RW03VLJX2Y');</script>
<?php } ?>

<?php /* contact page scripts - CF7 is being weird, loading oldschool */ ?>
<?php if ( is_page('Contact') ) : ?>
    <!-- <script async src="/wp-content/plugins/contact-form-7/includes/swv/js/index.js?ver=6.0.7"></script> -->
<?php endif ?>

<?php /* Woo scripts 
<?php if (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_product())) : ?>
    <script src="/wp-content/plugins/woocommerce/assets/kaneism/js/frontend/add-to-cart.min.js"></script>
    <script src="/wp-content/plugins/woocommerce/assets/kaneism/js/frontend/woocommerce.min.js" id="woocommerce-js" defer data-wp-strategy="defer"></script>
    <script src="/wp-content/plugins/woocommerce/assets/kaneism/js/frontend/cart.min.js"></script>
    <?php if (is_cart()) : ?>
        <script src="/wp-content/plugins/woocommerce/assets/kaneism/js/frontend/cart.min.js"></script>
    <?php endif; ?>
    <?php if (is_checkout()) : ?>
        <script src="/wp-content/plugins/woocommerce/assets/kaneism/js/frontend/checkout.min.js"></script>
        <script src="/wp-content/plugins/woocommerce/assets/kaneism/js/frontend/country-select.min.js"></script>
        <script src="/wp-content/plugins/woocommerce/assets/kaneism/js/frontend/address-i18n.min.js"></script>
    <?php endif; ?>
<?php endif; ?>
*/ ?>
<?php wp_footer(); ?>
</body>
</html>