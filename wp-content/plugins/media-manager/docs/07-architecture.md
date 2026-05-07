# Media Manager — Architecture

**Document:** `07-architecture.md`  
**Status:** 🟡 Draft  
**Last updated:** 2026-05-05  
**Depends on:** `06-prd.md`

---

## 1. Plugin Root

```
wp-content/plugins/media-manager/
├── media-manager.php      ← Plugin header + bootstrap call only
├── uninstall.php          ← Drop tables, delete options, clear cron on plugin delete
├── includes/              ← All PHP classes (manually required via loader)
├── assets/                ← CSS, JS, vendor libraries
└── languages/             ← .pot / .po / .mo translation files
```

`media-manager.php` does three things only: defines constants, instantiates the autoloader, and calls `MediaManager\Core\Plugin::run()`. No logic in the root file.

---

## 2. PHP Namespace Map

```
MediaManager\
├── Core\
│   ├── Plugin               ← Bootstrap: wires all modules, fires mm_loaded action
│   ├── Loader               ← Accumulates add_action / add_filter calls; registers once on run()
│   ├── Activator            ← DB table creation (dbDelta), option seeding, cron schedule
│   ├── Deactivator          ← Clears scheduled cron jobs (does NOT delete data)
│   └── Updater              ← DB migration runner keyed on mm_version option
│
├── CPT\
│   └── FolderPost           ← Registers mm_folder CPT
│
├── Admin\
│   ├── Admin                ← Admin hook registrar — ties all admin modules to Loader
│   ├── Menu                 ← add_menu_page / add_submenu_page calls; capability routing
│   ├── LibraryPage          ← Renders Library admin page (folder tree + file grid)
│   ├── SettingsPage         ← Tabbed settings page (Options + BDA tabs); WP Settings API
│   ├── ThumbnailsPage       ← Thumbnails regeneration admin page
│   └── Assets               ← admin_enqueue_scripts — conditionally loads CSS/JS per page
│
├── FileSystem\
│   ├── FolderManager        ← Create, delete, hide folders on disk + CPT records
│   ├── FileManager          ← Upload, move, copy, rename, delete files on disk + WP records
│   ├── SyncManager          ← Scan a folder for new files; import to WP; register new sub-folders
│   └── LinkUpdater          ← Search-replace embedded file URLs in post_content after move/rename
│
├── Thumbnails\
│   └── RegenManager         ← Queue and process thumbnail regeneration; chunked AJAX
│
├── Security\
│   ├── BdaManager           ← Protected directory, .htaccess writes, block/unblock file
│   └── IpBlocker            ← IP block list CRUD; .htaccess Deny-from rules
│
├── Ajax\
│   └── AjaxHandler          ← All wp_ajax_mm_* handlers (capability-checked, nonce-verified)
│
├── Data\
│   ├── FolderRepository     ← WP_Query + $wpdb wrappers for mm_folder CPT
│   ├── FileRepository       ← $wpdb CRUD for {prefix}mm_files table
│   ├── ProtectedRepository  ← $wpdb CRUD for {prefix}mm_protected table
│   └── IpRepository         ← $wpdb CRUD for {prefix}mm_blocked_ips table
│
└── Helpers\
    └── PathHelper           ← Path/URL conversion utilities; is_path_inside() guard
```

---

## 3. Directory Structure

