# Kaneism — Child Theme

Child theme of **Basecamp**. Kaneism is the author's own site — the first real-world project built on the Basecamp parent theme.

---

## Requirements

- WordPress 6.0+
- PHP 8.1+
- **Basecamp parent theme** installed and present at `wp-content/themes/basecamp/`

---

## Activation

1. Ensure the Basecamp parent theme is installed (but **not** activated)
2. Activate **Kaneism** via WP Admin → Appearance → Themes
3. WordPress will automatically load Basecamp's `functions.php` first, then this theme's

---

## Structure

```
kaneism/
  style.css              WordPress theme header — declares Basecamp as parent
  functions.php          Child bootstrap — project filters, module requires
  inc/
    admin/               Project-specific admin tweaks
    frontend/            Project-specific frontend helpers
    theme-functions/     Project CPTs, taxonomies, custom helpers
  screenshot.png
```

---

## How It Works

The Basecamp parent provides all core PHP modules automatically. This child only needs to:

1. **Add filters** to customise parent behaviour (body classes, footer links, CF7 slugs, schema data)
2. **Require project modules** for anything project-specific (CPTs, custom metaboxes, etc.)
3. **Override templates** by placing same-named files in this folder (WordPress template hierarchy handles the rest)

See `functions.php` for the full list of available parent filters with examples.

---

## Overriding Templates

Any template from the parent can be overridden by placing a same-named file here:

| Override this... | By creating... |
|---|---|
| `basecamp/header.php` | `kaneism/header.php` |
| `basecamp/footer.php` | `kaneism/footer.php` |
| `basecamp/page.php` | `kaneism/page.php` |
| `basecamp/single.php` | `kaneism/single.php` |
| `basecamp/template-parts/video-carousel.php` | `kaneism/template-parts/video-carousel.php` |

Most projects won't need to override `header.php` or `footer.php` — use the `basecamp_header_logo` and `basecamp_footer_legal_links` filters in `functions.php` instead.

---

## CSS

CSS strategy is deferred — to be decided. See Basecamp Phase 4 roadmap.

---

## Developer Reference

Full documentation for the parent theme lives at:  
**WP Admin → Basecamp Docs** (Dashboard menu)

Or browse the source at `wp-content/themes/basecamp/Docs/developer/`.
