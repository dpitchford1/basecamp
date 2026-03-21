# Metaboxes Reference

All custom metaboxes in the theme, where they appear, what they store, and how the data is used on the frontend.

---

## General Rules for All Metaboxes

1. **Nonce verification** — all save callbacks verify a nonce before processing `$_POST`
2. **Capability check** — `current_user_can('edit_post', $post_id)` checked before saving
3. **Sanitization on save** — text fields use `sanitize_text_field()`, URLs use `esc_url_raw()`
4. **Auto-save guard** — all save callbacks bail on `wp_is_post_autosave()` and `wp_is_post_revision()`
5. **Admin edit guard** — WebP conversion is skipped during post saves to avoid unintended re-processing

---

## Adding a New Metabox

Follow this pattern:

```php
// 1. Register the metabox
add_action( 'add_meta_boxes', 'basecamp_example_register_metabox' );
function basecamp_example_register_metabox(): void {
    add_meta_box(
        'basecamp-example',
        __( 'Example', 'basecamp' ),
        'basecamp_example_render',
        'post',
        'normal',
        'high'
    );
}

// 2. Render callback
function basecamp_example_render( WP_Post $post ): void {
    wp_nonce_field( 'basecamp_example_save', 'basecamp_example_nonce' );
    $value = get_post_meta( $post->ID, '_basecamp_example', true );
    echo '<input type="text" name="basecamp_example" value="' . esc_attr( $value ) . '">';
}

// 3. Save callback
add_action( 'save_post', 'basecamp_example_save' );
function basecamp_example_save( int $post_id ): void {
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;
    if ( ! isset( $_POST['basecamp_example_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['basecamp_example_nonce'], 'basecamp_example_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    update_post_meta( $post_id, '_basecamp_example', sanitize_text_field( $_POST['basecamp_example'] ?? '' ) );
}
```
