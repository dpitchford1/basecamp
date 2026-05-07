# Media Manager — Hook Inventory

**Status:** ✅ Complete  
**Last updated:** 2026-05-05  
**Source audited:** `media-library-plus.php` (`setup_hooks()`), `includes/maxgalleria-media-library-hooks.php`

---

## Legend

| Symbol | Meaning |
|---|---|
| ✅ Keep | Port to new plugin |
| 🔄 Redesign | Keep intent, rewrite implementation |
| ❌ Drop | Remove entirely |

---

## 1. WordPress Core Actions

| Hook | Callback | Priority | Notes | Keep? |
|---|---|---|---|---|
| `init` | `load_textdomain` | 10 | i18n — keep if plugin ships with translations | ✅ Keep |
| `init` | `register_mgmlp_post_type` | 10 | Register `mgmlp_media_folder` CPT | ✅ Keep |
| `init` | `get_upload_status` | 10 | Reads WP upload dir config | ✅ Keep |
| `admin_init` | `ignore_notice` | 10 | Dismisses upsell notice | ❌ Drop |
| `admin_menu` | `setup_mg_media_plus` | 10 | Registers all admin pages | ✅ Keep (rebuild) |
| `admin_print_styles` | `enqueue_admin_print_styles` | 10 | Enqueues plugin CSS | 🔄 Redesign — use `admin_enqueue_scripts` |
| `admin_print_scripts` | `enqueue_admin_print_scripts` | 10 | Enqueues plugin JS | 🔄 Redesign — use `admin_enqueue_scripts` |
| `admin_enqueue_scripts` | `bda_add_class_to_media_library_grid_elements` | 10 | BDA — adds CSS class to blocked files in WP media grid | ✅ Keep (BDA) |
| `admin_enqueue_scripts` | `bda_load_protected_file` | 10 | BDA — enqueues script for protected image on edit-attachment screen | ❌ Drop (frontend proxy dropped) |
| `admin_enqueue_scripts` | `enqueue_post_media` | 99 | Loads `mm-post-media.js` (WP media frame extension) on `post.php` / `post-new.php` and any screen where `media-editor` is already enqueued. Calls `wp_enqueue_media()` only on those screens. | ✅ Keep |
| `wp_enqueue_media` | `mlfp_enqueue_media` | 99 | Old plugin — hooks into WP media uploader | ❌ Drop — replaced by `enqueue_post_media` |
| `wp_enqueue_scripts` | `bda_enqueue_scripts` | 10 | BDA — enqueues jQuery on frontend for right-click disable | ❌ Drop |
| `wp_footer` | `mlfp_display_protected_file` | 10 | BDA — outputs JS for frontend protected image proxy | ❌ Drop |
| `new_folder_check` | `admin_check_for_new_folders` | 10 | Scheduled — daily folder scan | ✅ Keep |
| `delete_attachment` | `delete_folder_attachment` | 10 | Removes folder record when attachment is deleted | ✅ Keep |
| `register_activation_hook` | `do_activation` | — | Activation: create tables, scan attachments, schedule cron | ✅ Keep (rebuild) |
| `register_deactivation_hook` | `do_deactivation` | — | Deactivation: (currently empty body) | ✅ Keep — add cron clearance |

---

## 2. WordPress Core Filters

| Hook | Callback | Priority | Args | Notes | Keep? |
|---|---|---|---|---|---|
| `wp_generate_attachment_metadata` | `add_attachment_to_folder2` | 10 | 4 | Fires after upload; places new attachment into the current folder | ✅ Keep |
| `wp_prepare_attachment_for_js` | `bda_prepare_attachment_for_js` | 10 | 3 | BDA — adds protected status data to attachment JS object | ✅ Keep (BDA) |
| `big_image_size_threshold` | `__return_false` | 10 | — | Conditional — only applied when "disable scaling" is on | ✅ Keep |
| `admin_body_class` | `mlf_body_classes` | 10 | — | Adds plugin-specific body classes to admin pages | ✅ Keep |
| `site_transient_update_plugins` | _(anonymous)_ | 10 | — | Suppresses WP update checks for the old plugin | ❌ Drop — not needed for own plugin |
| `mod_rewrite_rules` | `mlfp_update_htaccess` | 10 | — | BDA — writes `.htaccess` rules for protected directory | ✅ Keep (BDA) |
| `regenerate_thumbs_cap` | _(exposes filter)_ | — | — | Custom filter — allows overriding the capability required to regen thumbnails | ✅ Keep |

---

## 3. AJAX Endpoints

All are admin-only (`wp_ajax_*`) unless marked `nopriv`.

### 3a. Keep

