	<footer class="footer" id="global-footer">

		<div id="inner-footer" class="wrap">

			<?php /* ─── Footer navigation ────────────────────────────────────
			   Uncomment to output the footer nav menu (registered as 'footer' location).
			   wp_nav_menu( [
				   'theme_location' => 'footer',
				   'container'      => 'nav',
				   'container_class'=> 'footer-nav',
				   'menu_class'     => 'nav footer-menu',
				   'depth'          => 1,
				   'fallback_cb'    => false,
			   ] );
			*/ ?>

			<p class="source-org copyright">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.</p>

		</div><!-- #inner-footer -->

	</footer><!-- .footer -->

</div><!-- #container -->

<?php wp_footer(); ?>

</body>
</html>
