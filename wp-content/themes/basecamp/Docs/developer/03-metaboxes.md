# Metaboxes Reference

All custom metaboxes in the theme, where they appear, what they store, and how the data is used on the frontend.

---

## Existing Metaboxes

### Link List

**Class:** `Basecamp\ThemeFunctions\MetaLinkList`  
**File:** `inc/theme-functions/basecamp-meta-link-list.php`  
**Appears on:** Pages (default — filterable via `basecamp_link_list_meta_box_args`)  
**Meta key:** `_basecamp_link_list`

A drag-to-reorder repeater for a list of labelled links. Each row stores three fields:

| Field | Key | Sanitization |
|---|---|---|
| Label text | `label` | `sanitize_text_field()` |
| URL | `url` | `esc_url_raw()` |
| Open in new tab | `new_tab` | `'1'` or `''` |

**Reading in templates:**

```php
// Global wrapper (declared in functions.php for template use)
$links = basecamp_get_link_list( get_the_ID() );

// Direct static call
$links = \Basecamp\ThemeFunctions\MetaLinkList::get( get_the_ID() );

// $links is an array of arrays: [ 'label' => '', 'url' => '', 'new_tab' => '1' ]
foreach ( $links as $link ) {
    $target = $link['new_tab'] ? ' target="_blank" rel="noopener"' : '';
    echo '<a href="' . esc_url( $link['url'] ) . '"' . $target . '>' . esc_html( $link['label'] ) . '</a>';
}
```

**Changing where it appears:**

```php
add_filter( 'basecamp_link_list_meta_box_args', function( array $args ): array {
    $args['post_type'] = [ 'page', 'portfolio' ]; // array or single string
    return $args;
} );
```

---

### Video Carousel

**Class:** `Basecamp\Frontend\VideoCarouselMetabox`  
**File:** `inc/frontend/class-basecamp-video-carousel-metabox.php`  
**Appears on:** Pages  
**Meta key:** `_basecamp_video_carousel_slides`

A repeater for video carousel slides. Each slide stores six fields:

| Field | Key | Sanitization | Notes |
|---|---|---|---|
| Desktop video URL | `desktop_video` | `esc_url_raw()` | Direct video file URL or embed |
| Mobile video URL | `mobile_video` | `esc_url_raw()` | Served on narrow viewports |
| Poster image URL | `poster` | `esc_url_raw()` | Shown before video plays (desktop) |
| Mobile poster URL | `mobile_poster` | `esc_url_raw()` | Poster for mobile |
| Overlay text | `overlay` | `sanitize_text_field()` | Text overlaid on the slide |
| Audio URL | `audio` | `esc_url_raw()` | Optional separate audio track |

**Reading in templates:**

```php
$slides = get_post_meta( get_the_ID(), '_basecamp_video_carousel_slides', true );

if ( ! empty( $slides ) && is_array( $slides ) ) {
    foreach ( $slides as $slide ) {
        $video   = esc_url( $slide['desktop_video'] ?? '' );
        $poster  = esc_url( $slide['poster'] ?? '' );
        $overlay = esc_html( $slide['overlay'] ?? '' );
        // render slide markup...
    }
}
```

The `template-parts/video-carousel.php` partial handles rendering — prefer using that over building slide markup inline.

---

### Page → Theme Assignment

**Class:** `Basecamp\Admin\PageTheme`  
**File:** `inc/admin/class-basecamp-page-theme.php`  
**Appears on:** Pages (sidebar, "Assigned Theme" box)  
**Meta key:** `_basecamp_page_theme`

In multi-project installations where several child themes share one WordPress install, this meta box lets editors tag each page with the theme it belongs to, providing a "Theme" column in the Pages list table at a glance.

| UI Element | Behaviour |
|---|---|
| Meta box | Dropdown of all installed themes (display name shown, slug stored) |
| Pages list column | Inserted after Title; shows theme display name |
| Column sorting | Clicking the "Theme" column header sorts pages by assigned theme |

**Reading in code:**

```php
$theme_slug = get_post_meta( get_the_ID(), '_basecamp_page_theme', true );
// e.g. 'kaneism', 'basecamp', or '' if unset
```

Select **— None —** to clear the assignment (meta key is deleted, not stored empty).

---

## General Rules for All Metaboxes


1. **Nonce verification** — all save callbacks verify a nonce before processing `$_POST`
2. **Capability check** — `current_user_can('edit_post', $post_id)` checked before saving
3. **Sanitization on save** — text fields use `sanitize_text_field()`, URLs use `esc_url_raw()`
4. **Auto-save guard** — all save callbacks bail early on autosave and revision requests
5. **Admin edit guard** — WebP conversion is skipped during post saves to avoid unintended re-processing

---

## Adding a New Metabox

```php
<?php
declare(strict_types=1);

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
    if ( ! wp_verify_nonce( sanitize_key( $_POST['basecamp_example_nonce'] ), 'basecamp_example_save' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    update_post_meta( $post_id, '_basecamp_example', sanitize_text_field( wp_unslash( $_POST['basecamp_example'] ?? '' ) ) );
}
```
