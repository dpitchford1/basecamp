<?php
/**
 * Template Part: Featured Project
 * Usage: get_template_part('template-parts/sector/featured-project', null, ['project' => $wp_post]);
 *
 * Expects:
 * - $args['project'] WP_Post (sector child of a sector with _micd_project_featured = 1)
 * - Optional $args['heading'] string (default "Featured Project")
 */
if (!isset($args['project']) || !($args['project'] instanceof WP_Post)) return;

$project = $args['project'];
$heading = isset($args['heading']) && $args['heading'] !== '' ? (string)$args['heading'] : __('Featured Project', 'micd');

$link     = get_permalink($project->ID);
$title    = get_the_title($project);
$thumb_id = get_post_thumbnail_id($project->ID);
$img_portrait = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'portait-m') : null;
?>
<div class="featured--project is--standalone">
    <article class="project--item">
        <div class="project--caption card--caption is--centered">
            <h3 class=""><?php echo esc_html($heading); ?></h3>
            <h4 class="hero--heading"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a></h4>
            
            <p class="flex--centered"><a class="slick--btn slick--secondary" href="<?php echo esc_url($link); ?>">
                <span class="circle" aria-hidden="true"><span class="icon arrow"></span></span>
                    <span class="button-text"><?php esc_html_e('View Project', 'micd'); ?></span></a></p>
        </div>
        <?php if ($thumb_id): ?>
            <a href="<?php echo esc_url($link); ?>" tabindex="-1" aria-hidden="true">
                <picture>
                    <?php if ($img_portrait): ?>
                        <source srcset="<?php echo esc_url($img_portrait[0]); ?>" media="(max-width: 600px)">
                    <?php endif; ?>
                    <?php echo wp_get_attachment_image($thumb_id, 'miconcept-img-xxl', false, [
                        'loading' => 'lazy',
                        'class'   => 'card--img'
                    ] ); ?>
                </picture>
            </a>
        <?php endif; ?>
    </article>
</div>
