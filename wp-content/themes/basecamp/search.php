<?php get_header(); ?>

<main id="main" class="m-all t-2of3 d-5of7">

    <h1 class="archive-title"><span><?php _e( 'Search Results for:', 'templatetheme' ); ?></span> <?php echo esc_attr(get_search_query()); ?></h1>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(''); ?>>

            <?php get_template_part( 'templates/header', 'title'); ?>

            <?php get_template_part( 'templates/byline'); ?>

            <section class="entry-content">
                
                <?php get_template_part( 'templates/content', 'excerpt'); ?>

            </section>

            <?php get_template_part( 'templates/category-tags'); ?>

        </article>

        <?php get_template_part( 'templates/post-navigation'); ?>

    <?php endwhile; endif; ?>

</main>

<?php // get_sidebar(); ?>

<?php get_footer(); ?>
