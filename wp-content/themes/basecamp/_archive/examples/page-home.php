<?php
/*
 Template Name: Home Page
 * 
*/
?>
<?php get_header(); ?>
<main id="main-content" class="site--main">
    <?php
    // HERO:
    $hero_thumb_id = get_post_thumbnail_id();
    $hero_landscape = $hero_thumb_id ? wp_get_attachment_image_src($hero_thumb_id, 'miconcept-img-xxl') : null;
    $hero_portrait  = $hero_thumb_id ? wp_get_attachment_image_src($hero_thumb_id, 'portait-m') : null;
    ?>
    <section class="global--hero home--hero">
        
        <div class="hero--wrapper">
            <?php if ($hero_thumb_id && $hero_landscape): ?>
                <picture>
                    <?php if ($hero_portrait): ?>
                        <source srcset="<?php echo esc_url($hero_portrait[0]); ?>" media="(max-width: 600px)">
                    <?php endif; ?>
                    <?php
                    // Use full featured image with eager loading (same as sectors template)
                    echo wp_get_attachment_image(
                        $hero_thumb_id,
                        'miconcept-img-xxl',
                        false,
                        ['loading' => 'eager', 'class' => 'hero--img']
                    );
                    ?>
                </picture>
            <?php endif; ?>
            <div class="hero--text-block animate-slide-down">
            <?php /* <!-- <h2 class="hero--heading"><?php echo esc_html(get_the_title()); ?></h2> --> */ ?>
                <?php
                $content = get_the_content('');
                if ($content) {
                    // Safe trimmed content (optional)
                    echo '<div class="hero--content">' . wp_kses_post(wpautop($content)) . '</div>';
                }
                ?>
            </div>
        </div>
    </section>
    
    <?php /* Explore 3 features */ ?>
    <section class="content--region fluid">
        <h2 class="section--heading">Explore</h2>

        <div class="grid-general grid--3col section--promos">
            <?php
            $features = function_exists('micd_get_home_features') ? micd_get_home_features(get_the_ID()) : [];
            if (!empty($features)) :
                $i = 0;
                foreach ($features as $f) :
                    $title   = isset($f['title']) ? $f['title'] : '';
                    $excerpt = isset($f['excerpt']) ? $f['excerpt'] : '';
                    $url     = isset($f['url']) ? $f['url'] : '';
                    $img_id  = isset($f['image_id']) ? (int)$f['image_id'] : 0;

                    $img_html = '';
                    if ($img_id) {
                        $img_html = wp_get_attachment_image(
                            $img_id,
                            'miconcept-img-s',
                            false,
                            [
                                'class'         => 'promo--img',
                                'loading'       => $i === 0 ? 'eager' : 'lazy',
                                'decoding'      => 'async',
                                'fetchpriority' => $i === 0 ? 'high' : false,
                            ]
                        );
                    }
                    ?>
                    <article class="card--wrapper promo--item <?php echo $img_html ? 'has-thumb' : 'no-thumb'; ?>">
                        <?php if ($img_html): ?>
                            <?php if ($url): ?><a class="feature-card--img" href="<?php echo esc_url($url); ?>" tabindex="-1" aria-hidden="true"><?php endif; ?>
                                <?php echo $img_html; ?>
                            <?php if ($url): ?></a><?php endif; ?>
                        <?php endif; ?>

                        <?php if ($title): ?>
                            <h3 class="feature-card--title">
                                <?php if ($url): ?><a href="<?php echo esc_url($url); ?>"><?php endif; ?><?php echo esc_html($title); ?><?php if ($url): ?></a><?php endif; ?>
                            </h3>
                        <?php endif; ?>

                        <?php if ($excerpt): ?>
                            <p class="feature-card--excerpt"><?php echo esc_html($excerpt); ?></p>
                        <?php endif; ?>
                    </article>
                    <?php
                    $i++;
                endforeach;
            else:
                // Optional: placeholder when no features are configured
                echo '<p>' . esc_html__('Configure Home features in the page editor.', 'micd') . '</p>';
            endif;
            ?>
        </div>
    </section>


<?php // Featured Project (homepage selection with new meta)
$project = (function_exists('micd_get_homepage_featured_project')) ? micd_get_homepage_featured_project() : null;
if ($project) {
    get_template_part('template-parts/sector/featured-project', null, [
        'project' => $project,
        'heading' => __('Featured Project', 'micd'),
    ]);
}
?>
    <?php /* <!-- <section class="content--region fluid"> --> */ ?>
        <?php
        // echo micd_render_featured_projects_list([
        //     'number'     => 3,
        //     'heading'    => __('Featured Projects','micd'),
        //     'fallback'   => true,
        //     'image_size' => 'miconcept-img-s', // Explicit size request
        //     'excerpt'    => true,
        // ]);
        ?>
    <?php /* <!-- </section> --> */ ?>

    <?php /* Latest News Features */ ?>
    <section class="content--region fluid">
        <?php
        echo micd_render_latest_news_list([
            'number'        => 3,
            'heading'       => __('Latest News','micd'),
            'image_size'    => 'miconcept-img-s', // Match test size
            'excerpt'       => true,
            'excerpt_words' => 18,
        ]);
        ?>
    </section>
</main>
<?php get_footer(); ?>
