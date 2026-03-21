# Built-in SEO

The theme ships a self-contained SEO layer that handles page titles, meta descriptions, and social sharing metadata (Open Graph + Twitter Card). All three modules automatically defer to Yoast SEO or Rank Math if either plugin is active.

**Files:**

```
inc/seo/
├── class-basecamp-seo.php                    Bootstrapper — loads the four modules
├── basecamp-title-functions.php              Page title system
├── basecamp-meta-description-functions.php   Meta description tags
├── basecamp-social-meta-functions.php        Open Graph + Twitter Card tags
└── class-basecamp-schema.php                 JSON-LD structured data
```

---

## Page Titles

**File:** `inc/seo/basecamp-title-functions.php`

The title system is extensible via a manager + extension class pattern. Two filters are hooked: `pre_get_document_title` (modern WP) and `wp_title` (legacy fallback), both at priority `1`.

### How It Works

`Basecamp\SEO\TitleManager::filter_title()` iterates through a list of extension classes. Each extension implements a static `maybe_title( $title )` method that returns a formatted title string or `null` if the current page is not its concern. The first non-null return wins. If no extension claims the request, `Basecamp\SEO\TitleCore::maybe_title()` provides the fallback.

```
Request comes in
    ↓
[registered extensions run in order — none active by default]
    ↓ (all return null)
Basecamp\SEO\TitleCore::maybe_title()     → singular fallback, then site name
```

### Built-in Extensions

| Class | Handles |
|---|---|
| `Basecamp\SEO\TitleCore` | Catch-all. Singular → `post_title - SiteName`, anything else → `SiteName` |

### Adding a New Title Extension

1. Create a class with a static `maybe_title` method:

```php
class TitleNews extends \Basecamp\SEO\TitleCore {
    public static function maybe_title( $title ) {
        if ( is_singular( 'post' ) ) {
            $site = get_bloginfo( 'name' );
            return get_the_title() . ' - News - ' . $site;
        }
        if ( is_home() ) {
            return 'News - ' . get_bloginfo( 'name' );
        }
        return null; // Not our concern — pass to next extension
    }
}
```

2. Register it in `Basecamp\SEO\TitleManager::$extensions`:

```php
protected static $extensions = [
    'Basecamp\\SEO\\TitleNews',   // ← add your FQCN extension class names here
];
```

You can define the class in the same file or in its own include — just ensure it's loaded before `Basecamp\SEO\TitleManager::init()` runs.

---

## Meta Descriptions

**File:** `inc/seo/basecamp-meta-description-functions.php`  
**Class:** `Basecamp\SEO\MetaDescription`  
**Hook:** `wp_head` at priority `1`

Outputs three tags simultaneously:

```html
<meta name="description" content="...">
<meta property="og:description" content="...">
<meta name="twitter:description" content="...">
```

### Description Source Logic

The system picks the best available description for each context:

| Page context | Source |
|---|---|
| Singular post/page | Post excerpt (if set), otherwise first 30 words of content |
| Taxonomy / category / tag archive | Term description, or `"Browse all {Term} content"` |
| CPT archive (other) | The CPT's `description` field from `register_post_type()` |
| Author archive | `"Posts by {Display Name}"` |
| Search results | `"Search results for '{query}'"` |
| Date archive | `"Archive for {date string}"` |
| Fallback | `get_bloginfo('description')` (site tagline) |

All descriptions are trimmed to 30 words using `wp_trim_words()`.

### Programmatic Access

You can retrieve a description without triggering head output:

```php
// For a post/page
$desc = Basecamp\SEO\MetaDescription::get_meta_description( $post_id );

// For a WP_Post object
$desc = Basecamp\SEO\MetaDescription::get_meta_description( $post, 25 ); // 25-word limit

// For a WP_Term
$desc = Basecamp\SEO\MetaDescription::get_meta_description( $term_object );
```

Returns a plain string (no HTML, no tags). The optional second argument overrides the default 30-word limit.

---

## Social Meta (Open Graph + Twitter Card)

**File:** `inc/seo/basecamp-social-meta-functions.php`  
**Class:** `Basecamp\SEO\SocialMeta`  
**Hooks:** `wp_head` at priority `2` (OG/Twitter tags) and `3` (image dimensions — currently no-op, kept for compatibility)

Outputs Open Graph and Twitter Card tags. On singular posts the image dimensions (`og:image:width` / `og:image:height`) are also output when the share image is hosted locally (determined by comparing the URL against `site_url()`).

### Tags Output

```html
<!-- Open Graph -->
<meta property="og:title" content="...">
<meta property="og:url" content="...">
<meta property="og:type" content="article"> <!-- or "website" -->
<meta property="og:site_name" content="...">
<meta property="og:image" content="...">
<meta property="og:image:width" content="...">   <!-- only for local images -->
<meta property="og:image:height" content="...">  <!-- only for local images -->
<meta property="og:description" content="...">   <!-- output by meta description module -->

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="...">
<meta name="twitter:url" content="...">
<meta name="twitter:image" content="...">
<meta name="twitter:site" content="@handle">     <!-- if set in Customizer -->
<meta name="twitter:creator" content="@handle">  <!-- if author has twitter meta -->
<meta name="twitter:description" content="...">  <!-- output by meta description module -->
```

