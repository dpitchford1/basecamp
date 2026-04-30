<?php
/*
 Template Name: Our Story
    Description: Custom template for the Our Story page.
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
                <h2 class="hero--heading"><?php echo esc_html(get_the_title()); ?></h2>
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
    
    <?php /* 3 features */ ?>
    <?php
    $os_features = get_post_meta($post->ID, MICD_OS_FEATURES_META, true);
    if (is_array($os_features) && !empty($os_features)) :
    ?>
    <section class="grid-general grid--3col section1--promos fluid">
        <h2 class="hide-text">A little about Us</h2>
        <?php foreach ($os_features as $feature):
            $f_heading = isset($feature['heading']) ? $feature['heading'] : '';
            $f_copy    = isset($feature['copy'])    ? $feature['copy']    : '';
            if (!$f_heading && !$f_copy) continue;
            ?>
            <article class="is--promo">
                <?php if ($f_heading): ?>
                    <h3><?php echo esc_html($f_heading); ?></h3>
                <?php endif; ?>
                <?php if ($f_copy): ?>
                    <p><?php echo wp_kses_post(nl2br($f_copy)); ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</main>
<?php
    // Our Executive Team (Leadership)
    $team = get_post_meta(get_the_ID(), MICD_LEADERSHIP_META, true);
    if (is_array($team) && !empty($team)) : ?>

<section class="our-story--leaders fluid">
    <h2 class="section--heading"><?php esc_html_e('Our Leadership Team', 'micd'); ?></h2>
    <div class="grid-general grid--3col team--grid">
        <?php foreach ($team as $member):
            $name   = isset($member['name']) ? esc_html($member['name']) : '';
            $title  = isset($member['title']) ? esc_html($member['title']) : '';
            $img_id = isset($member['image_id']) ? (int)$member['image_id'] : 0;
            ?>
            <article class="team--member">
                <?php if ($img_id): ?>
                        <?php echo wp_get_attachment_image($img_id, 'miconcept-img-s', false, ['class'=>'team--img','loading'=>'lazy']); ?>
                <?php endif; ?>
                <?php if ($name): ?>
                    <h3 class="team--name"><?php echo $name; ?></h3>
                <?php endif; ?>
                <?php if ($title): ?>
                    <p class="team--title"><?php echo $title; ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

    <?php endif; ?>

<?php /* Pullquote */ ?>
<section class="fluid section--blockquote">
    <h2 class="hide-text">Quotable</h2>
    <blockquote class="blockquote--feature">
        <?php echo get_post_meta(get_the_ID(), 'Pullquote', true); ?>
    </blockquote>
</section>

<?php
// Featured Project (global) with fallback
$projects = function_exists('micd_get_featured_projects') ? micd_get_featured_projects(['number' => 1]) : [];
if (empty($projects) && function_exists('micd_get_sector_children')) {
    // Fallback to latest child sector project if no featured set
    $projects = micd_get_sector_children(null, ['number' => 1, 'featured' => null]);
}
if (!empty($projects)) {
    get_template_part('template-parts/sector/featured-project', null, [
        'project' => $projects[0],
        'heading' => __('Featured Project', 'micd'),
    ]);
}
?>

<?php /* Latest News */ ?>
<section class="content--region fluid">
    <?php
    echo micd_render_latest_news_list([
        'number'        => 3,
        'heading'       => __('Latest News','micd'),
        'image_size'    => 'miconcept-img-s',
        'excerpt'       => true,
        'excerpt_words' => 18,
    ]);
    ?>
</section>

<?php get_footer(); ?>
