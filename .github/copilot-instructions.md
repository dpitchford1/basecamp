# Basecamp Theme Agent Guide

## Architecture: Parent / Child

- **Basecamp is a parent theme** — it provides all shared infrastructure. Never add project-specific code to Basecamp. Each project runs as a **child theme** (e.g. `kaneism`) that declares `Template: basecamp` in its `style.css`.
- **Child theme bootstrap** lives in the child's `functions.php`. It must only `require_once` project-specific modules, never re-require parent modules. WordPress loads the parent `functions.php` automatically first.
- **Child theme namespaces** follow a project-specific hierarchy (e.g. `Kaneism\ThemeFunctions`). Any function called directly from a template must be wrapped as a plain global function in the child's `functions.php` — namespaced functions are not callable from templates without a wrapper.
- **Child `after_setup_theme`** is the correct place to extend parent theme supports (e.g. adding a CPT to `post-thumbnails`). Do not edit `class-basecamp.php` for project-specific extensions.
- **Active child themes** currently in the repo: `kaneism` (`wp-content/themes/kaneism/`) — mural portfolio with WooCommerce shop, Work plugin CPT.

---

## Parent Theme (Basecamp)

- **Stack** WordPress parent theme lives in `wp-content/themes/basecamp`; `functions.php` bootstraps all feature modules.
- **Bootstrap order** Settings → Core → Frontend → Admin → SEO → ThemeFunctions → ImgOptimization → REST → Cron → Dev → Ecommerce. Keep `require_once` calls grouped by area; comment toggles are the accepted way to disable subsystems.
- **Namespaces** All `inc/` classes live under `Basecamp\*` (`Basecamp\Admin`, `Basecamp\Frontend`, `Basecamp\SEO`, `Basecamp\Core`, `Basecamp\ThemeFunctions`, `Basecamp\Ecommerce`, `Basecamp\Development`). New classes must declare `namespace Basecamp\<Area>;` and be wired with a `require_once` in `functions.php`. Back-compat aliases (`Basecamp_Frontend`, `Basecamp_Settings`) are registered via `class_alias()` — do not add new ones without a clear compat reason.
- **Templates** Basecamp has no project-specific page templates. `template-parts/` holds only generic partials. Project templates live in the child theme.
- **Frontend** `inc/frontend/class-basecamp-frontend.php` (`Basecamp\Frontend\Frontend`, aliased `Basecamp_Frontend`) provides `output_critical_css()`, `page_navi()`, `related_posts()`, and menu class injection — call these instead of re-implementing. `output_critical_css( $path, $transient_key )` handles file-mtime caching; always pass a child-theme-specific transient key when calling from a child.
- **SVG Icons** `inc/frontend/class-basecamp-svg-icons.php` (`Basecamp\Frontend\SVGIcons`) feeds UI and social icons; extend its maps when adding new providers.
- **Bloat Removal** `inc/frontend/remove-bloat.php` aggressively dequeues core assets, disables heartbeat, strips REST/oEmbed links — new code must not rely on those hooks being present.
- **SEO Titles** `inc/seo/basecamp-title-functions.php` uses extension classes with `maybe_title`; register new contexts by appending FQCNs to `Basecamp\SEO\TitleManager::$extensions`.
- **Meta Descriptions** `inc/seo/basecamp-meta-description-functions.php` auto-builds descriptions per template; reuse `Basecamp\SEO\MetaDescription::get_meta_description` when custom markup needs the same logic.
- **Social Meta** `inc/seo/basecamp-social-meta-functions.php` injects Open Graph/Twitter tags; ensure new share images honor the `basecamp_default_share_image` theme mod.
- **Theme Settings** `inc/admin/class-basecamp-settings.php` (`Basecamp\Admin\Settings`, aliased `Basecamp_Settings`) is loaded first — use `Basecamp\Admin\Settings::get('key', 'default')` to read any feature flag or configured value.
- **Page → Theme Assignment** `inc/admin/class-basecamp-page-theme.php` (`Basecamp\Admin\PageTheme`) adds a sidebar meta box and sortable "Theme" column on the Pages list table. Meta key: `_basecamp_page_theme` (stores theme slug). Useful in multi-child-theme installs to identify which page belongs to which theme.
- **Media/WebP** `inc/img-optimization/basecamp-webp-functions.php` swaps image URLs to `.webp` variants; respect `basecamp_should_exclude_webp()` and add `no-webp` class if markup must keep originals.
- **Admin UX** `inc/admin/class-basecamp-admin.php` (`Basecamp\Admin\Admin`) enforces classic editor, hides menus, brands login, raises timeouts — new admin features belong here.
- **Admin Helpers** `inc/admin/basecamp-admin-helpers.php` houses sanitizers; reuse `AdminHelpers::sanitize_choices()` and `AdminHelpers::sanitize_checkbox()` for Customizer/meta controls.
- **Meta Boxes** Two repeaters: `inc/theme-functions/basecamp-meta-link-list.php` (`Basecamp\ThemeFunctions\MetaLinkList`) for link lists; `inc/frontend/class-basecamp-video-carousel-metabox.php` for video carousels. Both expose filters — extend rather than duplicating storage.
- **REST Endpoints** `inc/rest/basecamp-rest-endpoints.php` registers under `basecamp/v1`; follow that pattern and remove debug `error_log` calls before deploying.
- **Scheduled Events** `inc/core/basecamp-scheduled-events.php` is stubbed — schedule recurring jobs here, isolated from template logic.
- **Development Tools** `inc/development/class-basecamp-development.php` loads DevPilot when `WP_ENVIRONMENT_TYPE === 'local'` (primary) or `REMOTE_ADDR` is `127.0.0.1`/`::1` (fallback). Assets are bundled in `inc/development/css|js/` and enqueued via `wp_enqueue_*`. Set `define( 'WP_ENVIRONMENT_TYPE', 'local' )` in `wp-config.php` for local dev.

