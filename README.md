# Basecamp WordPress Theme Framework

A modular, extensible WordPress theme framework and function library for rapid development, prototyping, and production sites.

## Features

- **SVG Icon System**  
  Easily manage and output SVG icons (UI and social) with a centralized, extensible class.

- **SEO Modules**  
  Modular SEO support built in for meta descriptions, social meta (Open Graph/Twitter), and extensible, context-aware title generation – no SEO plugins required.

- **Frontend Helpers**  
  - Output critical CSS inline with caching.
  - Clean up HTML output (self-closing tags, etc.).
  - Schema.org markup helpers.
  - Menu enhancements (active class, social icons).
  - Related posts and numeric pagination.
  - Customizable header and logo logic.
  - Utility functions for template authors.

- **Admin & Development Tools**  
  - Admin helpers for sanitization and performance.
  - DevPilot: local-only debug/dev bar with template and context info.
  - Increased admin PHP timeout for heavy operations.
  - Customizer field sanitization and validation.

- **WooCommerce & CPT Support**  
  - Title and meta logic extensible for WooCommerce, custom post types, and taxonomies.
  - Easily add new logic for any CPT or plugin via extension classes.

- **Scheduled Events & REST API**  
  - Ready for custom scheduled tasks and REST endpoints.
  - Modular REST API endpoint registration.

- **Performance & Clean Code**  
  - Removes unnecessary resource hints and speculative loading.
  - Cleans up sidebar logic and disables unused features by default.
  - Minimal, modern markup with accessibility in mind.

## Structure

- `/inc/frontend/` – Frontend helpers, SVG icons, template logic.
- `/inc/seo/` – SEO modules (titles, meta, social meta), class-based and extensible.
- `/inc/admin/` – Admin and sanitization helpers.
- `/inc/development/` – Local dev tools and debug helpers.
- `/inc/core/` – Core theme logic and scheduled events.
- `/inc/rest/` – REST API endpoints.

## Usage

- Designed for use as a parent theme, starter theme, or as a function library for custom themes.
- Modular: enable/disable features by including or commenting out modules in `functions.php`.
- Easily extend SEO/title logic for any CPT or plugin by adding new extension classes.
- All helpers and classes are namespaced to avoid conflicts.

## Getting Started

1. Clone or copy the theme into your `wp-content/themes/` directory.
2. Activate the theme in WordPress.
3. Customize or extend modules as needed for your project.

## Extending

- Add new frontend or admin helpers in the appropriate `/inc/` subdirectory.
- Add new SEO/title logic by creating a new extension class and registering it in the title manager.
- Use the DevPilot bar for local development and debugging.
- Override or extend any feature via child theme or custom plugin.

---

**Note:**  
Do not add custom code directly to this theme—use a child theme or custom plugin for your site-specific customizations to ensure upgradability.

---
