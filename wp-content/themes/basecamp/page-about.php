<?php
/**
 * Template Name: About Page
 *
 *
 * @package basecamp
 */

get_header(); ?>

<main id="main" class="site-main bits-test-page">
    <?php the_title( '<h2 class="sizes-XLG page--heading">', '</h2>' ); ?>
    <div class="blurb--quote"><?php the_content(); ?></div>
    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
            <div class="entry-content">
                
                <?php the_excerpt(); ?>

                <?php
                // Output a list of links from the About page meta box
                $about_links = basecamp_get_link_list(get_the_ID());

                if (!empty($about_links)): ?>

                <div class="region component--section">
                    <h3 class="sizes-LG section--heading">Link List</h3>
                <?php
                    echo '<ul class="about-link-list">';
                    foreach ($about_links as $link) {
                        $target = !empty($link['new_tab']) ? ' target="_blank" rel="noopener"' : '';
                        $label = esc_html($link['label'] ?: $link['url']);
                        $url = esc_url($link['url']);
                        echo "<li><a href=\"$url\"$target>$label</a></li>";
                    }
                    echo '</ul>';
                ?>
                </div>
                <?php endif; ?>

            </div><!-- .entry-content -->
        </article><!-- #post-## -->
        
        <?php
    endwhile; // End of the loop.
    ?>

</main><!-- #main -->

<?php
get_footer();