### Image Source Logic

| Context | Image source |
|---|---|
| Singular post with featured image | Featured image at `full` size |
| Home / front page | Customizer: **Home Share Image**, fallback to Default Share Image |
| Taxonomy (with thumbnail meta `thumbnail_id`) | Term thumbnail at `full` size |
| Author archive | Gravatar at 512px |
| Everything else | Customizer: **Default Share Image** |

### Customizer Settings

| Setting key | Location in Customizer | Default |
|---|---|---|
| `basecamp_default_share_image` | — | `/assets/img/logos/login_logo.png` |
| `basecamp_home_share_image` | — | Falls back to Default Share Image |
| `basecamp_twitter_site` | — | Empty (no `twitter:site` tag output) |

Set these under **Appearance → Customize** or programmatically via `set_theme_mod()`.

### Author Twitter Handle

If an author has their Twitter handle stored in their user profile (user meta key `twitter`), the `twitter:creator` tag is output automatically on singular posts:

```php
// Store via WP user meta
update_user_meta( $user_id, 'twitter', 'handleWithoutAt' );
```

### Getting Social Meta Programmatically

```php
// Returns an array: title, url, image, type, description, site_name
$meta = Basecamp\SEO\SocialMeta::get_social_meta( $post_id );

echo $meta['image'];       // Full image URL
echo $meta['description']; // 30-word excerpt or site tagline
```

## JSON-LD Structured Data

**File:** `inc/seo/class-basecamp-schema.php`  
**Class:** `Basecamp\SEO\Schema`  
**Hook:** `wp_head` at priority `6` (after meta description at 1, social at 2)

Outputs `<script type="application/ld+json">` blocks. Defers to Yoast SEO or Rank Math if either is active (same check as the other SEO modules).

### Schema Output by Context

| Page context | Schema types output |
|---|---|
| Every page | `Organization` |
| Post (`is_singular('post')`) | `Organization` + `Article` + `BreadcrumbList` |
| Taxonomy / CPT archive | `Organization` + `BreadcrumbList` |
| Home / other | `Organization` only |

### Organization

The `Organization` node uses `@id: {home_url}/#organization` as a stable anchor. All other graph nodes (`Article.publisher`, `Service.provider`) reference it by `@id` rather than duplicating the data.

Business details pulled from:
- **Name / URL** — `get_bloginfo('name')`, `home_url('/')`
- **Logo** — WordPress Customizer `custom_logo` setting (set via Appearance → Customize → Site Identity)
- **Contact, address, hours, social** — all optional; configured via filters (default to empty — see below)

### Filters

Every data point is overridable without touching the class:

```php
// Individual fields
add_filter( 'basecamp_schema_email',     fn() => 'hello@example.com' );
add_filter( 'basecamp_schema_telephone', fn() => '+10000000000' );
add_filter( 'basecamp_schema_hours',     fn() => 'Mo-Fr 08:00-18:00' );

// Address block (full array)
add_filter( 'basecamp_schema_address', function( $address ) {
    $address['streetAddress'] = 'New Street Address';
    return $address;
} );

// Social profiles (sameAs array)
add_filter( 'basecamp_schema_same_as', function( $urls ) {
    $urls[] = 'https://www.facebook.com/basecampdesign/';
    return $urls;
} );

// Whole Organization graph
add_filter( 'basecamp_schema_organization', function( $org ) {
    $org['foundingDate'] = '2010';
    return $org;
} );

// Article graph (receives $post as second arg)
add_filter( 'basecamp_schema_article', function( $article, $post ) {
    $article['wordCount'] = str_word_count( strip_tags( $post->post_content ) );
    return $article;
}, 10, 2 );

// BreadcrumbList graph
add_filter( 'basecamp_schema_breadcrumb', fn( $crumb ) => $crumb );
```

### BreadcrumbList Logic

- **Post** → Home → Category → Post title
- **Taxonomy / tag / category** → Home → Term name
- **CPT archive** → Home → Post type label
- Returns `null` (no output) if only the home node would be present

---

## Plugin Compatibility

Both `Basecamp\SEO\MetaDescription::init()` and `Basecamp\SEO\SocialMeta::init()` check for active SEO plugins before registering their `wp_head` hooks:

```php
if ( class_exists( 'WPSEO_Frontend' ) || class_exists( 'RankMath' ) ) {
    return; // Yoast or Rank Math is active — do nothing
}
```

If either plugin is detected the entire module bows out — no hooks are registered, no tags are output. The title system (`Basecamp\SEO\TitleManager`) does not perform this check, as Yoast/Rank Math both hook at higher priorities and override it naturally.

> If you install Yoast or Rank Math in the future, the theme's meta description and social tags will automatically stop outputting. The title system will be overridden by the plugin's own `pre_get_document_title` filter. No code changes required.

---

## Head Output Order

```
priority 1  →  Basecamp\SEO\MetaDescription  (description + og:description + twitter:description)
priority 2  →  Basecamp\SEO\SocialMeta       (og:title, og:image, twitter:card, etc.)
priority 3  →  (image dimension hook — no-op, kept for backwards compatibility)
priority 4  →  Cookie Consent defaults     (GA Consent Mode v2 — see cookie consent docs)
priority 5  →  GA4 analytics snippet
```
