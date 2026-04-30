# Dish Events — Build Plan

**Document:** `08-build-plan.md`
**Status:** 🟡 In progress
**Last updated:** 2026-03-27
**Plugin path:** `wp-content/plugins/dish-events/`
**Depends on:** `06-prd.md`, `07-architecture.md`

---

## Standing Constraints

These rules apply to **every phase**. No exceptions without an explicit decision recorded here.

### Admin UI — WordPress Native Styles Only

The admin interface must look and feel like WordPress. No custom admin CSS frameworks, no imported fonts, no third-party icon libraries in admin.

**Use these WP core classes:**

| Pattern | WP class / element |
|---|---|
| Page wrapper | `.wrap` |
| Settings form | `.form-table`, `<th scope="row">`, `<td>` |
| Tabbed navigation | `.nav-tab-wrapper`, `.nav-tab`, `.nav-tab-active` |
| Meta boxes | `.postbox`, `.postbox-header`, `.inside` |
| List tables | `WP_List_Table`, `.wp-list-table`, `.widefat`, `.striped` |
| Buttons | `.button`, `.button-primary`, `.button-secondary`, `.button-link-delete` |
| Notices | `.notice`, `.notice-success`, `.notice-error`, `.notice-warning`, `.updated` |
| Repeater rows | Plain `<table>` or `<div>` rows — no library |
| Section headers | `<h1>`, `<h2 class="title">` |
| Screen icon | Dashicons via `'menu_icon' => 'dashicons-...'` on CPT/menu |

**Do not use:**
- Custom admin fonts (no `@import` of Google Fonts or any web font in admin CSS)
- Font Awesome, Heroicons, Phosphor, Feather, or any icon library
- Bootstrap, Tailwind, or any CSS framework in admin
- Select2, Chosen, or any custom dropdown library — if an enhanced select is genuinely needed, use the **WP-bundled** `wp_enqueue_script('select2')` only
- Flatpickr or any date picker library — use native `<input type="datetime-local">` in admin

### Icons

**Approved:** Dashicons only. WP ships them — no extra HTTP request.  
**Anything else requires explicit approval before use.**

Dashicons reference: https://developer.wordpress.org/resource/dashicons/

### Frontend JS (reminder)

No jQuery on the frontend. Vanilla JS only. (jQuery is acceptable in admin since WP core ships it, but write our own code as vanilla where reasonable.)

### Third-Party Packages — Approval Required

The following were used in the old plugin. Status for the rebuild:

| Package | Old usage | Decision |
|---|---|---|
| FullCalendar.js | Event calendar views | ✅ **Approved — carry over existing bundle** |
| phpqrcode | QR code generation | ✅ **Approved — carry over, fix include path** |
| Select2 | Dropdowns (admin + frontend) | ⚠️ **Admin: WP-bundled version only. Frontend: native `<select>` — no Select2** |
| Flatpickr | Date/time pickers | ❌ **Removed — use native `<input type="datetime-local">` in admin** |
| Chart.js | Reports charts | ✅ **Approved — admin Reports screen only** |
| jQuery Toast | Admin toast notifications | ❌ **Removed — use WP-native `.notice` pattern** |
| TCPDF | PDF generation | ❌ **Removed — HTML print view only** |
| Google Maps JS API | Venue map embed | ⚠️ **Conditionally loaded — "View Map" lazy-loads iframe only. API key from Settings.** |
| PayPal JS SDK | Checkout | ✅ **Approved — loaded only on checkout page** |
| Google Fonts (admin) | Old plugin imported fonts | ❌ **Removed** |

> **Chart.js:** Approved for the Reports admin screen only. Loaded conditionally — not enqueued on any frontend or non-Reports admin page.

---

## How This Works

Each phase has:
- A **goal** — what exists at the end of the phase
- A **file list** — every file that will be created or modified
- A **confirmation checklist** — manually verify before moving to the next phase

Phases are sequential. Do not start Phase N+1 until Phase N is confirmed ✅.

When a phase is active, a todo list is set and tracked per-file.

---

## Phase Overview

| # | Phase | Key deliverable | Status |
|---|---|---|---|
| 1 | Plugin Scaffold | Plugin activates, DB tables exist, no errors | ✅ |
| 2 | CPTs, Taxonomy & Post Statuses | All CPTs registered; format taxonomy public; permalinks work | ✅ |
| 2.5 | Class Template CPT & Admin | Template pages resolve at correct URL; format auto-derive wired | ✅ |
| 3 | Settings | Settings page saves and reads correctly | ✅ |
| 4 | Admin — Class Management | Create/edit instance via template; recurrence generates children | ✅ |
| 4.5 | Admin — Ticketing | Ticket Formats (CPT) + Types manageable; template Ticket tab wired | ✅ |
| 5 | Admin — Chef & Booking Management | Chefs editable, booking detail screen readable | ✅ |
| 6 | Data Layer | Repositories and helpers wired, no query errors | ✅ |
| 7 | Frontend Templates & Shortcodes | Shortcodes render, single-class permalinks work | ✅ |
| 8 | Calendar | FullCalendar loads, events populate from REST feed | ✅ |
| 9 | Booking & Checkout | Checkout form renders, timer runs, capacity enforced | ✅ |
| 9.5 | Corporate Bookings | Admin can record a corporate/private booking; open questions must be answered first | 🔲 |
| 9.6 | Waitlist | Customers can join a waitlist when a class is full; spot claim flow TBD | 🔲 |
| 9.7 | Format-Specific Checkout Fields | Extra checkout fields attached to a class format; owner input required | 🔲 |
| 10 | Payments — PayPal | Sandbox payment completes, booking confirmed | 🔲 |
| 11 | Notifications | All 7 email types send with correct tokens | 🔲 |
| 12 | Reports | Reports page loads, all 3 tabs render, CSV export works | ✅ |
| 12.5 | QR & iCal | QR generates on confirmation, iCal downloads correctly | 🔲 |
| 13 | Admin AJAX | All admin-side AJAX actions verified | 🔲 |
| 14 | Polish & Hardening | Accessible, secure, performant, end-to-end confirmed | 🔲 |

### Next Up

1. **Phase 11** — Notifications — 7 email types, tokens, kill switches
2. **Phase 12.5** — QR & iCal
3. **Phase 13** — Admin AJAX
4. **Phase 14** — Polish & Hardening (final E2E smoke test)
5. **Phase 10** — PayPal — deferred until PayPal account is set up; wired in last
6. **Phases 9.5, 9.6, 9.7** — deferred; waiting on owner decisions before scoping

---

## ✅ Phase 1 — Plugin Scaffold

**Completed:** 2026-03-22

**Goal:** The plugin can be activated without PHP errors. DB tables are created on activation. Constants and autoloader work. The admin shows no notices.

### Files

```
dish-events/
├── dish-events.php                    ← Plugin header, constants, autoloader, bootstrap call
├── uninstall.php                      ← Drop tables + delete options on plugin delete
├── includes/
│   └── Core/
│       ├── class-plugin.php           ← Instantiate all modules; fire dish_events_loaded
│       ├── class-loader.php           ← Collect and register all add_action/add_filter calls
│       ├── class-activator.php        ← Create DB tables (dbDelta), seed options, schedule cron
│       ├── class-deactivator.php      ← Unschedule cron; no data deletion
│       └── class-updater.php          ← Run DB migrations keyed on dish_db_version
```

