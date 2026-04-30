
<div class="region global-footer cf" id="global-footer">
    <footer class="fluid">    
        <h2 class="hide-text">Additional Information</h2>
        <div class="footer-grid ra">
            <div class="footer-span-1">
                <div class="footer-logo"></div>
            </div>
            <div class="footer-area footer-span-2" itemscope itemtype="http://www.schema.org/SiteNavigationElement">
                <h3 class="subtitle">Browse</h3>
                <?php /* Main Menu - different css */ ?>
                <?php
                    wp_nav_menu( 
                        array(
                            'theme_location'  => 'footer',
                            'menu_class' => 'footer-menu',
                            'menu_id' => 'footer-menu',
                            'container' => 'ul'
                        )
                    );
                ?>
            </div>
            <div class="footer-area footer-span-3">
                <h3 class="subtitle">In Person</h3>
                <div itemscope itemtype="https://schema.org/LocalBusiness">
                    
                </div>
            </div>
            <div class="footer-area footer-span-4">
                <h3 class="subtitle">Online</h3>
                
                
            </div>
            <div class="footer-area footer-span-5">
                <?php // echo do_shortcode('[mc4wp_form id=164]'); ?>
                <?php // echo do_shortcode('[mc4wp_form id=583]'); ?>
                </div>
			<p class="source-org copyright footer-area footer-span-6">&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?>. All Rights Reserved<?php
				/**
				 * basecamp_footer_legal_links
				 * Return an array of [ 'label' => '', 'url' => '' ] to add legal links.
				 * Add in child theme functions.php — no footer.php override needed.
				 *
				 * Example:
				 *   add_filter( 'basecamp_footer_legal_links', function( array $links ): array {
				 *       $links[] = [ 'label' => 'Privacy Policy', 'url' => '/privacy-policy/' ];
				 *       $links[] = [ 'label' => 'Terms & Conditions', 'url' => '/terms-and-conditions/' ];
				 *       return $links;
				 *   } );
				 */
				$legal_links = apply_filters( 'basecamp_footer_legal_links', [] );
				foreach ( $legal_links as $link ) {
					echo ' &nbsp;&mdash;&nbsp; <a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['label'] ) . '</a>';
				}
			?></p>
        </div>

    </footer>
</div>
<!-- <script src="/assets/js/core/base.min.js" async></script> -->

<?php wp_footer(); ?>

</body>
</html> <!-- View source huh? Oldschool. Nice. -->