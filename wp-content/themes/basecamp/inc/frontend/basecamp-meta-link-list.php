<?php

/**
 * Add a meta box for a customizable link list.
 * Use the 'basecamp_link_list_meta_box_args' filter to control where it appears.
 */
function basecamp_link_list_metabox() {
    $args = apply_filters('basecamp_link_list_meta_box_args', [
        'id'       => 'basecamp_link_list',
        'title'    => 'Link List',
        'post_type'=> 'page',
        'context'  => 'normal',
        'priority' => 'default',
        'templates'=> ['page-about.php'], // Default: only About page template
    ]);
    add_meta_box(
        $args['id'],
        $args['title'],
        function($post) use ($args) { basecamp_link_list_metabox_callback($post, $args); },
        $args['post_type'],
        $args['context'],
        $args['priority']
    );
}

/**
 * Enqueue jQuery and jQuery UI Sortable for the link list meta box.
 */
function basecamp_link_list_admin_scripts($hook) {
    global $post;
    if ( ! $post ) return;
    $args = apply_filters('basecamp_link_list_meta_box_args', [
        'post_type'=> 'page',
        'templates'=> ['page-about.php'],
    ]);
    $post_types = (array) ($args['post_type'] ?? 'page');
    $templates = (array) ($args['templates'] ?? []);
    $show = in_array($post->post_type, $post_types, true)
        && (empty($templates) || in_array(get_page_template_slug($post->ID), $templates, true));
    if ( ($hook === 'post-new.php' || $hook === 'post.php') && $show ) {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');
    }
}
add_action('admin_enqueue_scripts', 'basecamp_link_list_admin_scripts');

/**
 * Render the link list meta box.
 * @param WP_Post $post
 * @param array $args
 */
function basecamp_link_list_metabox_callback($post, $args = []) {
    // Comment out or remove this block for testing:
    // $templates = (array) ($args['templates'] ?? []);
    // if (!empty($templates) && !in_array(get_page_template_slug($post->ID), $templates, true)) {
    //     return;
    // }
    $links = get_post_meta($post->ID, '_basecamp_link_list', true);
    // Always show at least one row (empty if no links)
    if (!is_array($links) || count($links) === 0) {
        $links = [ [ 'label' => '', 'url' => '', 'new_tab' => 0 ] ];
    }
    wp_nonce_field('basecamp_link_list_nonce', 'basecamp_link_list_nonce_field');
    ?>
    <style>
        .link-list-row { display: flex; align-items: center; gap: 8px; }
        .link-list-row .drag-handle { cursor: move; font-size: 18px; color: #888; padding: 0 8px; }
    </style>
    <div id="link-list-rows">
        <?php foreach ($links as $i => $link): ?>
            <div class="link-list-row" style="margin-bottom:10px;">
                <span class="drag-handle" title="Drag to reorder" aria-label="Drag to reorder">&#9776;</span>
                <input type="text" name="basecamp_link_list[<?php echo $i; ?>][label]" placeholder="Label" value="<?php echo esc_attr($link['label'] ?? ''); ?>" style="width:20%;" aria-label="Link label" />
                <input type="url" name="basecamp_link_list[<?php echo $i; ?>][url]" placeholder="URL" value="<?php echo esc_url($link['url'] ?? ''); ?>" style="width:40%;" aria-label="Link URL" />
                <label>
                    <input type="checkbox" name="basecamp_link_list[<?php echo $i; ?>][new_tab]" value="1" <?php checked(!empty($link['new_tab'])); ?> aria-label="Open in new tab" />
                    Open in new tab
                </label>
                <button class="remove-link button" type="button" aria-label="Remove link">Remove</button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="add-link-list-row" aria-label="Add link">Add link</button>
    <script>
    (function($){
        function updateLinkIndexes() {
            $('#link-list-rows .link-list-row').each(function(i, row){
                $(row).find('input, label input').each(function(){
                    var name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/basecamp_link_list\[\d+\]/, 'basecamp_link_list['+i+']');
                        $(this).attr('name', name);
                    }
                });
            });
        }
        $(document).ready(function(){
            $('#link-list-rows').sortable({
                handle: '.drag-handle',
                items: '.link-list-row',
                update: function() { updateLinkIndexes(); }
            });
            $('#add-link-list-row').on('click', function(e){
                e.preventDefault();
                var i = $('#link-list-rows .link-list-row').length;
                var row = `<div class="link-list-row" style="margin-bottom:10px;">
                    <span class="drag-handle" title="Drag to reorder" aria-label="Drag to reorder">&#9776;</span>
                    <input type="text" name="basecamp_link_list[`+i+`][label]" placeholder="Label" style="width:20%;" aria-label="Link label" />
                    <input type="url" name="basecamp_link_list[`+i+`][url]" placeholder="URL" style="width:40%;" aria-label="Link URL" />
                    <label>
                        <input type="checkbox" name="basecamp_link_list[`+i+`][new_tab]" value="1" aria-label="Open in new tab" />
                        Open in new tab
                    </label>
                    <button class="remove-link button" type="button" aria-label="Remove link">Remove</button>
                </div>`;
                $('#link-list-rows').append(row);
            });
            $(document).on('click', '.remove-link', function(e){
                e.preventDefault();
                $(this).closest('.link-list-row').remove();
                updateLinkIndexes();
            });
        });
    })(jQuery);
    </script>
    <?php
}

/**
 * Save the link list meta box data.
 * @param int $post_id
 */
function basecamp_link_list_save($post_id) {
    if (!isset($_POST['basecamp_link_list_nonce_field']) || !wp_verify_nonce($_POST['basecamp_link_list_nonce_field'], 'basecamp_link_list_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['basecamp_link_list']) && is_array($_POST['basecamp_link_list'])) {
        $links = [];
        foreach ($_POST['basecamp_link_list'] as $link) {
            if (empty($link['url'])) continue;
            $links[] = [
                'label' => sanitize_text_field($link['label'] ?? ''),
                'url' => esc_url_raw($link['url']),
                'new_tab' => !empty($link['new_tab']) ? 1 : 0,
            ];
        }
        update_post_meta($post_id, '_basecamp_link_list', $links);
    } else {
        delete_post_meta($post_id, '_basecamp_link_list');
    }
}

/**
 * Helper to get the link list array (no HTML).
 * @param int|null $post_id
 * @return array
 */
function basecamp_get_link_list($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $links = get_post_meta($post_id, '_basecamp_link_list', true);
    if (!is_array($links) || empty($links)) return [];
    return $links;
}

// Only add meta box and save handler in admin
if (is_admin()) {
    add_action('add_meta_boxes', 'basecamp_link_list_metabox');
    add_action('save_post', 'basecamp_link_list_save');
}