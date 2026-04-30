# Theme Settings

**Appearance → Theme Settings** is the central configuration page for the Basecamp theme. It exposes a small set of foundational options that would otherwise require touching PHP source files.

All values are stored as a single serialised option array (`basecamp_theme_settings`) and read anywhere via the `Basecamp\Admin\Settings::get()` helper (aliased as `Basecamp_Settings::get()` for back-compat).

Defined in `inc/admin/class-basecamp-settings.php`.

---

## Settings Reference

### Analytics

| Setting | Key | Type | Default |
|---|---|---|---|
| GA4 Measurement ID | `ga_id` | Text | *(empty — GA disabled)* |

Enter a GA4 Measurement ID (e.g. `G-XXXXXXXXXX`) to enable Google Analytics. Leave empty to disable the analytics snippet entirely — no gtag script will be output.

GA loads on all environments but only sends config hits when `BASECAMP_ENV` is `production` (or unset). On `local` and `staging` the script loads but config is skipped, and a `console.info` note is printed instead.

---

### Announcement Bar

| Setting | Key | Type | Default |
|---|---|---|---|
| Enable announcement bar | `toast_enabled` | Checkbox | `0` (disabled) |
| Announcement text | `toast_text` | Text | *(empty)* |
| Announcement URL | `toast_url` | URL | *(empty — no link)* |

A dismissable banner shown at the top of every frontend page. When `toast_enabled` is `'1'` and `toast_text` is non-empty, the bar renders automatically via `the_toast()` (called in `header.php`).

If `toast_url` is set, the text is wrapped in an anchor tag pointing to that URL. Leave empty for a text-only bar.

Dismissed state is persisted per-visitor in `localStorage`, keyed to an MD5 hash of the bar content. Editing the text or URL automatically resets the dismissed state for all visitors.

---

### Privacy & Compliance

| Setting | Key | Type | Default |
|---|---|---|---|
| Cookie Consent Banner | `cookie_compliance` | Checkbox | `1` (enabled) |

When enabled, the GDPR/CCPA consent banner and Google Consent Mode v2 defaults are active. The banner blocks analytics cookies until the visitor accepts.

When disabled, the entire `Basecamp\Frontend\CookieConsent` class is never initialised — no banner markup, no Consent Mode scripts, and no cookie-related JS is loaded on the frontend.

The banner copy (headline, body text, button labels, position) is configured separately at **Settings → Cookie Consent**.

---

### Features

| Setting | Key | Type | Default |
|---|---|---|---|
| Structured Data | `schema_output` | Checkbox | `1` (enabled) |
| WebP Image Optimisation | `webp_optimization` | Checkbox | `1` (enabled) |

**Structured Data** — controls whether `Basecamp\SEO\Schema::init()` is called. When disabled, no Schema.org JSON-LD is output in the page head.

**WebP Image Optimisation** — when disabled, the three WebP `require_once` files are never loaded, so no URL rewriting or on-upload conversion occurs. Useful for hosts with broken WebP support or projects where the client is supplying pre-optimised assets.

---

### Verification

| Setting | Key | Type | Default |
|---|---|---|---|
| Google Search Console | `gsc_verification` | Text | *(empty)* |

Paste only the `content` value from the `<meta name="google-site-verification" content="…">` tag provided by Google Search Console — not the full HTML tag. The meta tag is injected into `<head>` at `wp_head` priority 1.

---

## Reading Settings in Code

```php
// Simple read — returns the stored value or the default if not yet saved.
$ga_id = Basecamp\Admin\Settings::get( 'ga_id' );

// With an explicit fallback.
$webp = Basecamp\Admin\Settings::get( 'webp_optimization', '1' );
```

`Basecamp\Admin\Settings` is loaded early in `functions.php` (before Frontend modules) so `::get()` is safe to call from any subsequent module. The `Basecamp_Settings` alias is registered immediately after for back-compat with any code that uses the old name.

---

## Overriding with `wp-config.php`

The GA4 Measurement ID can be locked at the server/infrastructure level by defining the constant in `wp-config.php`. When set, it takes precedence over the database value and a visible warning is shown on the settings page:

```php
define( 'BASECAMP_GA_MEASUREMENT_ID', 'G-XXXXXXXXXX' );
```

No other settings currently support a constant override. Add cases to `Basecamp\Admin\Settings::get()` if additional server-level locks are needed.

---

## Defaults on Fresh Install

All feature flags default to `'1'` (enabled) so that existing installs upgrading to this version of the theme are unaffected — no settings page visit required to preserve previous behaviour.

The GA4 ID defaults to empty, meaning analytics is **off by default** on a clean install until an ID is explicitly entered. This is intentional for a starter theme.
