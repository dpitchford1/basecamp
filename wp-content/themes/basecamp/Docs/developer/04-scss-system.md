# SCSS System

The theme uses **Dart Sass modern modules** (`@use` / `@forward`) compiled by the Live Sass Compiler VS Code extension. There is no separate build pipeline.

---

## Compiled Bundles

| File | Purpose | Loaded |
|------|---------|--------|
| `critical-css.min.css` | Above-fold critical styles, inlined in `<head>` | Via PHP `file_get_contents()` |
| `basecamp-base-layout.min.css` | Base layout, header, nav, responsive | `<link>` in `header.php` |
| `basecamp-global-layout.min.css` | Page-level components, sections | `<link>` in `header.php` |
| `cookie-consent.min.css` | Cookie banner | `wp_enqueue_style` |

Third-party CSS (`swiper.min.css`, `video-player.min.css`) lives in `assets/css/resources/` and is loaded separately per template.

---

## SCSS Source Structure

```
assets/css/scss/
  critical-css.scss           Entry: critical (above-fold) styles
  basecamp-base-layout.scss         Entry: base layout bundle
  basecamp-global-layout.scss       Entry: global layout bundle
  cookie-consent.scss         Entry: cookie banner

  critical-css/               Partials for critical CSS
  basecamp-base-layout/             Partials for base layout
    _basecamp-header.scss           Barrel — forwards header partials with namespaces
    header/
      _global-header.scss     Site header styles + bp-* mixins
      _menu-global.scss       Desktop nav styles + menu-global-bp-* mixins
      _menu-mobile.scss       Mobile nav styles
  basecamp-global-layout/           Partials for global layout
    components/               Page section components
  basecamp-toolbox/                 Shared mixins, variables, functions
```

---

## Namespace Pattern

Barrel files use `@forward ... as <prefix>-*` to namespace their partials:

```scss
// _basecamp-header.scss
@forward "header/global-header" as basecamp-header-*;
@forward "header/menu-global"   as menu-global-*;
@forward "header/menu-mobile"   as menu-mobile-*;
```

This means mixins from `_menu-global.scss` are called as `basecamp-header.menu-global-bp-768()` inside the responsive coordinator.

---

## Responsive Coordinator

All breakpoint mixin calls are centralized in:

```
assets/css/scss/basecamp-base-layout/_responsive.scss
```

This file is the single place to add new breakpoint calls — never call `@include` breakpoint mixins inline in component files.

---

## Breakpoints

| Mixin | Width | Use |
|-------|-------|-----|
| `bp-480()` | max 480px | Small mobile |
| `bp-600()` | max 600px | Mobile |
| `bp-768()` | max 768px | Tablet portrait |
| `bp-920()` | max 920px | Tablet landscape |
| `bp-1024()` | max 1024px | Small desktop |
| `bp-1280()` | max 1280px | Desktop |
| `bp-1440()` | max 1440px | Large desktop |
| `bp-1600()` | max 1600px | Wide |
| `bp-2100()` | max 2100px | Ultra-wide |

---

## Raw Media Query Values

```scss
@media (min-width: 30em)    { }  /* 480px  */
@media (min-width: 37.5em)  { }  /* 600px  */
@media (min-width: 48em)    { }  /* 768px  */
@media (min-width: 57.5em)  { }  /* 920px  */
@media (min-width: 64em)    { }  /* 1024px */
@media (min-width: 80em)    { }  /* 1280px */
@media (min-width: 90em)    { }  /* 1440px */
@media (min-width: 100em)   { }  /* 1600px */
@media (min-width: 131.25em){ }  /* 2100px */
```

---

## Workflow

1. Edit any `.scss` file in `assets/css/scss/`
2. Live Sass Compiler automatically compiles to `assets/css/build/*.min.css` on save
3. Auto-Minify handles the `.min.css` output
4. Commit both the `.scss` source and the compiled `.min.css` — the compiled file is what the browser loads

> Do not edit compiled `.min.css` files directly. Changes will be overwritten on next SCSS save.

---

## Adding a New Component

1. Create partial in the appropriate subfolder (e.g. `basecamp-global-layout/components/_my-component.scss`)
2. `@forward` it from the relevant barrel file
3. Add any breakpoint mixins to `_responsive.scss`
4. Save — compiler handles the rest
