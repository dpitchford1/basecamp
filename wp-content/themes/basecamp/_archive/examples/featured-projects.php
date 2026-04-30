<?php
/**
 * Featured Projects Block Template
 * Variables provided:
 *  - $heading_text (string|null)
 *  - $projects (array of view model arrays from micd_build_project_view_model)
 *
 * View model structure per project:
 *  id, title, permalink, parent_title, date, location,
 *  thumb => [has, src, w, h, alt, attr], is_first
 *
 * No queries here. Pure presentation.
 */
if (!isset($projects) || !is_array($projects)) {
    return;
}
?>
<?php if (!empty($heading_text)): ?>
    <h2 class="section--heading"><?php echo esc_html($heading_text); ?></h2>
<?php endif; ?>
<?php if (empty($projects)): ?>
    <p class="featured-projects-empty"><?php esc_html_e('No projects available.', 'micd'); ?></p>
<?php else: ?>

<div class="grid-general grid--3col features--list">
    <?php
    $requested_size = isset($image_size) ? $image_size : 'miconcept-img-sm';
    foreach ($projects as $vm):
        $thumb_html = '';
        if (has_post_thumbnail($vm['id'])) {
            $thumb_html = get_the_post_thumbnail(
                $vm['id'],
                $requested_size,
                [
                    'class'         => 'fp-thumb-img',
                    'loading'       => $vm['is_first'] ? 'eager' : 'lazy',
                    'decoding'      => 'async',
                    'fetchpriority' => $vm['is_first'] ? 'high' : false,
                ]
            );
        } elseif (!empty($vm['thumb']['has'])) {
            $t = $vm['thumb'];
            $thumb_html = '<img class="fp-thumb-img" src="' . esc_url($t['src']) . '" width="' . (int)$t['w'] . '" height="' . (int)$t['h'] . '" alt="' . esc_attr($t['alt']) . '" decoding="async"' . $t['attr'] . '>';
        }
    ?>

    <article class="card--wrapper <?php echo $thumb_html ? 'has-thumb' : 'no-thumb'; ?>" data-size-requested="<?php echo esc_attr($requested_size); ?>">
        <a class="feature-card--img" href="<?php echo esc_url($vm['permalink']); ?>" tabindex="-1" aria-hidden="true"><?php echo $thumb_html; ?></a>
        <h3 class="feature-card--title"><a href="<?php echo esc_url($vm['permalink']); ?>"><?php echo esc_html($vm['title']); ?></a></h3>
        <?php if (!empty($vm['excerpt'])): ?>
            <?php /* <p class="feature-card--excerpt"><?php echo esc_html($vm['excerpt']); ?></p> */ ?>
        <?php endif; ?>
        <?php if ($vm['parent_title']): ?>
            <p class="feature-card--meta has--label">Sector: <?php echo esc_html($vm['parent_title']); ?></p>
        <?php endif; ?>
        <?php if ($vm['date'] || $vm['location']): ?>
            <?php /*
            <div class="feature-card--meta">
                <?php if ($vm['date']): ?><p class="fp-date">Date: <?php echo esc_html($vm['date']); ?></p><?php endif; ?>
                <?php if ($vm['location']): ?><p class="fp-loc">Location: <?php echo esc_html($vm['location']); ?></p><?php endif; ?>
            </div> */ ?>
        <?php endif; ?>
    </article>

    <?php endforeach; ?>
</div>

<?php endif; ?>