---

## Child Theme (Kaneism)

- **Location** `wp-content/themes/kaneism/` — declares `Template: basecamp`.
- **Namespace** `Kaneism\ThemeFunctions` for all child `inc/` classes.
- **Global wrappers** Any namespaced function called from a template needs a plain global wrapper in `kaneism/functions.php`. Pattern: `function kaneism_foo( ...$args ) { return \Kaneism\ThemeFunctions\kaneism_foo( ...$args ); }`. Missing wrappers cause fatal errors — scan templates and verify wrappers exist before adding new namespaced helpers.
- **Critical CSS** Call `Basecamp_Frontend::output_critical_css( ABSPATH . 'assets/kaneism/css/build/kaneism-inline-head.min.css', 'kaneism_critical_css' )` — always pass the child-specific transient key to avoid collisions with the parent.
- **WooCommerce** Activated via `add_theme_support( 'woocommerce' )` in `after_setup_theme` in kaneism's `functions.php`. WC template overrides live in `kaneism/woocommerce/`.
- **Post thumbnails for CPTs** Extended in the child: `add_theme_support( 'post-thumbnails', [ 'post', 'page', 'work' ] )` in `after_setup_theme`. Do not edit the parent's `class-basecamp.php` for this.
- **Work plugin** `wp-content/plugins/work/` — registers CPT `work`, taxonomy `work_category`, gallery/featured/project-details/schema meta boxes, template loader, data API, admin columns, and a Documentation submenu page.

---

## Workflows & Gotchas

- **Transient flush** after touching critical CSS: `wp transient delete kaneism_critical_css kaneism_critical_css_mtime basecamp_critical_css basecamp_critical_css_mtime`
- **WebP cache flush:** `wp cache flush`
- **Testing** Validate manually in WordPress; verify menu states, SEO tags, image fallbacks across template contexts; hit `/wp-json/basecamp/v1/ping` to confirm REST wiring.
- **Sidebars** The theme removes sidebars (`is_active_sidebar` always false) — gate any sidebar-dependent feature behind an explicit opt-in.
- **Bloat hooks** `RemoveBloat` strips heartbeat, oEmbed, REST link headers — do not rely on these being present on the frontend.
- **Coding Style** New classes: `namespace Basecamp\<Area>` or `namespace ChildTheme\<Area>` with matching declaration. WordPress escaping/sanitizing patterns throughout (`esc_html`, `esc_url`, `esc_attr`, `sanitize_text_field`, `wp_kses_post`). Self-documenting hooks with short inline comments when behavior is non-obvious.
- **Extending filters** `basecamp_register_nav_menus`, `basecamp_link_list_meta_box_args`, `basecamp_cf7_page_slugs`, `basecamp_body_page_classes` — prefer these over modifying parent files.