```
media-manager/
│
├── media-manager.php
│
├── uninstall.php
│
├── includes/
│   ├── Core/
│   │   ├── class-plugin.php
│   │   ├── class-loader.php
│   │   ├── class-activator.php
│   │   ├── class-deactivator.php
│   │   └── class-updater.php
│   │
│   ├── CPT/
│   │   └── class-folder-post.php
│   │
│   ├── Admin/
│   │   ├── class-admin.php
│   │   ├── class-menu.php
│   │   ├── class-library-page.php
│   │   ├── class-settings-page.php
│   │   ├── class-thumbnails-page.php
│   │   └── class-assets.php
│   │
│   ├── FileSystem/
│   │   ├── class-folder-manager.php
│   │   ├── class-file-manager.php
│   │   ├── class-sync-manager.php
│   │   └── class-link-updater.php
│   │
│   ├── Thumbnails/
│   │   └── class-regen-manager.php
│   │
│   ├── Security/
│   │   ├── class-bda-manager.php
│   │   └── class-ip-blocker.php
│   │
│   ├── Ajax/
│   │   └── class-ajax-handler.php
│   │
│   ├── Data/
│   │   ├── class-folder-repository.php
│   │   ├── class-file-repository.php
│   │   ├── class-protected-repository.php
│   │   └── class-ip-repository.php
│   │
│   └── Helpers/
│       └── class-path-helper.php
│
├── assets/
│   ├── css/
│   │   ├── mm-admin.css          ← Compiled admin styles
│   │   └── mm-admin.min.css      ← Minified (Auto-Minify extension)
│   │
│   ├── js/
│   │   ├── mm-library.js         ← Library page: folder tree, file grid, drag-and-drop
│   │   ├── mm-library.min.js
│   │   ├── mm-upload.js          ← Upload panel: drag-and-drop multi-upload
│   │   ├── mm-upload.min.js
│   │   ├── mm-thumbnails.js      ← Thumbnails page: regen queue and progress
│   │   ├── mm-thumbnails.min.js
│   │   ├── mm-bda.js             ← BDA tab: IP management, block/unblock toggle
│   │   ├── mm-bda.min.js
│   │   ├── mm-post-media.js      ← WP media frame extension: "Media Folders" tab
│   │   ├── mm-post-media.min.js
│   │   └── vendor/
│   │       └── jstree/           ← jsTree 3.x (folder tree widget)
│   │
│   └── images/
│       └── icons.svg             ← SVG sprite for toolbar icons (replaces Font Awesome)
│
└── languages/
    └── media-manager.pot
```

---

## 4. Bootstrap Flow

```
media-manager.php
  └─ defines MM_VERSION, MM_PLUGIN_DIR, MM_PLUGIN_URL
  └─ require_once includes/Core/class-plugin.php
  └─ MediaManager\Core\Plugin::run()
       ├─ new Loader()
       ├─ new Activator()   (hooks: register_activation_hook)
       ├─ new Deactivator() (hooks: register_deactivation_hook)
       ├─ new CPT\FolderPost( $loader )
       ├─ new Admin\Admin( $loader )
       │    ├─ new Admin\Menu( $loader )
       │    ├─ new Admin\LibraryPage( $loader )
       │    ├─ new Admin\SettingsPage( $loader )
       │    ├─ new Admin\ThumbnailsPage( $loader )
       │    └─ new Admin\Assets( $loader )
       ├─ new Ajax\AjaxHandler( $loader )
       └─ $loader->run()   ← registers all accumulated add_action / add_filter calls
```

---

## 5. AJAX Action Map

All AJAX actions are prefixed `mm_` and handled by `Ajax\AjaxHandler`. Every handler:
1. Verifies nonce (`wp_verify_nonce`)
2. Checks capability before doing any work
3. Returns JSON via `wp_send_json_success()` / `wp_send_json_error()`

| AJAX Action | Handler Method | Capability | Module |
|---|---|---|---|
| `mm_create_folder` | `create_folder()` | `edit_others_posts` | FolderManager |
| `mm_delete_folder` | `delete_folder()` | `edit_others_posts` | FolderManager |
| `mm_hide_folder` | `hide_folder()` | `edit_others_posts` | FolderManager |
| `mm_refresh_folders` | `refresh_folders()` | `edit_others_posts` | FolderManager |
| `mm_load_folder` | `load_folder()` | `edit_others_posts` | FolderRepository |
| `mm_folder_contents` | `folder_contents()` | `edit_others_posts` | LibraryPage |
| `mm_upload_file` | `upload_file()` | `upload_files` | FileManager |
| `mm_move_copy_file` | `move_copy_file()` | `edit_others_posts` | FileManager |
| `mm_rename_file` | `rename_file()` | `edit_others_posts` | FileManager |
| `mm_delete_files` | `delete_files()` | `edit_others_posts` | FileManager |
| `mm_hide_file` | `hide_file()` | `edit_others_posts` | FileManager |
| `mm_sync_folder` | `sync_folder()` | `edit_others_posts` | SyncManager |
| `mm_sync_chunk` | `sync_chunk()` | `edit_others_posts` | SyncManager |
| `mm_sort_contents` | `sort_contents()` | `edit_others_posts` | FileRepository |
| `mm_regen_thumbnails` | `regen_thumbnails()` | `edit_others_posts` | RegenManager |
| `mm_regen_process` | `regen_process()` | `edit_others_posts` | RegenManager |
| `mm_save_settings` | `save_settings()` | `edit_others_posts` | SettingsPage |
| `mm_save_bda_settings` | `save_bda_settings()` | `manage_options` | BdaManager |
| `mm_toggle_file_access` | `toggle_file_access()` | `manage_options` | BdaManager |
| `mm_save_no_access_page` | `save_no_access_page()` | `manage_options` | BdaManager |
| `mm_get_protected_files` | `get_protected_files()` | `manage_options` | BdaManager |
| `mm_add_blocked_ip` | `add_blocked_ip()` | `manage_options` | IpBlocker |
| `mm_remove_blocked_ips` | `remove_blocked_ips()` | `manage_options` | IpBlocker |
| `mm_get_blocked_ips` | `get_blocked_ips()` | `manage_options` | IpBlocker |

