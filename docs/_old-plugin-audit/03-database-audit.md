# EventPrime — Database Footprint Audit

**Source plugin:** `eventprime-event-calendar-management` v4.0.9.7  
**Audit date:** 2026-03-21  
**Status:** 🟢 Complete

---

## 1. Custom Post Types

| Post Type | Public | Has Archive | Capability Type | Purpose |
|---|---|---|---|---|
| `em_event` | `true` | `false` | `em_event` | Core event records |
| `em_performer` | `true` | `false` | `em_performer` | Performer / speaker profiles |
| `em_booking` | `false` | `false` | `em_booking` | Booking records (internal) |

**Rewrite slugs** are dynamic — read from `seo_urls` setting via `ep_get_seo_page_url()`.

---

## 2. Custom Taxonomies

| Taxonomy | Attached To | Hierarchical | Public | Purpose |
|---|---|---|---|---|
| `em_event_type` | `em_event` | Yes | `false` | Event categories/types |
| `em_venue` | `em_event` | Yes | `false` | Event venues/locations |
| `em_event_organizer` | `em_event` | Yes | `false` | Event organisers |

All three taxonomies are `publicly_queryable => false` and `show_in_rest => false` (commented out). Rewrite slugs are also dynamic from settings.

---

## 3. Custom Post Statuses

| Slug | Label | Public | Used On |
|---|---|---|---|
| `emexpired` | EM Expired | `true` | `em_event` (legacy) |
| `expired` | Expired | `true` | `em_event` |
| `cancelled` | Cancelled | `false` | `em_event`, `em_booking` |
| `pending` | Pending | `false` | `em_event`, `em_booking` |
| `refunded` | Refunded | `false` | `em_booking` |
| `completed` | Completed | `false` | `em_booking` |
| `failed` | Failed | `false` | `em_booking` |

> **Note:** `emexpired` and `expired` are duplicates — both appear to serve the same purpose. Consolidate to `expired` in rebuild.

---

## 4. Custom Database Tables

All tables use `dbDelta()` for creation/upgrade.

### 4a. `{prefix}em_price_options` — Tickets

Created by activator via identifier `'TICKET'`.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint(20) AUTO_INCREMENT PK | |
| `event_id` | bigint(20) NOT NULL | FK → `wp_posts.ID` (em_event) |
| `name` | varchar(255) | Ticket name |
| `description` | longtext | |
| `start_date` | datetime | Availability window start |
| `end_date` | datetime | Availability window end |
| `price` | varchar(50) | Stored as string (allows empty) |
| `special_price` | varchar(50) | Sale price |
| `capacity` | int(11) | Max tickets available |
| `is_default` | tinyint(2) | Default ticket flag |
| `is_event_price` | tinyint(2) | Used as event-level price |
| `icon` | longtext | Icon identifier |
| `priority` | int(11) | Sort order |
| `capacity_progress_bar` | tinyint(2) | Show progress bar |
| `status` | tinyint(2) DEFAULT 1 | 1=active, 0=inactive |
| `created_at` | datetime | |
| `updated_at` | datetime | |
| `variation_color` | varchar(20) | Hex colour |
| `seat_data` | longtext | JSON (Live Seating ext.) |
| `parent_price_option_id` | int(11) DEFAULT 0 | Parent ticket ID |
| `category_id` | int(11) DEFAULT 0 | FK → ticket_categories.id |
| `additional_fees` | longtext | JSON array of fee objects |
| `allow_cancellation` | tinyint(2) DEFAULT 0 | |
| `show_remaining_tickets` | tinyint(2) DEFAULT 0 | |
| `show_ticket_booking_dates` | tinyint(2) DEFAULT 0 | |
| `min_ticket_no` | varchar(50) | Min per booking |
| `max_ticket_no` | varchar(50) | Max per booking |
| `visibility` | longtext | JSON — role-based visibility rules |
| `offers` | longtext | JSON array of offer/discount rules |
| `booking_starts` | longtext | JSON — booking window start config |
| `booking_ends` | longtext | JSON — booking window end config |
| `multiple_offers_option` | longtext | JSON |
| `multiple_offers_max_discount` | longtext | JSON |
| `ticket_template_id` | int(11) | FK to paid extension table |

**Issues:**
- `price` stored as `varchar` rather than `decimal` — problematic for maths
- No foreign key constraints
- No indexes beyond PRIMARY KEY — queries filtering by `event_id` are unindexed

---

### 4b. `{prefix}eventprime_checkout_fields` — Custom Checkout Fields

