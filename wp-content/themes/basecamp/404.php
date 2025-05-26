<?php get_header(); ?>

<main id="main" class="m-all t-all d-all" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

    <section class="entry-content">

        <?php get_template_part( 'templates/header', 'title'); ?>

        <article id="post-not-found" class="hentry">

            <div class="hal">

                <img src="<?php echo get_template_directory_uri(); ?>/library/images/hal.png" alt="HAL 9000">

                <div class="circle"></div>

            </div>

            <div class="404-txt">

                <h3><?php _e( 'I\'m sorry Dave, I\'m afraid I can\'t do that.', 'templatetheme' ); ?></h3>
                <p>We couldn't find what you are looking for, please try searching.</p>

            </div>

        </article>

        <section class="search">

                <p><?php get_search_form(); ?></p>

        </section>

    </section>

</main>

<?php get_footer(); ?>