---

## 6. Hook Registration Pattern

All hooks are accumulated via `Loader` then registered once. No `add_action` / `add_filter` calls scattered across class constructors.

```php
// In each module's constructor:
class FolderPost {
    public function __construct( Loader $loader ) {
        $loader->add_action( 'init', $this, 'register_post_type' );
    }
}

// Loader accumulates, then in run():
foreach ( $this->actions as $hook ) {
    add_action( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['args'] );
}
```

---

## 7. Data Flow — File Upload

```
User drops files onto upload zone
  └─ mm-upload.js: FormData POST to admin-ajax.php
       └─ AjaxHandler::upload_file()
            ├─ verify_nonce()
            ├─ current_user_can('upload_files')
            ├─ FileManager::upload( $file, $folder_id )
            │    ├─ wp_handle_upload()          ← writes to folder path
            │    ├─ wp_insert_attachment()
            │    ├─ wp_generate_attachment_metadata()
            │    └─ FileRepository::insert( $attachment_id, $folder_id )
            └─ wp_send_json_success( $attachment_data )
```

---

## 8. Data Flow — File Move

```
User drags file to folder node
  └─ mm-library.js: POST mm_move_copy_file
       └─ AjaxHandler::move_copy_file()
            ├─ verify_nonce()
            ├─ current_user_can('edit_others_posts')
            ├─ FileManager::move( $attachment_id, $dest_folder_id )
            │    ├─ get source path from FileRepository
            │    ├─ physical file move (rename())
            │    ├─ move all thumbnail files
            │    ├─ update _wp_attached_file post meta
            │    ├─ LinkUpdater::update_all( $old_url, $new_url )
            │    │    ├─ wpdb UPDATE on wp_posts
            │    │    └─ apply_filters('mm_update_table_links', $tables)  ← extensibility
            │    └─ FileRepository::update_folder( $attachment_id, $dest_folder_id )
            └─ wp_send_json_success()
```

---

## 9. Data Flow — Folder Sync

```
User clicks Sync toolbar button
  └─ mm-library.js: POST mm_sync_folder
       └─ AjaxHandler::sync_folder()
            ├─ verify_nonce()
            ├─ current_user_can('edit_others_posts')
            ├─ SyncManager::prepare( $folder_id )
            │    ├─ scan physical directory
            │    ├─ identify files not yet in WP library
            │    ├─ store queue in transient mm_sync_queue_{$folder_id}
            │    └─ return count + chunk size to JS
            └─ wp_send_json_success( ['total' => $n, 'chunk' => 10] )

JS loops POSTing mm_sync_chunk until done:
  └─ AjaxHandler::sync_chunk()
       └─ SyncManager::process_chunk( $folder_id )
            ├─ read next N items from transient queue
            ├─ for each file:
            │    ├─ wp_insert_attachment()
            │    ├─ wp_generate_attachment_metadata()
            │    └─ FileRepository::insert()
            └─ wp_send_json_success( ['processed' => $n, 'remaining' => $r] )
```

---

## 10. Constants

Defined in `media-manager.php` before bootstrap:

| Constant | Value |
|---|---|
| `MM_VERSION` | `'1.0.0'` |
| `MM_PLUGIN_DIR` | `plugin_dir_path( __FILE__ )` |
| `MM_PLUGIN_URL` | `plugin_dir_url( __FILE__ )` |
| `MM_NONCE` | `'mm_nonce'` |
| `MM_POST_TYPE` | `'mm_folder'` |
| `MM_TABLE_FILES` | `$wpdb->prefix . 'mm_files'` (set in Activator) |
| `MM_TABLE_PROTECTED` | `$wpdb->prefix . 'mm_protected'` |
| `MM_TABLE_BLOCKED_IPS` | `$wpdb->prefix . 'mm_blocked_ips'` |
| `MM_PROTECTED_DIR` | `'protected-content'` |

---

## 12. WP Media Frame Extension (`mm-post-media.js`)

### Overview

`mm-post-media.js` injects a **"Media Folders" tab** into the standard WordPress media modal, in every context where that modal is opened.

