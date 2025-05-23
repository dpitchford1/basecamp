<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap">

					<main id="main" class="m-all t-all d-all" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

						<article id="post-not-found" class="hentry">

							<header class="article-header">

								<?php get_template_part( 'templates/header', 'title'); ?>

							</header>

							<section class="entry-content">

								<div class="hal">

									<img src="<?php echo get_template_directory_uri(); ?>/library/images/hal.png" alt="HAL 9000">

									<div class="circle"></div>

								</div>

								<div class="404-txt">

									<h3><?php _e( 'I\'m sorry Dave, I\'m afraid I can\'t do that.', 'templatetheme' ); ?></h3>
									<p>We couldn't find what you are looking for, please try searching.</p>

								</div>

							</section>

							<section class="search">

									<p><?php get_search_form(); ?></p>

							</section>

							<footer class="article-footer">

							</footer>

						</article>

					</main>

				</div>

			</div>

<?php get_footer(); ?>