### Confirmation Checklist

- [x] Plugin appears in WP admin → Plugins with correct name, description, version
- [x] Plugin activates with zero PHP errors or warnings
- [x] `dish_db_version` option exists in `wp_options` after activation
- [x] `{prefix}dish_ticket_types` table exists in DB
- [x] `{prefix}dish_checkout_fields` table exists in DB
- ~~`{prefix}dish_ticket_categories` table~~ — **removed in Phase 4.5** (concept replaced by `dish_format` CPT)
- [x] `dish_settings` option exists in `wp_options`
- [x] Deactivating the plugin produces no PHP errors
- [x] `uninstall.php` runs cleanly (test: delete plugin from WP admin, confirm tables/options removed)

### Notes

- `uninstall.php` expanded to delete CPT posts, taxonomy terms, and all `dish_` post meta — not just tables and options
- `class-activator.php`: schema does **not** create `dish_ticket_categories` — that table was dropped as part of the Phase 4.5 architecture decision (see Phase 4.5 notes)
- `class-class-format.php`: removed `DEFAULT_TERMS` seeding — the `dish_class_format` taxonomy was subsequently deprecated in Phase 4.5 and replaced by the `dish_format` CPT

---

## ✅ Phase 2 — CPTs, Taxonomy & Post Statuses

**Completed:** 2026-03-22

**Goal:** Four CPTs and the class format taxonomy are registered. `dish_class_format` is publicly queryable with `classes/` as its base slug. `dish_class` instances are not public. Admin menus show correct structure.

### Files

```
includes/
├── CPT/
│   ├── class-class-post.php           ← dish_class CPT (non-public instance) + dish_expired / dish_cancelled statuses
│   ├── class-class-template-post.php  ← dish_class_template CPT (public; nested URL via post_type_link filter + dish_format slug wired in Phase 2.5)
│   ├── class-chef-post.php            ← dish_chef CPT
│   └── class-booking-post.php         ← dish_booking CPT + dish_pending/completed/failed/refunded statuses
└── Taxonomy/
    └── class-class-format.php         ← dish_class_format taxonomy (public, rewrite: classes/); attached to both dish_class_template + dish_class for menu anchoring
```

### Confirmation Checklist

- [x] Admin sidebar shows: **Dish Events → Classes / Class Templates / Class Formats / Chefs / Bookings**
- [x] Can create a `dish_class_template` post and publish it
- [x] Can create a `dish_class` instance and publish it
- [x] Can create a `dish_chef` post and publish it
- [x] `dish_booking` does not show "Add New" in admin
- [x] `dish_class` instances do **not** have a public permalink — no frontend URL resolves
- [x] `/chef/{slug}/` resolves
- [x] Admin → Dish Events → Class Formats screen is accessible — **Note:** the `dish_class_format` taxonomy screen existed at this phase; in Phase 4.5 it was deprecated and replaced by the `dish_format` CPT list table
- [ ] `/classes/hands-on/` (`dish_format` CPT single post) resolves — established in Phase 4.5 via `dish_format` CPT; Phase 2.5 adds template listing on this page
- [ ] Custom post statuses (`dish_pending`, `dish_completed`, etc.) appear on booking edit screen — deferred (needs a booking record to test)
- [x] No `em_`, `ep_`, or `eventprime_` prefixes anywhere

### Notes

- `dish_class_format` taxonomy registered against both `dish_class_template` and `dish_class` — required for the taxonomy menu item to anchor under the `dish_class` top-level menu. **Deprecated in Phase 4.5** — replaced by `dish_format` CPT; file emptied to stub.
- `dish_class_template` currently rewrites to `class-template/{slug}/`; Phase 2.5 replaces this with `classes/{format-slug}/{slug}/` via a `post_type_link` filter using the parent `dish_format` post slug
- Format terms created manually during testing (4 formats); these are now `dish_format` CPT posts as of Phase 4.5
- 3 chefs, 2 templates (Hands On + Skills), 8 weekly German Beer Garden instances created as test data

---

## ✅ Phase 2.5 — Class Template CPT & Admin

**Completed:** 2026-03-27

**Goal:** `dish_class_template` posts can be created and edited with a meta box. Template URLs resolve at `/classes/{format-slug}/{slug}/` using the parent `dish_format` post's slug. Format is auto-derived from the selected Ticket Type on save. The `dish_format` single page lists its templates correctly.

> **Note:** Requires Phase 4.5 (Ticketing Admin) for the Ticket Type dropdown to have data. **Phase 4.5 is complete** — `dish_format` CPT posts and at least one Ticket Type exist.

### Files

```
includes/
└── Admin/
    └── class-class-template-admin.php ← Meta box (ticket type, gallery, social, theme) + list table columns

templates/
└── class-templates/
    ├── single.php                     ← Template page: canonical description + upcoming instances date picker + Book Now
    ├── card.php                       ← Partial: template card for format archive + listings
    └── upcoming.php                   ← Partial: upcoming instances list rendered on template page
```

### Meta Box Fields

| Field | Stored as |
|---|---|
| Ticket Type dropdown (required) | `dish_ticket_type_id` post meta |
| Parent Format (auto-derived from ticket type) | `dish_format_id` post meta (stores `dish_format` post ID) |
| Gallery images | `dish_gallery_ids` JSON post meta |
| Frontend template name | `dish_event_theme` post meta |

### Confirmation Checklist