It is enqueued by `Admin\Assets::enqueue_post_media()` (priority 99 on `admin_enqueue_scripts`) exclusively on screens that already load `wp.media`:
- Post/page edit screens (`post.php`, `post-new.php`) — always.
- Any other admin screen where something else has already enqueued `media-editor` (Customizer, WooCommerce, etc.).

### WordPress media frame types

WordPress instantiates different frame classes depending on context. Understanding this is the key to extending the modal correctly:

| Context | Frame class | Has tab router? |
|---|---|---|
| Add Media (post/page editor) | `wp.media.view.MediaFrame.Post` | ✅ Yes |
| Featured Image meta box | `wp.media.view.MediaFrame.Select` | ✅ Yes |
| Customizer image controls | `wp.media.view.MediaFrame.Select` | ✅ Yes |
| Custom `wp.media({ frame: 'post' })` calls | `wp.media.view.MediaFrame.Post` | ✅ Yes |

`MediaFrame.Post` extends `MediaFrame.Select` in WP core — but they are **separate constructors**. Extending only `Post` does not affect `Select` frames. Both must be patched independently.

### Extension pattern

Both frame classes are extended via prototype swap at script-load time:

```js
var OriginalPost = wp.media.view.MediaFrame.Post;
wp.media.view.MediaFrame.Post = OriginalPost.extend({
    initialize() { /* add FolderBrowserState */ },
    bindHandlers() { /* listen for content:create:mm-folder-browser */ },
    browseRouter( routerView ) { /* add 'Media Folders' tab */ },
    mmCreateFolderContent( contentRegion ) { /* mount FolderBrowserView */ },
});
// Identical pattern applied to wp.media.view.MediaFrame.Select
```

The `Select` extension includes a guard (`states.get('mm-folder-browser')`) to prevent double-adding the state if both paths were ever hit by the same frame.

### Selection → action flow

When the user selects a file and clicks **Select**:

1. `FolderBrowserView.insertSelected()` fetches the full WP attachment model via `wp.media.attachment( id ).fetch()`.
2. Calls `controller.lastState()` to retrieve the state that was active before the user switched to the Media Folders tab.
   - `'insert'` → Add Media context → WP's "Insert into post" toolbar button handles the rest.
   - `'featured-image'` → Featured Image context → WP's "Set featured image" button handles the rest.
3. Resets that state's Backbone `selection` collection to the chosen attachment.
4. Calls `controller.setState( prevState.id )` to switch back — WP's own context-aware button is now active with the correct attachment and fires natively.

This approach works universally without any per-context special-casing. It does **not** call `wp.media.editor.insert()` (which targets TinyMCE only and silently fails in all non-editor contexts).

### Why `wp.media.editor.insert()` was wrong

Early attempts used `wp.media.editor.insert( html )`. This API writes a raw HTML string into the active TinyMCE instance. When the Featured Image modal is open there is no active TinyMCE editor reference — the call either silently fails or inserts into the last-focused editor. The modal closed (giving the appearance of success) but nothing was set. Attempts to work around this added more wrapper code on a broken foundation. The correct fix was to use WP's own selection/state machinery instead.

---

## 11. Activation / Deactivation / Uninstall

### Activation (`Activator::run()`)

1. Create `{prefix}mm_files` via `dbDelta()`
2. Create `{prefix}mm_protected` via `dbDelta()`
3. Create `{prefix}mm_blocked_ips` via `dbDelta()`
4. Seed default options (`mm_items_per_page`, `mm_move_or_copy`, etc.) with `add_option()` (no-op if already set)
5. If `mm_upload_folder_name` doesn't exist: scan existing `wp_uploads` dir, import current folder structure, set `mm_upload_folder_id`
6. Schedule `mm_folder_scan` daily cron if not already scheduled
7. Store `mm_version` in options

### Deactivation (`Deactivator::run()`)

1. `wp_clear_scheduled_hook('mm_folder_scan')`
2. No data deleted

### Uninstall (`uninstall.php`)

1. `$wpdb->query("DROP TABLE IF EXISTS {prefix}mm_files")`
2. `$wpdb->query("DROP TABLE IF EXISTS {prefix}mm_protected")`
3. `$wpdb->query("DROP TABLE IF EXISTS {prefix}mm_blocked_ips")`
4. Delete all CPT posts of type `mm_folder`
5. `delete_option()` for all `mm_*` keys
6. `wp_clear_scheduled_hook('mm_folder_scan')`
7. Delete all user meta with key `mm_sort_field` and `mm_sort_direction`
