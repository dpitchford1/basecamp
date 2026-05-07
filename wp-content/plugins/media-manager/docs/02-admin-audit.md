# Media Manager — Admin & Settings Audit

**Status:** ✅ Complete  
**Last updated:** 2026-05-04  
**Source audited:** `media-library-plus.php`, `includes/mlf-settings.php`, `includes/mlfp-bda-options.php`

---

## 1. Admin Menu Structure (Current Plugin)

All pages are registered under the `admin_menu` hook. The current plugin creates a top-level menu node plus several sub-pages:

| Menu Slug | Title | Type | Keep? |
|---|---|---|---|
| `mlf-folders8` | Media Library Folders | Top-level (custom) | ✅ Keep (rename) |
| `mlf-settings8` | Settings | Sub-page | ✅ Keep (rebuild) |
| `mlf-thumbnails8` | Thumbnails | Sub-page | ✅ Keep |
| `mlf-image-seo8` | Image SEO | Sub-page | ❌ Drop |
| `mlf-support8` | Support | Sub-page | ❌ Drop |
| `mlf-support-articles8` | Support Articles | Sub-page | ❌ Drop |
| `mlf-support-tips8` | Support Tips | Sub-page | ❌ Drop |
| `mlf-support-sys-info8` | System Info | Sub-page | ❌ Drop |

**Rebuild target menu structure:**

```
Media Manager (top-level)
├── Library          (main folders + files view)
├── Settings         (options + BDA)
└── Thumbnails       (regeneration tool)
```

---

## 2. Settings Page — Options Tab (`mlf-settings8`)

### Tab: Options

| Option Label | Option Key (`wp_options`) | Type | Default | Keep? |
|---|---|---|---|---|
| Number of images to display | `mlf_items_per_page` | int | `500` | ✅ Keep |
| Disable large image scaling | `mlfp_disable_scaling` | on/off | `off` | ✅ Keep |
| Add index to postmeta table | `mgmlp-index` | on/off | `off` | 🔄 Redesign — handle at activation |
| Skip .webp files when syncing | `mlfp-skip-webp-files` | on/off | `off` | ✅ Keep |
| Move or copy default | `mgmlp_move_or_copy` | on/off | `on` | ✅ Keep |
| Sort order (ASC/DESC) | `mlf_sort_order_type` | ASC/DESC | `ASC` | 🔄 Redesign — user meta, not global |
| Sort field (date/title) | `mgmlp_sort_order` | 0/1 | `0` | 🔄 Redesign — user meta |

### Tab: Block Direct Access

| Option Label | Option Key (`wp_options`) | Type | Default | Keep? |
|---|---|---|---|---|
| Activate Block Direct Access | `mlfp-bda` | on/off | `off` | ✅ Keep |
| Prevent directory listing | `mlfp-bda-dir-listing` | on/off | `off` | ✅ Keep |
| Prevent hotlinking | `mlfp-bda-hotlinking` | on/off | `off` | ✅ Keep |
| Auto-protect new uploads | `mlfp-bda-auto-protect` | on/off | `off` | ❌ Drop |
| Display protected images on frontend | `mlfp-bda-display-fe-images` | on/off | `off` | ❌ Drop |
| Disable right-click on images | `mlfp-bda-prevent-right-click` | on/off | `off` | ❌ Drop |
| User roles who can view protected files | `mlfp-bda-user-role` | admins/authors | `admins` | ✅ Keep |
| Custom no-access page | `mlfp-no-access-page-id` / `mlfp-no-access-page-id-title` | int / string | `0` / `''` | ✅ Keep |
| IP block list (managed via AJAX) | `mgmlp_blocked_ips` table | DB table | — | ✅ Keep |
| Download page ID | `mlfp-download-page` | int | — | ❌ Drop |

---

## 3. Main Library Page (`mlf-folders8`)

This is the primary admin screen. It contains two panes:

### Left pane — Folder tree
- jsTree component showing the full physical folder hierarchy
- Root: `uploads/` directory
- Right-click context menu: Hide folder / Delete folder
- Clicking a folder loads its contents via AJAX

### Toolbar (above file grid)
| Icon / Action | Description | Keep? |
|---|---|---|
| Add Folder | Creates a new physical directory + registers in DB | ✅ Keep |
| Upload Files | Opens drag-and-drop upload panel | ✅ Keep |
| Refresh Folders | Triggers check for new server-side folders | ✅ Keep |
| Move Files | Sets move mode for drag-and-drop | ✅ Keep |
| Copy Files | Sets copy mode for drag-and-drop | ✅ Keep |
| Order by Date | Sorts file grid by upload date | ✅ Keep |
| Sort by Title | Sorts file grid by filename | ✅ Keep |
| Reverse Order | Toggles ASC/DESC | ✅ Keep |
| Sync | Scans current folder for new/FTP-uploaded files | ✅ Keep |
| Rename File | Rename the selected file | ✅ Keep |
| Regenerate Thumbnails | Regen thumbs for selected files | ✅ Keep |
| Delete Files | Delete selected files | ✅ Keep |
| Add to MaxGalleria | Send selected images to a MaxGalleria gallery | ❌ Drop |

### File Grid
- Thumbnail-per-file display
- Checkbox per file for multi-select
- Shift-click range select
- Select All checkbox
- Clicking a file opens native WP edit-attachment in new tab
- Bulk Actions dropdown (Block/Unblock, Download links — mostly BDA; keep only what's kept from BDA)

### Search / Find
- Text field + button to search files/folders by name
- Results page shows matching items; clicking navigates to that folder

---

## 4. Thumbnails Page (`mlf-thumbnails8`)

Standalone page for bulk thumbnail regeneration.

| Element | Description | Keep? |
|---|---|---|
| Folder selector | Pick which folder to regenerate | ✅ Keep |
| Regenerate button | Kicks off chunked AJAX regen process | ✅ Keep |
| Progress indicator | Shows per-file progress | ✅ Keep |
| Skip SVGs | Auto-excluded from regen | ✅ Keep |

---

## 5. Capabilities & Role Access

### Current plugin
- All actions gated behind `manage_options` (Administrators only)
- Upload uses `upload_files` check (available to Authors+)
- Capability for thumbnail regen is filterable via `regenerate_thumbs_cap` filter (defaults to `manage_options`)

### Rebuild target

| Capability | Role | Access |
|---|---|---|
| `manage_options` | Administrator | Full access — all settings, BDA config, IP management |
| `edit_others_posts` | Editor | Full library access — folders, files, upload, move, copy, sync, regen |
| `upload_files` | Author | Upload only (within native WP media — **no** Media Manager admin access) |
| Subscribers | None | No access |

> **Note:** The capability check for the admin menu pages themselves should use `edit_others_posts` as the minimum so Editors can see and use the plugin. BDA settings remain admin-only (`manage_options`).

---

## 6. Notices & Promotional UI (All Drop)

The current plugin contains the following notice/upsell scaffolding — **all removed in rebuild:**

- Review notice (stored in user meta `maxgalleria_mlp_feature_notice` — prompts for wp.org review after 1 day)
- Feature notice / update notice banner
- "Upgrade to Pro" links throughout admin UI
- Inline advertising for MaxGalleria galleries
- `mlpp_hide_template_ad` AJAX handler (hides a promotional template notice)

---

## 7. Help Panel

The current plugin uses a slide-out help panel (toggle via help icon in header). Each admin page has contextual help content.

**Rebuild approach:** Use WordPress's native `add_help_tab()` API on the screen object instead of a custom slide-out panel. Cleaner, accessible, and consistent with WP admin conventions.