| Column | Type | Notes |
|---|---|---|
| `id` | bigint(20) AUTO_INCREMENT PK | |
| `type` | varchar(50) | Field type (text, select, checkbox, etc.) |
| `label` | varchar(255) | Display label |
| `option_data` | longtext | JSON — options for select/radio fields |
| `priority` | int(11) | Sort order |
| `status` | tinyint(2) DEFAULT 1 | |
| `created_by` | int(11) | WP user ID |
| `last_updated_by` | int(11) | WP user ID |
| `created_at` | datetime | |
| `updated_at` | datetime | |

---

### 4c. `{prefix}eventprime_ticket_categories` — Ticket Category Groups

| Column | Type | Notes |
|---|---|---|
| `id` | bigint(20) AUTO_INCREMENT PK | |
| `event_id` | int(11) NOT NULL | FK → `wp_posts.ID` |
| `parent_id` | int(11) | Self-referential parent category |
| `name` | varchar(100) | |
| `capacity` | int(100) | Total capacity for this category |
| `priority` | int(11) | Sort order |
| `status` | tinyint(2) DEFAULT 1 | |
| `created_by` | int(11) | |
| `last_updated_by` | int(11) | |
| `created_at` | datetime | |
| `updated_at` | datetime | |

**Issues:**
- No index on `event_id`
- `capacity` typed as `int(100)` — width hint meaningless, should be `int(11)`

---

## 5. `wp_options` Keys

### Settings

| Key | Contents |
|---|---|
| `em_global_settings` | Serialised array — the entire settings blob (see admin audit) |

### Plugin Management

| Key | Contents |
|---|---|
| `emagic_db_version` | Current DB schema version (float, e.g. `4.0`) |
| `event_magic_do_activation_redirect` | Flag to redirect to welcome screen after activation |
| `ep_update_revamp_version` | Flag that marks the v4 migration as complete |
| `ep_db_need_to_run_migration` | Set to `1` when a data migration is pending |
| `ep_update_event_date_time_meta` | Tracks progress of date-time meta migration |
| `ep_encrypt_secret_key` | Random 16-char key used for simple encryption |
| `ep_encrypt_secret_iv` | Random 16-char IV used for simple encryption |
| `ep_deactivate_extensions_on_migration` | List of extensions to deactivate during migration |

### Transients / Caches

No plugin-registered transients found in the codebase. The DB handler uses `wp_cache_set/get` with the group `eventprime_posts` (1-hour TTL).

---

## 6. Post Meta Keys

### `em_event` post meta

| Meta Key | Type | Description |
|---|---|---|
| `em_start_date` | string `Y-m-d` | Start date |
| `em_end_date` | string `Y-m-d` | End date |
| `em_start_time` | string `H:i` | Start time |
| `em_end_time` | string `H:i` | End time |
| `em_start_date_time` | int (timestamp) | Composite UTC timestamp (used for sorting/querying) |
| `em_end_date_time` | int (timestamp) | Composite UTC timestamp |
| `em_add_more_dates` | serialised array | Additional event dates (multi-session) |
| `em_enable_booking` | string | `'yes'`/`'no'`/`'external'` |
| `em_event_external_link` | string URL | External booking URL |
| `em_gallery_image_ids` | serialised array | Attachment IDs |
| `em_is_featured` | bool | Featured flag |
| `em_display_front` | bool | Show on frontend flag |
| `em_role` | serialised array | Roles allowed to view/book |
| `em_created_by` | int | WP user ID of creator |
| `em_status` | string | Custom status |
| `em_event_type` | int | Term ID (also stored in `term_relationships`) |
| `em_venue` | int | Term ID |
| `em_organizer` | int | Term ID |
| `em_performer` | serialised array | Performer post IDs |
| `em_social_links` | serialised array | Social sharing URLs |
| `em_event_checkout_attendee_fields` | serialised array | Per-event checkout field overrides |
| `em_recurrence_interval` | string | Recurrence rule type (daily/weekly/monthly/yearly) |
| `em_recurrence_ends` | string | How recurrence ends (`on`/`after`/`never`) |
| `em_recurrence_limit` | string | End date or count |
| Various `em_recurrence_*` | mixed | Recurrence detail fields |
| `em_event_theme` | string | Template theme name |
| `ep_result_start_date` / `ep_result_end_date` | timestamp | Results date range |
| `em_seat_data` | JSON | Seating chart (Live Seating extension) |

**Issues:**
- Date/time stored in four separate meta keys + two composite timestamp keys — six fields for one date range
- Taxonomy terms also stored in post meta (redundant with `term_relationships`)
- No meta sanitisation/validation at the model level

---

### `em_performer` post meta