| AJAX Action | Callback | Auth | Purpose |
|---|---|---|---|
| `create_new_folder` | `create_new_folder` | admin | Create a new physical folder |
| `delete_maxgalleria_media` | `delete_maxgalleria_media` | admin | Delete one or more files |
| `upload_attachment` | `upload_attachment` | admin | Handle file upload to a specific folder |
| `maxgalleria_rename_image` | `maxgalleria_rename_image` | admin | Rename a file |
| `sort_contents` | `sort_contents` | admin | Change sort field |
| `mgmlp_move_copy` | `mgmlp_move_copy` | admin | Move or copy file to another folder |
| `mlf_check_for_new_folders` | `mlf_check_for_new_folders` | admin | Immediate refresh of folder tree |
| `mlp_load_folder` | `mlp_load_folder` | admin | Load folder contents (navigation) |
| `mlp_display_folder_contents_ajax` | `mlp_display_folder_contents_ajax` | admin | Render folder file grid |
| `mlp_display_folder_contents_images_ajax` | `mlp_display_folder_contents_images_ajax` | admin | Render images-only view |
| `mgmlp_display_folder_nav_ajax` | `display_folder_nav_ajax` | admin | Render folder nav pane |
| `mlp_get_folder_data` | `mlp_get_folder_data` | admin | Get folder metadata |
| `regen_mlp_thumbnails` | `regen_mlp_thumbnails` | admin | Trigger thumbnail regeneration for selected files |
| `regeneratethumbnail` | `ajax_process_image` | admin | Process single image during regen |
| `hide_maxgalleria_media` | `hide_maxgalleria_media` | admin | Hide folder from tree |
| `mlf_change_sort_type` | `mlf_change_sort_type` | admin | Toggle ASC/DESC sort |
| `mlfp_set_scaling` | `mlfp_set_scaling` | admin | Toggle large image scaling on/off |
| `mlfp_run_sync_process` | `mlfp_run_sync_process` | admin | Run sync chunk |
| `mlfp_process_mc_data` | `mlfp_process_mc_data` | admin | Process move/copy batch data |
| `mlfp_process_bdp` | `mlfp_process_bdp` | admin | Save BDA settings (partial — only kept BDA options) |
| `mlfp_save_noaccess_page` | `mlfp_save_noaccess_page` | admin | Set no-access redirect page |
| `mlfp_bdp_report` | `mlfp_bdp_report` | admin | View protected files report |
| `mlfp_block_new_ip` | `mlfp_block_new_ip` | admin | Add IP to block list |
| `mlfp_unblock_ips` | `mlfp_unblock_ips` | admin | Remove IPs from block list |
| `mlfp_get_block_ips` | `mlfp_get_block_ips` | admin | Fetch current IP block list |
| `mlfp_toggle_file_access` | `mlfp_toggle_file_access` | admin | Block/unblock a file |
| `mlfp_update_bda_record` | `mlfp_update_bda_record` | admin | Update a protected file's record |
| `mlfp_display_bda_info` | `mlfp_display_bda_info` | admin | Display BDA status info |
| `mlfp_load_image` | `mlfp_load_image` | admin | Serve protected image on edit-attachment screen |

### 3b. Drop

| AJAX Action | Reason |
|---|---|
| `add_to_max_gallery` | MaxGalleria integration — cut |
| `mlpp_hide_template_ad` | Upsell ad dismissal — cut |
| `mlpp_create_new_ng_gallery` | NextGen Gallery integration — cut |
| `mlp_image_seo_change` | Image SEO feature — cut |
| `mlf_hide_info` | Info/notice banner dismissal — cut |
| `mlfp_load_fe_image` (nopriv + priv) | Frontend protected image proxy via base64 — cut |
| `mflp_enable_auto_protect` | Auto-protect new uploads — cut |

---

## 4. Custom Hooks (Defined by Plugin for Extensibility)

Defined in `includes/maxgalleria-media-library-hooks.php`:

| Constant | Filter/Action Hook | Purpose | Keep? |
|---|---|---|---|
| `MGMLP_FILTER_POST_TYPE_ARGS` | `mg-media-library-plus_post_type_args` | Filter CPT registration args | ✅ Keep — useful for extensibility |
| `MGMLP_FILTER_ADD_TOOLBAR_BUTTONS` | `mgmlp_add_toolbar_buttons` | Let 3rd parties add toolbar buttons to library UI | ✅ Keep |
| `MGMLP_FILTER_ADD_TOOLBAR_AREAS` | `mgmlp_add_toolbar_areas` | Let 3rd parties add toolbar UI areas | ✅ Keep |

Additional extensibility filters used inline in main file:

| Filter Hook | Purpose | Keep? |
|---|---|---|
| `mlfp_filter_update_tables_links` | Allow 3rd parties to register DB tables to update on file move | ✅ Keep |
| `mlfp_filter_update_tables_fields` | Allow 3rd parties to register DB fields to update on file move | ✅ Keep |
| `regenerate_thumbs_cap` | Override capability for thumbnail regeneration | ✅ Keep |

**Rebuild note:** All kept custom hooks should be renamed with a `media_manager_` prefix in the new plugin for clean namespacing (e.g., `media_manager_post_type_args`, `media_manager_toolbar_buttons`).

---

## 5. Commented-Out / Dead Hooks (Not Used)

These are present in the old plugin but disabled — do not carry forward:

```php
// add_action( 'add_attachment', ... )              — replaced by wp_generate_attachment_metadata
// add_action('wp_ajax_nopriv_copy_media', ...)     — never enabled
// add_action('wp_ajax_copy_media', ...)            — never enabled
// add_action('wp_ajax_nopriv_move_media', ...)     — never enabled
// add_action('wp_ajax_move_media', ...)            — never enabled
// add_action('wp_ajax_nopriv_max_sync_contents', ...) — never enabled
// add_action('wp_ajax_max_sync_contents', ...)     — never enabled
// add_action('wp_ajax_nopriv_mlp_display_folder_ajax', ...) — never enabled
// add_action('admin_menu', hide_mlf_menu_items)   — commented out
```