- [x] Admin → Dish Events → Class Templates list table shows: title, format (auto-derived from ticket type), ticket type, status columns
- [x] Creating a template shows the meta box with all fields
- [x] Ticket Type dropdown lists active types grouped by format (`dish_format` post title)
- [x] Saving a template auto-stores the parent `dish_format` post ID as `dish_format_id` meta (derived from selected ticket type's `format_id`)
- [x] `/classes/hands-on/german-beer-garden/` resolves to `class-templates/single.php`
- [x] `/classes/hands-on/` (`dish_format` single page) lists all active templates in that format
- [x] Template title, excerpt, content, and featured image display on the single page
- [x] Upcoming instances for this template render on the template page
- [x] `dish_gallery_ids` saves and repopulates correctly
- [x] Theme can override `{theme}/dish-events/class-templates/single.php`

---

## ✅ Phase 3 — Settings

**Completed:** 2026-03-22

**Goal:** The Settings page renders all tabs. Values save and can be read back. `Settings::get()` returns the correct default when a key is unset.

### Files

```
includes/
└── Admin/
    ├── class-admin.php                ← Admin hook registrar; wires all admin modules to Loader
    └── class-settings.php             ← Settings page (tabbed WP Settings API), get/set/defaults
```

### Settings Tabs

| Tab | Key settings |
|---|---|
| General | date/time format, timezone display, currency, checkout timer |
| Venue | name, address, suburb, state, postcode, maps URL, lat/lng, API key |
| Studio | name, email, phone, website, Instagram, Facebook |
| Pages | page ID dropdowns for all 7 shortcode pages |
| Calendar | default view, available views, hide past, per-page count |
| Payments | active gateway, PayPal mode, PayPal client ID |
| Emails | from name/address, per-email enabled/subject/body/cc |
| URLs | class slug, chef slug, class format slug |
| Features | GCal link, iCal download, QR code, guest checkout, account creation |

### Confirmation Checklist

- [x] Admin → Dish Events → Settings loads with no PHP errors
- [x] All 9 tabs render and switch without page reload
- [x] Save a value on each tab; confirm it persists on reload
- [x] `Settings::get('currency', 'AUD')` returns correct fallback — logic verified in code
- [x] `Settings::get('currency')` returns saved value after setting it
- [x] No raw `$_POST` values output — all values escaped on render
- [x] Changing URL slugs triggers rewrite flush — confirmed (chef → chefs → chef, both URLs resolved)

### Notes

- `default_settings()` keys fully aligned with settings page field keys (was mismatched on email keys, calendar view values, and several stale keys)
- Email keys standardised to `email_{template}_{field}` pattern throughout defaults, sanitizer, and page renderer
- `booking_close_offset` removed (booking always closes at `dish_start_datetime` per architecture)
- Calendar view values corrected from FullCalendar internal keys (`dayGridMonth`) to settings page simple keys (`month`, `week`, etc.)
- `gateway_order`, `disable_customer_emails`, `disable_admin_emails`, `register_subject`, `reset_password_subject` removed — stale or out of scope
- Currency fields (currency, currency_symbol, currency_position) added to General tab — were in defaults but had no UI
- Stale `dish_settings` DB record wiped and reseeded from corrected defaults

---

## ✅ Phase 4 — Admin: Class Management

**Completed:** 2026-03-22

**Goal:** Class instances can be created by selecting a template. Date/time, chef, content override, and booking opens are editable. Recurrence generates correct child instances.

### Files

```
includes/
├── Admin/
│   ├── class-class-metabox.php        ← Tabbed meta box: Date/Time, Tickets, Chefs, Details, Checkout, Social, Settings
│   └── class-class-columns.php        ← List table columns, sortable date, format/status filters, duplicate bulk action
├── Recurrence/
│   └── class-recurrence-manager.php   ← generate(), update_series(), delete_series()
assets/
└── js/
    └── dish-admin.js                  ← Tab switching, recurrence UI show/hide, date pickers, ticket repeater
```

### Meta Box Tabs

| Tab | Fields |
|---|---|
| Date & Time | start datetime, end datetime, recurrence type/interval/days/ends/end_date |
| Template | Template dropdown (dish_class_template posts); read-only summary card (format, ticket type, price, capacity) |
| Chefs | multi-select of dish_chef posts (required per instance) |
| Content Override | Optional notes for this specific instance (e.g. weekly menu change); blank = display template content |
| Details | class type (public/corporate), min/max attendees, booking opens override |
| Checkout | per-instance checkout field overrides |
| Settings | featured flag, external booking URL, show QR flag |

### Confirmation Checklist

- [x] Class edit screen shows tabbed meta box with all tabs
- [x] Tab switching works (vanilla JS, no jQuery)
- [x] Date/time fields save and repopulate correctly (stored as UTC epoch)
- [x] Chef multi-select lists all `dish_chef` posts
- [x] Setting recurrence to "weekly" and saving generates correct child posts
- [x] Child posts have `dish_recurrence_parent_id` meta set
- [x] Parent post `dish_recurrence` JSON includes `child_ids` array
- [x] Classes list table shows: date, chef(s), format, bookings, status columns
- [x] Date column is sortable
- [x] Format dropdown filter works
- [x] Duplicate bulk action creates a copy without bookings
- [x] **Template tab** — completed in Phase 4 after Phase 2.5 unblocked it
  - [x] Template dropdown lists all published `dish_class_template` posts
  - [x] Selecting a template shows a read-only summary card (format, ticket type, price, capacity)
  - [x] `dish_template_id` saves correctly as integer post meta on the instance
  - [x] `dish_booking_opens` saves correctly as UTC epoch post meta (nullable — leave blank to use ticket type rule)

---

## ✅ Phase 4.5 — Admin: Ticketing

**Completed:** 2026-03-23

**Goal:** Ticket Formats are managed as `dish_format` CPT posts with full editorial support. Ticket Types are managed via a `WP_List_Table`-based admin screen linked to formats. A class's Tickets tab can then select from existing types (completing the deferred item from Phase 4).

> **Architecture decision:** The original plan called for a `dish_ticket_categories` DB table synced with the `dish_class_format` taxonomy. During implementation this was replaced with a single `dish_format` CPT — a full WordPress post type that provides a proper landing page (`/classes/hands-on/`) with title, block editor, excerpt, featured image, and SEO, capabilities that taxonomy terms lack. Consequences:
> - `dish_ticket_categories` table was **dropped** — never exists in the final schema
> - `dish_class_format` taxonomy was **deprecated** — `class-class-format.php` emptied to a stub
> - `dish_ticket_types.format_id` stores the `dish_format` **post ID** (column was `category_id` → `format_term_id` → `format_id`)
> - No custom Category admin screen — WP's native CPT screens handle `dish_format` Add/Edit
> - `class-ticket-category-admin.php` removed from `class-admin.php` wiring (file kept on disk, instantiated nowhere)

### Files

```
includes/
├── CPT/
│   └── class-format-post.php           ← Registers dish_format public CPT (title, editor, excerpt, thumbnail, revisions)
└── Admin/
    ├── class-ticket-type-admin.php     ← WP_List_Table + add/edit form for dish_ticket_types; handle_request() on admin_init
    └── class-format-columns.php        ← "Ticket Types" count column on dish_format CPT list table
```

### dish_format CPT

| Property | Value |
|---|---|
| Post type | `dish_format` |
| Public | Yes |
| URL | `/{class_format_slug}/{slug}/` — e.g. `/classes/hands-on/` |
| Supports | title, editor, excerpt, thumbnail, revisions |
| Admin menu | Under Dish Events (beside Class Templates) |
| `has_archive` | `false` — single post IS the format landing page |

### DB Schema Change

`dish_ticket_types` column evolution:
- Original plan: `category_id int` → reference `dish_ticket_categories.id`
- Phase 4.5 final: `format_id bigint(20) NOT NULL DEFAULT 0` → stores `dish_format` post ID

### Field Reference

**Ticket Type form fields:** name, format (dropdown of published `dish_format` posts), price (cents), sale price (cents), capacity, show_remaining (toggle), min_per_booking, per_ticket_fees (repeater: label + amount), per_booking_fees (repeater: label + amount), booking_starts mode (immediate / days_before), show_booking_dates (toggle), is_active

### Confirmation Checklist

- [x] Dish Events → Formats screen accessible; can create a `dish_format` post (e.g. "Hands On")
- [x] `dish_format` CPT single post resolves at `/{class_format_slug}/{slug}/`
- [x] `dish_format` list table shows a "Ticket Types" count column
- [x] Dish Events → Ticketing → Ticket Types loads without PHP errors
- [x] Ticket Types list table shows: name, format, price, sale price, capacity, active columns
- [x] "Add Ticket Type" saves all fields correctly; record appears in list table
- [x] Format dropdown lists published `dish_format` posts; `format_id` stores the post ID
- [ ] Price and sale price stored as integers (cents), displayed formatted (e.g. `$45.00`)
- [ ] Editing an existing ticket type repopulates all fields correctly
- [ ] per_ticket_fees repeater: add a row, save, repopulate — JSON stored correctly
- [ ] per_booking_fees repeater: same
- [ ] booking_starts mode switching: both modes save correct JSON (`immediate` and `days_before`)
- [ ] Soft delete (is_active = 0) removes type from active dropdowns but preserves DB row
- [ ] **Template ticket type dropdown (Phase 2.5 item)**
  - [ ] Ticket Type dropdown on template meta box lists active types grouped by format
  - [ ] Selecting a type displays a read-only summary card (price, capacity, fees, booking window)
  - [ ] `dish_ticket_type_id` saves as integer post meta on the **template** (not the instance)
  - [ ] Saving the template stores the parent `dish_format` post ID as `dish_format_id` meta (for URL generation)
  - [ ] Repopulates correctly on template edit screen reload

---

## Phase 5 — Admin: Chef & Booking Management ✅

**Goal:** Chef profiles can be created and edited. Booking detail screen is read-only with correct meta boxes. Bookings list table shows correct columns and filters.

### Files

```
includes/
└── Admin/
    ├── class-chef-meta-box.php        ← role, website, Instagram, LinkedIn, TikTok, gallery
    ├── class-booking-meta-box.php     ← read-only: general info, tickets, attendees, checkout fields, notes, transaction log, actions
    └── class-booking-columns.php      ← booking ID, class, customer, ticket, total, status, date; status filter
```

### Chef Meta Box Fields

| Field | Meta key |
|---|---|
| Role | `dish_chef_role` |
| Website | `dish_chef_website` |
| Instagram | `dish_chef_instagram` |
| LinkedIn | `dish_chef_linkedin` |
| TikTok | `dish_chef_tiktok` |
| Phone | `dish_customer_phone` |
| Gallery | `dish_chef_gallery_ids` (JSON array of attachment IDs) |

### Notes

- Email and phone fields removed from chef — contact handled offline
- Chef checkboxes moved from dedicated "Chefs" tab into the Details tab of the Class Settings meta box (UI cleanup)
- Booking status transitions expanded: any status can reset back to Pending; recovery transitions added for posts that accidentally land on a native WP status (publish/pending)
- `pre_get_posts` "All" query uses `get_post_stati()` dynamically — catches any status a booking lands on
- Native WP view links (Published, Pending, Draft etc.) stripped from bookings list table header via `views_edit-dish_booking`
- Gallery JS wrapped in `DOMContentLoaded` (not IIFE) — `wp.media` not available until WP footer scripts load
- `enqueue_assets` uses `get_current_screen()` — `global $post` unreliable at `admin_enqueue_scripts` hook time

### Confirmation Checklist

- [x] Chef edit screen shows meta box with all fields
- [x] Chef meta saves and repopulates
- [x] Chef gallery Add Images works
- [x] Booking edit screen shows three meta boxes: Booking Details (read-only), Booking Actions (sidebar), Internal Notes
- [x] Booking status can be changed via the Actions meta box (complete / cancel / refund / reset to pending) — transitions scoped by current status
- [x] Internal note can be added; notes list in reverse chronological order
- [x] Bookings list table shows correct columns: Booking ID, Class, Customer, Ticket, Total, Status, Date Booked
- [x] Status filter dropdown works
- [x] Status badge colours render correctly per status
- [x] All bookings appear under "All" filter regardless of what status they hold

---

## ✅ Phase 6 — Data Layer

**Completed:** 2026-03-27

**Goal:** All 5 repositories and 2 helpers are wired and return correct data. `$wpdb` queries use prepared statements. No business logic in repositories.

### Files

```
includes/
├── Data/
│   ├── class-class-repository.php              ← get, query, get_upcoming, get_by_template, get_chef_ids, get_booked_count
│   ├── class-class-template-repository.php     ← get, get_active, get_by_format, get_upcoming_instances, get_ticket_type
│   ├── class-booking-repository.php            ← get, get_for_class, get_for_customer, create, update_status, add_note, export_csv
│   ├── class-chef-repository.php           ← get, query, get_for_class
    ├── class-ticket-type-repository.php    ← get, get_active, get_by_format, save, delete
│   └── class-checkout-field-repo.php       ← get_active, save, delete
└── Helpers/
    ├── class-date-helper.php          ← UTC↔site-tz conversion, format(), to_display(), from_input()
    └── class-money-helper.php         ← cents_to_display(), display_to_cents(), format_price()
```

### Confirmation Checklist

- [x] `ClassRepository::get_upcoming(5)` returns 5 upcoming instances ordered by start date
- [x] `ClassTemplateRepository::get_by_format($format_id)` returns active templates for that `dish_format` post ID
- [x] `ClassTemplateRepository::get_upcoming_instances($template_id, 5)` returns the next 5 instances
- [x] `TicketTypeRepository::get_active()` returns rows from `dish_ticket_types`
- [x] `CheckoutFieldRepository::get_active()` returns active rows from `dish_checkout_fields`
- [x] `DateHelper::to_display($epoch)` respects site timezone setting
- [x] `MoneyHelper::cents_to_display(4500)` returns `$45.00` (respects currency settings)
- [x] `MoneyHelper::display_to_cents('45.00')` returns `4500`
- [x] All `$wpdb->prepare()` calls — no raw interpolation in queries
- [x] No `WP_Query` in `TicketTypeRepository` or `CheckoutFieldRepository` (direct `$wpdb`)

---

## Phase 7 — Frontend Templates & Shortcodes

**Goal:** All shortcodes render without errors. Single class and chef permalinks load the plugin templates. The theme can override any template.

### Files

```
includes/
└── Frontend/
    ├── class-frontend.php             ← Frontend hook registrar
    ├── class-shortcodes.php           ← Register all [dish_*] shortcodes
    ├── class-class-view.php           ← render_archive(), render_single(), get_template()
    ├── class-chef-view.php            ← render_archive(), render_single()
    └── class-assets.php               ← Conditional enqueue; page_has_shortcode() check

templates/
├── classes/
│   ├── archive.php                    ← [dish_classes] list/grid view
│   ├── single.php                     ← [dish_class] + single-dish_class.php
│   └── card.php                       ← Partial: class card
├── chefs/
│   ├── archive.php                    ← [dish_chefs] listing
│   ├── single.php                     ← single-dish_chef.php
│   └── card.php                       ← Partial: chef card
└── account/
    ├── login.php                      ← [dish_login]
    ├── register.php                   ← [dish_register]
    └── profile.php                    ← [dish_profile]

assets/
└── css/
    ├── dish-events.scss               ← Frontend source
    └── dish-events.css                ← Compiled output (Live Sass Compiler)
```

### Confirmation Checklist

- [x] `[dish_classes]` on a page renders a list of classes without PHP errors
- [x] `[dish_chefs]` renders chef cards
- [x] `/class/{slug}/` loads `templates/classes/single.php`
- [x] `/chef/{slug}/` loads `templates/chefs/single.php`
- [x] Template override: placing `{theme}/dish-events/classes/card.php` uses the theme file
- [x] `dish-events.css` is enqueued only on pages with a `[dish_*]` shortcode or a single CPT post
- [x] `dish-events.css` is NOT enqueued on unrelated pages
- [x] `[dish_login]` renders a login form
- [x] `[dish_register]` renders a registration form
- [x] No jQuery dependencies in any enqueued frontend script

---

## ✅ Phase 8 — Calendar

**Completed:** 2026-03-24

**Goal:** FullCalendar renders on `[dish_classes]` with a view toggle. The REST endpoint returns correctly structured class data. Calendar populates on page load and updates on date navigation. Post-completion enhancements added: time-slot constraints, hover popover, spots label, and direct booking URL wiring.

### Files

```
includes/
├── Frontend/
│   └── class-calendar.php             ← FullCalendar init, wp_localize_script for dishCalendar config
└── REST/
    └── class-classes-endpoint.php     ← GET /wp-json/dish/v1/classes?start=&end=

templates/
└── classes/
    └── calendar.php                   ← FullCalendar container + view toggle buttons

assets/
├── js/
│   ├── dish-calendar.js               ← FullCalendar init, view switching, filter bar (vanilla JS)
│   └── dish-calendar.min.js
└── vendor/
    └── fullcalendar/                  ← Existing FullCalendar bundle from EventPrime
```

### Confirmation Checklist

- [x] `[dish_classes]` with `view="calendar"` attr renders FullCalendar without JS errors
- [x] `GET /wp-json/dish/v1/classes?start=2026-04-01&end=2026-04-30` returns valid JSON array
- [x] Each event in the response includes `id`, `title`, `start`, `end`, `url`, `format.color`, `spots_remaining`
- [x] Classes appear as events on the calendar
- [x] Month/week/day/list view toggle buttons work
- [x] Navigating to a new month fires a new REST request (not cached stale data)
- [x] Clicking an event navigates to the class detail page
- [x] REST response is cached (`wp_cache_set`); second identical request does not hit DB
- [x] Format filter (by class format) narrows calendar events correctly
- [x] No jQuery used in `dish-calendar.js`
- [x] Classes with `dish_is_private = true` display as **"Private Event"** on the calendar (title suppressed, label overridden); event is non-clickable / links to nothing

#### Post-Completion Enhancements

- [x] `slotMinTime`/`slotMaxTime` constrain calendar display to **2:00 pm – 11:00 pm** (eliminates dead morning/midnight rows)
- [x] Calendar events link to `{template_permalink}?class_id=N` — clicking opens the template single page with that specific instance pre-selected
- [x] Hover **popover singleton** — shows class title, date/time, price, spots remaining; dismisses on outside click; only one open at a time
- [x] `spots_left_threshold` admin setting (Settings → Calendar) controls the threshold below which the "X spots left" warning label appears on events
- [x] `dish-event__spots-label` span injected into FullCalendar event HTML when `spots_remaining ≤ spots_left_threshold`
- [x] `single.php` — `.dish-instance-panel` block renders when `?class_id=N` is in the URL; shows date, time, spots remaining, Book This Class CTA (or Sold Out state)
- [x] Card "Book Now" links to `{booking_page}?class_id=N`

### Phase 8 Notes

- `slotMinTime`/`slotMaxTime` set in `dish-calendar.js` FullCalendar init options
- Popover appended to `document.body`; positioned via `getBoundingClientRect` + scroll offset; z-index above calendar; one singleton reused across all events
- `spots_left_threshold` stored in `dish_settings`; returned in each REST event payload so the JS can compare without a second request
- Instance panel uses `ClassRepository::get_booked_count()` against the template's ticket type capacity for a live spot count

---

## ✅ Phase 9 — Booking & Checkout

**Completed:** 2026-03-25

**Goal:** The full checkout flow works end-to-end up to (but not including) payment. Capacity is enforced. The timer runs. Guest checkout works. Account creation checkbox reveals extra fields.

### Files

```
includes/
├── Booking/
│   ├── class-booking-manager.php      ← create(), confirm(), cancel(), get_remaining_capacity()
│   ├── class-capacity-manager.php     ← reserve(), release(), get_available()
│   └── class-timer.php                ← start(), get_expiry(), expire(), cron cleanup
├── Frontend/
│   └── class-booking-view.php         ← render_checkout(), render_details()
└── Ajax/
    └── class-public-ajax.php          ← Handlers: save_booking, update_ticket_quantity, check_capacity,
                                          validate_booking_fields, booking_timer_expire, cancel_booking_process,
                                          login, register

templates/
└── booking/
    ├── checkout.php                   ← Full checkout form
    ├── timer.php                      ← Countdown timer bar partial
    ├── confirmation.php               ← Inline success state (pre-redirect)
    └── details.php                    ← [dish_booking_details] page

assets/
└── js/
    ├── dish-booking.js                ← Qty stepper, timer, account toggle, form validation, PayPal placeholder
    └── dish-booking.min.js
```

### Confirmation Checklist

- [x] `[dish_booking]?class_id=X` renders the checkout form for class X
- [x] Ticket quantity stepper respects capacity from the class's assigned ticket type
- [x] Countdown timer starts on form load and counts down in real time
- [x] On timer expiry, AJAX `dish_booking_timer_expire` fires and capacity is released
- [x] Submitting the form with missing required fields shows inline validation errors
- [x] Guest checkout: form submits with name, email, phone only (no WP account)
- [x] "Create an account?" checkbox is unchecked by default
- [x] Checking "Create an account?" reveals username + password fields smoothly
- [x] `dish_booking` post is created with status `dish_pending` when checkout begins
- [x] `dish_booking` post exists in WP admin → Bookings after partial checkout
- [x] If class is at capacity, "Book Now" button is replaced with "Sold Out" state
- [x] If class has `dish_is_private = true`, "Book Now" is replaced with a non-bookable state (no checkout renders, no timer starts); class is effectively invisible to public booking regardless of capacity
- [x] `[dish_booking_details]?booking_id=X` renders booking summary
- [x] No jQuery in `dish-booking.js`

### Phase 9 Notes

- **BFCache bug fixed:** `pagehide` beacon was firing when the browser entered BFCache (`event.persisted = true`), deleting the live server-side session while the page was merely frozen in cache. Fix: skip beacon when `e.persisted === true`; new `pageshow` listener calls `restartTimer()` when `e.persisted === true` to resume the frozen `setInterval` on restore.
- Checkout CSS appended to `dish-events.css` (~469 lines): two-column Grid layout (`.dish-checkout__layout`), form inputs/selects/textareas, timer bar (`.dish-checkout__timer`), payment section, submit button, responsive breakpoints at 900px and 640px.
- REST endpoint URL corrected: `?class_id=N` query arg was missing from calendar event and card "Book Now" links.

---

## Phase 9.5 — Corporate Bookings (STUB — not yet scoped)

> **Status:** Deferred. Scenario documented; implementation requires answers to open questions before work begins.

**Background:** Dish regularly takes corporate / private group bookings by phone. The owner flips a public class to "private" (via `dish_is_private` — done in Phase 5/9) and then needs to record and manage the corporate booking in the system. Currently this is handled entirely offline.

### Open Questions (must be answered before this phase starts)

1. **Visibility** — confirmed: private classes stay visible on the calendar as "Private Event" (non-bookable). No further change needed there.
2. **Admin booking creation** — when owner agrees the phone booking, do they create the booking record manually in WP admin, or does the corporate client receive a private checkout link to fill in their own details?
3. **Payment** — PayPal on-site, offline invoice (bank transfer / Xero), or both depending on client?
4. **Headcount** — always full-class takeover, or can corporate book a partial block (e.g. 12 of 20 seats)?
5. **Recurring series** — if the class is recurring, does the corporate takeover affect only the one occurrence or the whole series?
6. **Frontend enquiry form** — is there appetite to build a "Corporate Enquiry" form on the website that feeds into the admin, rather than always starting from a phone call?

### Likely Scope (once questions answered)

- Admin: "Create Corporate Booking" flow — assign class, set headcount, set payment method, generate booking record with `dish_completed` or `dish_pending` status
- Optional: private checkout URL the corporate client can use to self-serve their details
- Optional: frontend corporate enquiry form (`[dish_corporate_enquiry]` shortcode) that creates a draft booking and notifies the owner
- Notification: tailored email template for corporate confirmation (different from public booking template)

### Files (TBD)

```
includes/
└── Booking/
    └── class-corporate-booking-manager.php   ← TBD
templates/
└── booking/
    └── corporate-enquiry.php                 ← TBD (optional frontend form)
```

---

## Phase 9.6 — Waitlist (STUB — requires owner input before scoping)

> **Status:** Deferred. Complex feature with multiple decision points that must be answered by the studio owner before any implementation begins.

**Background:** When a class reaches capacity, customers currently have no way to express interest or be notified if a spot opens. Waitlist functionality would capture that demand and automate (or semi-automate) spot offers when cancellations occur.

### Open Questions for the Owner

1. **Trigger** — does the waitlist open automatically the moment a class hits capacity, or can the owner also manually open it early (e.g. "this class is nearly full, start collecting interest now")?

2. **Spot claim method** — when a cancellation frees a spot, does the system:
   - **(A) Auto-book** the next person on the list and charge them immediately, or
   - **(B) Notify** them by email with a time-limited link to complete checkout themselves?
   Option B is strongly recommended (no surprise charges, no payment details stored on waitlist), but requires a claim window and expiry logic.

3. **Claim window** — if option B, how long does the first person on the list have to claim their spot before it passes to the next person? (e.g. 24 hours, 48 hours?)

4. **Simultaneous cancellations** — if multiple spots open at once (e.g. a group cancels), do the top N waitlist entries all get notified at once, or does it cascade one at a time?

5. **Admin visibility** — does the owner need to view and manage the waitlist per class? (see the queue, remove entries, manually bump someone up the order?)

6. **Owner override** — can the owner manually offer a spot to a specific person outside of queue order?

7. **Frontend join flow** — when a class is full, "Book Now" becomes "Join Waitlist". Does joining require payment details upfront, or just name + email (+ phone)?

8. **Logged-in users** — if the customer already has a WP account, should their details auto-populate when joining the waitlist?

### Likely Scope (once questions answered)

- DB: `dish_waitlist` table — `id`, `class_id`, `name`, `email`, `phone`, `user_id` (nullable), `position`, `status` (waiting/notified/claimed/expired), `notified_at`, `expires_at`, `created_at`
- Admin: waitlist panel on booking edit screen — view queue, remove entries
- Frontend: "Join Waitlist" form replaces "Book Now" when `spots_remaining = 0`
- Cron: spot-offer expiry job — when claim window passes, advance queue and notify next person
- Notifications: two new email types — "Spot Available" (to waitlist customer) + "Waitlist Joined" (confirmation)
- Phase 9 dependency: capacity management in `CapacityManager` needs a hook point for waitlist advancement on cancellation

### Files (TBD)

```
includes/
├── Booking/
│   └── class-waitlist-manager.php     ← join(), offer_next(), claim(), expire(), get_queue()
└── Ajax/
    └── class-public-ajax.php          ← add handler: join_waitlist
templates/
└── booking/
    └── waitlist.php                   ← "Join Waitlist" form partial
```

---

## Phase 9.7 — Format-Specific Checkout Fields (STUB — requires owner input)

> **Status:** Deferred. Known requirement, architecture decision needed before implementation.

**Background:** Certain class formats need checkout questions that don't apply globally. Known example: Skills format classes ask *"Are you scared of knives?"*. Global fields (dietary restrictions, first time at Dish) are handled separately via the standard checkout field manager.

### Open Questions for the Owner

1. **Where should format fields be managed?** Three options on the table:
   - On the **`dish_format` post** edit screen — fields travel with the format (e.g. every Skills class automatically gets the knives question)
   - On the **class template** — each template defines its own extra fields on top of globals (more granular, more admin work)
   - On the **ticket type** — fields travel with the ticket regardless of template

2. **Stacking behaviour** — do format-specific fields appear *in addition to* global fields, or can a format suppress a global one? (e.g. "this corporate format doesn't need the dietary question")

3. **Full field inventory** — what are all the format-specific fields currently asked? One confirmed: Skills → "Are you scared of knives?" What else?

4. **Field types needed** — text, textarea, select (dropdown), checkbox, radio? Or text-only for now?

### Likely Scope (once questions answered)

- Format-specific fields most likely live on the `dish_format` post meta (a repeater, same pattern as ticket type fees) — this keeps field management close to format definition and avoids duplicating it per template
- Checkout form merges: global fields first, then format-specific fields for the class's assigned format
- Booking meta stores all submitted fields together under `dish_checkout_fields` JSON (no schema change needed)
- Admin booking detail screen already reads `dish_checkout_fields` generically — no change needed there

### Files (TBD)

```
includes/
└── Admin/
    └── class-format-checkout-fields.php   ← Repeater meta box on dish_format edit screen (TBD)
```

---

## Phase 10 — Payments: PayPal

**Goal:** A guest can complete a PayPal sandbox payment. The booking status updates to `dish_completed`. The confirmation email sends. The user is redirected to the booking details page.

### Files

```
includes/
├── Payments/
│   ├── interface-gateway.php          ← GatewayInterface contract
│   ├── class-gateway-registry.php     ← dish_payment_gateways filter, get_active(), get_all()
│   └── class-paypal-gateway.php       ← enqueue_scripts(), render_button(), handle_confirm()
└── Ajax/
    └── class-public-ajax.php          ← Add handlers: paypal_create_order, paypal_confirm
```

### Confirmation Checklist

- [ ] PayPal JS SDK script loads only on the booking/checkout page
- [ ] PayPal sandbox buttons render in the checkout form
- [ ] Clicking Pay triggers `dish_paypal_create_order` AJAX — a PayPal order ID is returned
- [ ] Approving payment in sandbox triggers `dish_paypal_confirm` AJAX — order captured server-side
- [ ] Booking post status changes from `dish_pending` → `dish_completed`
- [ ] `dish_transaction_id` meta is saved on the booking post
- [ ] `dish_transaction_log` meta records the PayPal capture event
- [ ] Customer is redirected to `booking_details_page?booking_id=X`
- [ ] Cancelling the PayPal popup fires `dish_cancel_booking_process`; booking remains `dish_pending`
- [ ] PayPal client secret is never output to the frontend (server-side only)
- [ ] `paypal_mode = sandbox` and `paypal_mode = live` both resolve to correct SDK URL

---

## Phase 11 — Notifications

**Goal:** All wired email types send with correct tokens when their trigger fires. HTML templates render with studio branding. Kill switches work.

> **Email types wired in Phase 11** (match Settings → Emails exactly):
> - `email_booking_confirmation` — customer; fires when booking → `dish_completed`
> - `email_booking_cancelled` — customer; fires when booking → `dish_cancelled`
> - `email_admin_new_booking` — studio copy; fires on `dish_booking_created` (pre-payment, immediate)
> - `email_admin_cancellation` — studio copy; fires when booking → `dish_cancelled`
>
> **Deferred** (template files exist, dispatch not wired yet):
> - `email_booking_reminder` — cron job (Phase 13)
> - `email_payment_receipt` — PayPal confirm callback (Phase 10)
> - `email_waitlist_available` — WaitlistManager (Phase 9.6)

### Files

```
includes/
└── Notifications/
    ├── class-notification-service.php  ← register_hooks(), on_booking_created(),
    │                                      on_status_transition(), dispatch(),
    │                                      build_tokens(), send()
    ├── class-email-template.php        ← replace_tokens(), wrap(), get_default_body()
    └── Templates/
        ├── booking-confirmed.php       ← default body: customer confirmation
        ├── booking-cancelled.php       ← default body: customer cancellation
        ├── booking-reminder.php        ← default body: reminder (dispatch in Phase 13)
        ├── admin-new-booking.php       ← default body: studio new booking notification
        └── admin-cancellation.php      ← default body: studio cancellation notification
```

### Token Reference

All tokens use `{{double_braces}}` format. Available in every email:

| Token | Source |
|---|---|
| `{{booking_id}}` | Booking post ID |
| `{{customer_name}}` | `dish_customer_name` meta |
| `{{customer_email}}` | `dish_customer_email` meta |
| `{{customer_phone}}` | `dish_customer_phone` meta |
| `{{class_title}}` | Class post title |
| `{{class_date}}` | Formatted start date (site timezone) |
| `{{class_time}}` | Formatted start time (site timezone) |
| `{{class_location}}` | `venue_name` setting |
| `{{ticket_type}}` | Ticket type name |
| `{{quantity}}` | `dish_ticket_qty` meta |
| `{{amount}}` | `dish_ticket_total_cents` via `MoneyHelper` |
| `{{booking_details_url}}` | `booking_details_page` setting + `?booking_id=N` |
| `{{studio_name}}` | `studio_name` setting |
| `{{studio_email}}` | `studio_email` setting |
| `{{studio_phone}}` | `studio_phone` setting |

### Dispatch Points

| Hook | Email(s) sent |
|---|---|
| `dish_booking_created` | `email_admin_new_booking` |
| `transition_post_status: * → dish_completed` | `email_booking_confirmation` |
| `transition_post_status: * → dish_cancelled` | `email_booking_cancelled`, `email_admin_cancellation` |

### Confirmation Checklist

- [ ] Settings → Emails: all sender fields (From name, From address, Admin notify) save correctly
- [ ] Admin manually marks booking `dish_completed` → customer receives confirmation email
- [ ] Admin marks `dish_completed` → studio receives **no** duplicate (admin copy only fires on `dish_booking_created`)
- [ ] New booking checkout submitted → studio receives `email_admin_new_booking`
- [ ] Admin cancels booking → customer receives cancellation email
- [ ] Admin cancels booking → studio receives admin cancellation email
- [ ] Disabling an email in Settings → Emails → Enabled → that email does not send
- [ ] Setting a custom Subject overrides the default subject
- [ ] Setting a custom Body overrides the built-in template
- [ ] Leaving Body blank falls back to built-in template (no raw `{{tokens}}` in sent email)
- [ ] CC field: setting a CC address results in a Cc: header in the sent email
- [ ] `dish_notification_should_send` filter returning false suppresses the email
- [ ] No raw `{{...}}` tokens appear in the sent email subject or body

---

## ✅ Phase 12 — Reports

**Completed:** 2026-03-26

**Goal:** The admin Reports page loads booking and revenue data across three tabs with CSV export.

### Files

```
includes/
├── Admin/
│   └── class-reports.php              ← Bookings, Revenue, Attendees tabs; CSV export
└── Data/
    └── class-reports-repository.php   ← Aggregate SQL: get_summary, get_bookings_list,
                                          get_revenue_by_class, get_attendees_for_class,
                                          export_bookings_csv, export_attendees_csv
```

### Key Implementation Details

- **Menu slug:** `dish-events-reports`; submenu under `edit.php?post_type=dish_class`
- **SQL pattern:** `build_date_status_where()` calls `$wpdb->prepare()` per clause internally and returns a pre-escaped `string`. Callers interpolate `$where` directly — **never** wrap in an outer `prepare()` call. LIMIT/OFFSET cast as `(int)` inline.
- **CSV export:** GET-based, fires on `admin_init` before output; nonce `dish_reports_export`; streams via `php://output`.
- **Active booking statuses** for revenue queries: `dish_pending`, `dish_completed`, `dish_refunded`.

### Confirmation Checklist

- [x] Admin → Dish Events → Reports loads without errors
- [x] Reports → Bookings tab: 4 stat cards (total bookings, revenue, avg/day, total tickets) + paginated table + date/status/search filter
- [x] Reports → Revenue tab: per-class revenue breakdown table with grand total `<tfoot>`
- [x] Reports → Attendees tab: class selector dropdown → attendee table for selected class
- [x] "Export Bookings CSV" downloads a valid 11-column CSV
- [x] "Export Attendees CSV" downloads a valid CSV (one row per attendee, including additional attendees from JSON)
- [x] No `wpdb::prepare` notices (outer `prepare()` wrappers removed; pre-escaped SQL interpolated directly)
- [x] `MoneyHelper::cents_to_display()` used throughout — respects currency settings
- [x] Status badges use same colour palette as `BookingColumns`

---

## Phase 12.5 — QR & iCal

**Goal:** QR codes generate on the booking confirmation/details page. iCal `.ics` files download with correct event data. Google Calendar link is pre-filled.

### Files

```
includes/
└── Helpers/
    ├── class-qr-helper.php            ← generate_png(), get_data_url(); wraps phpqrcode
    └── class-ical-helper.php          ← build_vevent(), get_ical_string(), output_file()
```

### Confirmation Checklist

- [ ] QR code appears on the booking confirmation/details page (`[dish_booking_details]`)
- [ ] QR PNG can be decoded and contains the correct booking reference
- [ ] "Add to iCal" link on class detail downloads a valid `.ics` file
- [ ] `.ics` file opens correctly in macOS Calendar and Google Calendar
- [ ] "Add to Google Calendar" link opens Google Calendar pre-filled with class title, date, location
- [ ] QR and iCal are gated by their respective feature flags in Settings → Features (`qr_code`, `ical_download`)

---

## Phase 13 — Admin AJAX (Remaining)

**Goal:** All remaining admin-side AJAX handlers are wired, capability-checked, and verified.

### Files

```
includes/
└── Ajax/
    └── class-admin-ajax.php           ← Remaining handlers:
                                          booking_update_status, booking_add_note,
                                          calendar_create_class, calendar_drag_date, calendar_delete,
                                          booking_cancel (customer-facing, auth-required)
```

### Confirmation Checklist

- [ ] Admin can change booking status via AJAX (approve/cancel/refund) on booking detail screen
- [ ] Admin note saves via AJAX with no page reload
- [ ] Admin calendar: dragging a class to a new date updates `dish_start_datetime` / `dish_end_datetime`
- [ ] Admin calendar: creating a class from the calendar opens the correct new-class URL pre-filled with date
- [ ] Admin calendar: deleting a class from calendar moves it to trash
- [ ] All admin AJAX handlers reject unauthenticated requests with 403
- [ ] All admin AJAX handlers check `manage_options` or appropriate capability
- [ ] All admin AJAX handlers verify nonce (`check_ajax_referer`)

---

## Phase 14 — Polish & Hardening

**Goal:** The plugin is production-ready. Accessible, secure, performant, and fully tested end-to-end.

### Tasks

```
Security
  - Audit every $wpdb query for prepare() usage
  - Audit every template for esc_html / esc_attr / esc_url / wp_kses coverage
  - Confirm all AJAX nonces verified
  - Confirm REST endpoint has no PII leakage
  - Review dish_booking creation for race condition on capacity reserve

Accessibility
  - All form inputs have <label for=""> associations
  - Error messages use aria-live="polite" regions
  - Calendar keyboard navigation works (FullCalendar built-in + focus management)
  - Colour contrast ≥ 4.5:1 on class format colours

Performance
  - dish-events.css / .js only loads on relevant pages (verify with browser devtools on homepage)
  - REST endpoint cache confirmed working (check query count with Query Monitor)
  - No N+1 queries in class archive (verify with Query Monitor)
  - Images lazy-loaded on class/chef cards

CSS
  - dish-events.scss compiled and minified
  - dish-admin.scss compiled and minified
  - BEM naming: .dish-card, .dish-calendar, .dish-booking-form etc.
  - Mobile-first breakpoints; checkout fully functional at 320px

Final E2E smoke test
  - Create a class with recurrence → 4 children exist
  - Add two chefs, assign them to the class
  - Navigate to class on frontend → card renders, single page renders
  - Click Book Now → checkout renders, timer starts
  - Fill in guest details, complete PayPal sandbox payment
  - Booking confirmation email received
  - Admin notification email received
  - Booking appears in admin list with status dish_completed
  - Download iCal → opens in calendar app
  - Admin views Reports → booking counted in revenue
```

### Confirmation Checklist

- [ ] `wp plugin check dish-events` passes (or all flagged issues reviewed and accepted)
- [ ] Zero PHP errors/warnings in `WP_DEBUG` mode with `debug.log`
- [ ] Zero JS console errors on frontend pages
- [ ] `dish-events.css` not loaded on WordPress homepage
- [ ] Full E2E smoke test completed (see tasks above)
- [ ] All `{{tokens}}` resolved in sent emails (no raw tokens)
- [ ] HTTPS: PayPal sandbox and live both require HTTPS — confirmed on staging
- [ ] Uninstall: delete plugin from WP admin → tables dropped, options deleted

---

## Notes

- **Docs location:** All docs live in `wp-content/themes/basecamp/Docs/plugin-audit/`
- **Plugin location:** `wp-content/plugins/dish-events/`
- **No build pipeline:** Live Sass Compiler (VS Code) for `.scss → .css`; Auto-Minify for `.min.css` / `.min.js`
- **Cancellation & refund policies:** Phase 9 and Phase 10 contain stubs; full self-service cancel and auto-refund to be scoped once policies are supplied
- **Corporate enquiry form:** Deferred to Phase 9.5; standard checkout handles corporate bookings at launch

---

## Standalone Mini-Projects

These are scoped separately from the `dish-events` plugin phases. Each is a self-contained project that integrates with `dish-events` at a defined hook point rather than being built into the core plugin.

---

### 🎁 dish-giftcards (STUB — requires owner input before scoping)

> **Status:** Deferred. Major multi-layer project. All three layers below need owner answers before any implementation begins.

**Background:** Dish has been selling gift cards for years across multiple systems — old paper/plastic cards, plus 2–3 different e-card providers. The goal is to absorb all legacy cards into a single system and allow gift card redemption at checkout. New card sales via the website is also in scope but secondary.

**Integration point:** `dish-giftcards` registers itself as a payment method via the `dish_payment_gateways` filter in `dish-events`. This keeps gift card logic entirely out of the core plugin and allows it to be built and shipped independently.

#### Layer 1 — Legacy Card Absorption (import problem)

**Open questions:**
1. What formats do the existing systems export? CSV, API, manual spreadsheet, or something else?
2. Are old plastic/paper cards still being actively honoured, or is there a cutoff date after which they are void?
3. Do legacy cards carry a known balance, or are they fixed-value (e.g. "$100 card" is always worth $100 regardless of partial use)?
4. How many cards are we talking about across all systems — rough order of magnitude? (hundreds, thousands?)
5. Is there a single person at Dish who knows where all the card records currently live?

**Likely scope:**
- One-time import tool: CSV upload → `dish_gift_cards` DB table
- Deduplication and conflict resolution UI for cards that appear in multiple systems
- Normalisation: map each legacy card format to a common schema

#### Layer 2 — Checkout Redemption

**Open questions:**
6. Is a gift card a **full or partial** payment method? If the card balance is $100 and the class is $150, does the customer pay the $50 difference via PayPal?
7. Can **multiple gift cards** be applied to a single booking?
8. Do cards **expire**? If so, is there a standard expiry period?
9. **Running balance or single-use?** Can a $150 card be used for a $80 class and retain the $70 remaining balance for a future booking?
10. Does the customer redeem by entering a **card number only**, or card number + PIN/email validation?

**Likely scope:**
- Checkout step: "Do you have a gift card?" field — validates card, shows remaining balance
- Partial payment split: gift card covers up to its balance, remainder goes to PayPal
- Card balance updated on successful booking confirmation
- Booking meta records gift card number + amount applied

#### Layer 3 — New Card Sales

**Open questions:**
11. Will new gift cards be **sold through the website** going forward, or is this purely a redemption system for cards issued elsewhere (in-studio, phone, third-party)?
12. If sold online: are cards a **fixed set of denominations** (e.g. $50, $100, $150) or can the buyer enter any amount?
13. If sold online: is the card **emailed to the recipient** (e-card), or does the buyer receive it to gift physically?
14. If sold online: does the buyer pay via PayPal? (This is a separate checkout flow from booking — buying a card, not a class.)

**Likely scope (if online sales in scope):**
- `[dish_buy_giftcard]` shortcode — purchase form with denomination selector
- PayPal payment for card purchase
- Email delivery: card number + balance sent to recipient's email
- New card record created in DB on successful purchase

#### Provisional DB Schema

```sql
CREATE TABLE {prefix}dish_gift_cards (
  id            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  card_number   varchar(64)  NOT NULL,
  original_value_cents int(11) NOT NULL DEFAULT 0,
  balance_cents int(11)      NOT NULL DEFAULT 0,
  holder_name   varchar(255) DEFAULT NULL,
  holder_email  varchar(255) DEFAULT NULL,
  holder_phone  varchar(50)  DEFAULT NULL,
  source        varchar(64)  NOT NULL DEFAULT 'manual',  -- 'manual','import','online_sale'
  expires_at    datetime     DEFAULT NULL,
  status        varchar(20)  NOT NULL DEFAULT 'active',  -- 'active','redeemed','expired','void'
  created_at    datetime     NOT NULL,
  updated_at    datetime     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY card_number (card_number)
);
```

#### Provisional File Structure

```
dish-giftcards/
├── dish-giftcards.php             ← Plugin header, constants, bootstrap
├── includes/
│   ├── class-gift-card-manager.php    ← validate(), redeem(), get_balance(), create()
│   ├── class-gift-card-import.php     ← CSV import tool, deduplication
│   ├── class-gift-card-gateway.php    ← Implements dish-events GatewayInterface
│   └── class-gift-card-admin.php      ← List table, add/edit screen, import UI
└── templates/
    ├── checkout-field.php             ← Gift card input partial for dish-events checkout
    └── email-delivery.php             ← E-card email template (if online sales in scope)
```