| Meta Key | Type | Description |
|---|---|---|
| `em_name` | string | Performer display name |
| `em_performer_phones` | serialised array | Phone numbers |
| `em_performer_emails` | serialised array | Email addresses |
| `em_performer_websites` | serialised array | Website URLs |
| `em_performer_gallery` | serialised array | Gallery attachment IDs |
| `em_social_links` | serialised array | Same structure as event |

---

### `em_booking` post meta

The booking CPT stores all booking data as post meta. Key fields:

| Meta Key | Description |
|---|---|
| `em_event` | Event post ID |
| `em_date` | Booking creation timestamp |
| `em_booking_tickets` | Serialised tickets + quantities + prices |
| `em_booking_attendees` | Serialised per-attendee field data |
| `em_booking_fields_data` | Submitted checkout fields |
| `em_booking_total` | Total price |
| `em_payment_method` | Gateway slug |
| `em_transaction_log` | Serialised payment event log |
| `em_booking_user_id` | WP user ID (or guest identifier) |
| `em_booking_notes` | Admin notes |

---

### Term meta (taxonomy terms)

| Taxonomy | Meta Key | Description |
|---|---|---|
| `em_event_type` | `em_event_type_color` | Hex colour |
| `em_event_type` | `em_event_type_image_id` | Attachment ID |
| `em_event_type` | Various custom fields | Set via `em_create_event_type_data` |
| `em_venue` | `em_venue_address` | Street address |
| `em_venue` | `em_venue_lat` / `em_venue_lng` | Coordinates |
| `em_venue` | `em_venue_capacity` | Venue capacity |
| `em_venue` | `em_venue_image_id` | Attachment ID |
| `em_venue` | Various custom fields | Set via `em_create_event_venue_data` |
| `em_event_organizer` | `em_organizer_image_id` | Attachment ID |
| `em_event_organizer` | `em_social_links` | Social URLs |
| `em_event_organizer` | Various custom fields | Set via `em_create_event_organizer_data` |

---

## 7. Direct `$wpdb` Queries (Raw SQL)

The DB handler class (`class-eventprime-dbhandler.php`) wraps most queries but several raw queries exist:

| Location | Query Type | Purpose |
|---|---|---|
| `class-eventprime-dbhandler.php` | `SELECT COUNT(*)` | Record count checks |
| `class-eventprime-dbhandler.php` | `SELECT *` with `WHERE` | General result fetching |
| `class-ep-bookings.php` | `SELECT` joins on posts + postmeta | Booking stat aggregation |
| `class-ep-report-controller-list.php` | `SELECT` with aggregates | Report data |
| `class-eventprime-functions.php` | `SELECT` on ticket/category tables | Ticket availability checks |

All raw queries use `$wpdb->prepare()` for variable substitution. No obvious SQL injection vectors, but several queries use `%s` format for integers (should use `%d`).

---

## 8. Data Flow Summary

```
[Admin creates event]
  → save_post hook
  → ep_save_event_meta_boxes() writes post meta
  → eventprime_update_event_tickets_and_category() upserts to
      {prefix}em_price_options
      {prefix}eventprime_ticket_categories

[Frontend user views events]
  → [em_events] shortcode → Eventprime_Basic_Functions::get_events()
  → WP_Query on em_event + meta_query on em_start_date_time
  → Results enriched with ticket data (JOIN-equivalent via PHP loop)
  → Rendered via template parts in public/partials/themes/default/

[User books an event]
  → AJAX ep_save_event_booking
  → Validates capacity, offers, restrictions
  → Creates em_booking post
  → Writes booking meta
  → Triggers payment gateway (if paid)
  → On payment confirm → ep_notification_service sends emails
  → Booking status updated to 'completed'
```

---

## 9. Rebuild Recommendations

| Area | Issue | Recommendation |
|---|---|---|
| Date storage | 6 meta keys per event date range | Store as `em_start_at` / `em_end_at` UTC timestamps only |
| Ticket price | Stored as `varchar` | Use `decimal(10,2)` or store in cents as `int` |
| Missing indexes | `event_id` unindexed in ticket/category tables | Add index on activation |
| Redundant taxonomy meta | Term IDs stored in both post meta and `term_relationships` | Remove post meta duplicates; use `get_the_terms()` |
| Serialised data in post meta | Complex JSON/serialised arrays not queryable | Move structured data (attendees, tickets) to dedicated tables where queries are needed |
| `em_booking` as CPT | Limits querying; no foreign keys | Consider dedicated `bookings` table in rebuild |
| Duplicate statuses | `emexpired` + `expired` | Consolidate to `expired` |
| Options blob | Entire config in one serialised option | Fine for reads; consider grouping into sub-options for partial updates |
