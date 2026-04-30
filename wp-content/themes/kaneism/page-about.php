<?php
/**
 * Template Name: About Page
 * The template for testing custom gallery layouts
 *
 * This template provides examples of accessing individual images
 * from the Bits metabox for complex custom layouts.
 *
 * @package kaneism
 */

get_header();

$gallery_images = get_post_meta(get_the_ID(), '_kaneism_bits_images', true);

// Output a list of links from the About page meta box
$about_links = kaneism_about_links_list(get_the_ID());

// Output the About page client list in clean HTML
$clients = get_post_meta(get_the_ID(), '_about_clients', true);
?>

<main id="main" class="site-main bits-test-page">
    <?php the_title( '<h2 class="sizes-XLG page--heading">', '</h2>' ); ?>

    <section class="promo--section">
        
    <?php
        // Output the first gallery image using wp_get_attachment_image()
        if (!empty($gallery_images) && isset($gallery_images[0])) {
            $first_image_id = $gallery_images[0];
            echo wp_get_attachment_image($first_image_id, 'basecamp-img-m', false, array(
                'class' => 'img--promo'
            ));
            // Optionally output caption
            $image_post = get_post($first_image_id);
            $image_caption = $image_post ? $image_post->post_excerpt : '';
            if (!empty($image_caption)) {
                echo '<div class="bit-caption">' . esc_html($image_caption) . '</div>';
            }
        }
    ?>
        <div class="blurb--quote">
            <h3 class="sizes-LG section--heading">A blurb...</h3>
            <?php the_content(); ?>
        </div>
    </section>
    <!-- <div class="blurb--quote"><?php the_content(); ?></div> -->


    <?php while ( have_posts() ) : the_post(); ?>

        <?php if ($clients && is_array($clients)): ?>
        
    <section class="web-feature">
        <h2 class="sizes-LG section--heading">Nerd Life</h2>
        <div class="region flexed">
            <div class="blurb--quote">
                <p>Over the years I've had the chance to work with a lot of great people and organizations, both with Painting and Digital. I've had a lot of fun painting a wide variety of places and things, and also had the opportunity to build some of this countries biggest, most visible websites - even getting a shout out in Apple global marketing material. Hat tip.</p>

                <p>My pieces are detailed, structured, well thought out - and if you can tell I take <em>pride</em> in my work - which translates well to my real life nerd life of building websites.</p>

                <p>Below is a selection of brands I consider it humbling to have worked with.</p>
            </div>
            <?php if ( has_post_thumbnail() ) { ?>
            <div class="post-thumbnail">
                <?php the_post_thumbnail( 'kaneism-img-m',array( 'loading' => 'lazy') ); ?>
            </div>
        </div>
    <?php } ?>
    </section>
    <section class="grid-general grid--7col">
        <h3 class="hide-text">Client List</h3>
    <?php foreach ($clients as $client): ?>
        <figure class="client--list">
            <?php echo wp_get_attachment_image($client['image'], 'thumbnail', false, array('class' => 'client-thumb')); ?>
            <!-- <figcaption class="text-center"><?php echo esc_html($client['name']); ?></figcaption> -->
        </figure>
    <?php endforeach; ?>
    </section>
        
    <?php endif; // end clients ?>

    <section class="region component--section">
        <h2 class="sizes-LG section--heading">Artistically Speaking...</h2>
        <p>More content on the way.</p>
    </section>

    <section class="web-feature">
        <h2 class="sizes-LG section--heading">Web things</h2>
        <p>A few couple O web things I've built.</p>
        <div class="grid-general grid--4col">
            <?php
            // Get the first 6 images from the gallery
            if (!empty($gallery_images)) {
                for ($i = 7; $i <= 10; $i++) {
                    if (isset($gallery_images[$i])) {
                        echo '<figure class="web-feature--item">';
                        echo wp_get_attachment_image($gallery_images[$i], 'kaneism-portrait-sm', false, array('loading' => 'lazy'));
                        echo '<figcaption class="text-center">';
                        $image_post = get_post($gallery_images[$i]);
                        $image_caption = $image_post ? $image_post->post_excerpt : '';
                        if (!empty($image_caption)) {
                            echo esc_html($image_caption);
                        } else {
                            echo 'Web Feature #' . ($i + 1);
                        }
                        echo '</figcaption>';
                        echo '</figure>';
                    }
                }
            }
            ?>
        </div>
    </section>

    <?php if (!empty($about_links)): ?>

    <section class="region component--section">
        <h3 class="sizes-LG section--heading">Link Dump</h3>
        <p>Oldschool link dump for cool people, and cool things. Not much still around on the web unfortunately.</p>
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
    </section>
    <?php endif; ?>

        <?php
        /**
         * Functions hooked in to kaneism_page_after action
         *
         * @hooked kaneism_display_comments - 10
         */
        // do_action( 'kaneism_page_after' );

    endwhile; // End of the loop.
    ?>

</main><!-- #main -->

<?php
get_footer();