# Dish Cooking Studio — Events Plugin PRD

**Document:** `06-prd.md`  
**Status:** 🟡 Draft — awaiting review  
**Last updated:** 2026-03-21

---

## 1. Project Summary

A bespoke WordPress events and booking plugin for **Dish Cooking Studio**, a cooking school offering public classes and corporate events. Built as a clean-room rebuild of EventPrime, stripped to exactly what the business needs, renamed throughout, and designed to run on the Basecamp theme without jQuery or Gutenberg.

---

## 2. Naming & Slugs

| Thing | Value |
|---|---|
| Plugin name | Dish Events |
| Plugin slug | `dish-events` |
| PHP namespace root | `Dish\Events\` |
| Text domain | `dish-events` |
| CPT: class | `dish_class` |
| CPT: chef | `dish_chef` |
| CPT: booking | `dish_booking` |
| Taxonomy: class format | `dish_class_format` |
| DB option key | `dish_settings` |
| DB table: ticket types | `{prefix}dish_ticket_types` |
| DB table: ticket categories | `{prefix}dish_ticket_categories` |
| DB table: checkout fields | `{prefix}dish_checkout_fields` |
| AJAX action prefix | `dish_` |
| REST namespace | `dish/v1` |

---

## 3. Scope

### In scope

| Area | Detail |
|---|---|
| Classes | CPT with dates, recurrence, format; references a ticket type for pricing and capacity |
| Class Formats | Admin-managed taxonomy (seasonal, renamed via admin) |
| Chefs | CPT for class instructors |
| Bookings | Guest checkout + optional account creation |
| Payment | PayPal Smart Buttons; stub interface for future gateways |
| Calendar views | All FullCalendar views (month/week/day/agenda) + card/list/grid views |
| Notifications | Booking confirmation, pending, cancellation, refund emails |
| Admin reports | Bookings list, revenue stats, attendee export |
| Custom checkout fields | Admin-configurable fields at checkout |
| iCal / Google Calendar | Add-to-calendar links on class detail |
| QR code | Per-booking QR code |
| Recurring events | Admin-created recurrence rules |
| Capacity | Per-ticket-type; class inherits capacity from its assigned ticket type |

### Explicitly out of scope

| Feature | Reason |
|---|---|
| Frontend event submission | Not needed — admin-only class creation |
| Wishlist | Dropped |
| Venue CPT / taxonomy | Single venue → settings field |
| Organizer CPT / taxonomy | Single organizer (the studio) → settings field |
| Multiple payment processors (beyond stubs) | Only PayPal at launch |
| Gutenberg blocks | No blocks on this install |
| Legacy WP widgets (all 11) | Not needed |
| User roles / chef logins | Owner-only admin |
| License system | Single-use plugin |
| Extensions marketplace | Upsell mechanism — removed entirely |
| Deactivation feedback / admin banners | Upsell mechanism — removed |
| TCPDF PDF library | Replaced with HTML print view |
| Multisite | Not applicable |

---

## 4. Data Model

### 4a. Custom Post Types

#### `dish_class` — Classes

| Property | Value |
|---|---|
| Public | `true` |
| Has archive | `false` |
| Capability type | `dish_class` |
| Supports | `title`, `editor`, `thumbnail` |
| Rewrite slug | Configurable via settings (default: `class`) |

**Post meta:**

| Key | Type | Description |
|---|---|---|
| `dish_start_at` | int (UTC timestamp) | Class start |
| `dish_end_at` | int (UTC timestamp) | Class end |
| `dish_additional_dates` | JSON array | Extra sessions (multi-session events) |
| `dish_ticket_type_id` | int | FK → `dish_ticket_types.id` |
| `dish_booking_enabled` | bool | Whether bookings are open |
| `dish_booking_opens_at` | int (UTC timestamp) | Booking window opens (per-class override) |
| `dish_booking_closes_at` | int (UTC timestamp) | Booking window closes (per-class override) |
| `dish_chef_ids` | JSON array | `dish_chef` post IDs |
| `dish_gallery_ids` | JSON array | Attachment IDs |
| `dish_recurrence` | JSON object | Recurrence rule (see 4f) |
| `dish_recurrence_parent_id` | int | Post ID of parent recurring class |
| `dish_is_featured` | bool | Featured flag |
| `dish_class_type` | string | `public` or `corporate` |
| `dish_min_attendees` | int | Minimum booking size (corporate) |
| `dish_max_attendees` | int | Maximum booking size (corporate) |
| `dish_checkout_fields` | JSON | Per-class checkout field overrides |
| `dish_event_theme` | string | Frontend template name |
| `dish_show_qr` | bool | Show QR on class detail |
| `dish_external_booking_url` | string | External booking redirect (optional) |

---

#### `dish_chef` — Chefs

| Property | Value |
|---|---|
| Public | `true` |
| Has archive | `false` |
| Capability type | `dish_chef` |
| Supports | `title`, `editor`, `thumbnail` |
| Rewrite slug | Configurable via settings (default: `chef`) |
| Menu parent | `edit.php?post_type=dish_class` |

**Post meta:**

| Key | Type | Description |
|---|---|---|
| `dish_chef_title` | string | Professional title / role |
| `dish_chef_email` | string | Contact email |
| `dish_chef_phone` | string | Contact phone |
| `dish_chef_website` | string | Personal site URL |
| `dish_chef_instagram` | string | Instagram URL |
| `dish_chef_gallery_ids` | JSON array | Gallery attachment IDs |

---

#### `dish_booking` — Bookings

| Property | Value |
|---|---|
| Public | `false` |
| Show in admin | `true` |
| Menu parent | `edit.php?post_type=dish_class` |
| Capability type | `dish_booking` |
| Create posts | `false` (admin-only, no UI "add new") |

**Post meta:**

| Key | Type | Description |
|---|---|---|
| `dish_class_id` | int | FK → `dish_class` post ID |
| `dish_class_date` | int (UTC timestamp) | Which session was booked |
| `dish_created_at` | int (UTC timestamp) | Booking timestamp |
| `dish_attendee_count` | int | Number of places booked |
| `dish_total_cents` | int | Total charged in cents |
| `dish_payment_method` | string | Gateway slug (e.g. `paypal`) |
| `dish_payment_status` | string | `pending`, `completed`, `failed` |
| `dish_transaction_id` | string | Gateway transaction reference |
| `dish_transaction_log` | JSON array | Payment event log |
| `dish_customer_name` | string | Guest customer name |
| `dish_customer_email` | string | Guest customer email |
| `dish_customer_phone` | string | Guest customer phone |
| `dish_customer_user_id` | int | WP user ID (0 = guest) |
| `dish_checkout_fields_data` | JSON | Submitted custom field values |
| `dish_attendees` | JSON array | Per-attendee data |
| `dish_notes` | string | Admin-only notes |
| `dish_coupon_code` | string | (reserved for future) |

---

### 4b. Custom Taxonomy

#### `dish_class_format` — Class Formats

| Property | Value |
|---|---|
| Attached to | `dish_class` |
| Hierarchical | `true` |
| Public | `false` |
| Publicly queryable | `false` |
| Show in REST | `false` |
| Single-value per class | `true` |

Replaces `em_event_type`. Four default formats will be seeded on activation (alpha names — renamed by admin at any time). Term meta: `dish_format_color` (hex), `dish_format_image_id` (attachment ID).

---

### 4c. Custom Post Statuses

| Slug | Label | Used on |
|---|---|---|
| `dish_expired` | Expired | `dish_class` |
| `dish_cancelled` | Cancelled | `dish_class`, `dish_booking` |
| `dish_pending` | Pending | `dish_booking` |
| `dish_completed` | Completed | `dish_booking` |
| `dish_failed` | Failed | `dish_booking` |
| `dish_refunded` | Refunded | `dish_booking` |

---

### 4d. Custom Database Tables

#### `{prefix}dish_ticket_types`

Global reusable ticket templates. Classes reference one type via `dish_ticket_type_id` post meta. Not tied to any specific class.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `category_id` | int NOT NULL DEFAULT 0 | FK → `dish_ticket_categories.id` |
| `name` | varchar(255) | Ticket type name |
| `description` | text | |
| `price_cents` | int NOT NULL DEFAULT 0 | Price in cents |
| `sale_price_cents` | int DEFAULT NULL | Sale price; NULL = no sale |
| `capacity` | int DEFAULT NULL | Total seats; NULL = unlimited |
| `show_remaining` | tinyint(1) DEFAULT 0 | Show remaining count on frontend |
| `min_per_booking` | int DEFAULT 1 | Minimum tickets per booking |
| `per_ticket_fees` | longtext DEFAULT NULL | JSON array — fees multiplied by qty |
| `per_booking_fees` | longtext DEFAULT NULL | JSON array — flat fees per booking |
| `booking_starts` | longtext DEFAULT NULL | JSON — opening config (see below) |
| `show_booking_dates` | tinyint(1) DEFAULT 0 | Show availability dates on frontend |
| `visibility` | longtext DEFAULT NULL | JSON — role-based visibility rules |
| `is_active` | tinyint(1) DEFAULT 1 | Soft delete |
| `priority` | int DEFAULT 0 | Display order |
| `created_at` | datetime NOT NULL | |
| `updated_at` | datetime DEFAULT NULL | |

**Indexes:** `category_id`

**Booking closes** — handled by two rules applied at runtime (not stored on ticket type):
1. Global setting `dish_booking_close_offset` — closes N minutes before event start (default: 30)
2. Per-class `dish_booking_closes_at` post meta — explicit hard override
3. Auto-close when `capacity` is reached

**`booking_starts` JSON structure:**
```json
// Immediately available
{"mode": "immediate"}

// N days before event start
{"mode": "days_before", "days": 30}

// Specific calendar date
{"mode": "date", "date": "2026-05-01"}
```

**Fee repeater JSON structure:**
```json
[
  {"label": "Kitchen Supply Fee", "amount_cents": 500},
  {"label": "Booking Fee",        "amount_cents": 200}
]
```

---

#### `{prefix}dish_ticket_categories`

Global organisational groups for ticket types. Not tied to any class.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | varchar(100) | e.g. "Hands On", "Skills Class" |
| `description` | text DEFAULT NULL | |
| `priority` | int DEFAULT 0 | Display order |
| `is_active` | tinyint(1) DEFAULT 1 | |
| `created_at` | datetime NOT NULL | |
| `updated_at` | datetime DEFAULT NULL | |

---

#### `{prefix}dish_checkout_fields`

Custom fields collected at checkout (admin-configured, global).

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `field_type` | varchar(50) | `text`, `select`, `checkbox`, `textarea`, `radio` |
| `label` | varchar(255) | |
| `options` | text DEFAULT NULL | JSON for select/radio options |
| `is_required` | tinyint(1) DEFAULT 0 | |
| `apply_per_attendee` | tinyint(1) DEFAULT 0 | Repeat per attendee vs once per booking |
| `priority` | int DEFAULT 0 | |
| `is_active` | tinyint(1) DEFAULT 1 | |
| `created_at` | datetime NOT NULL | |
| `updated_at` | datetime DEFAULT NULL | |

---

### 4e. `wp_options` Keys

| Key | Contents |
|---|---|
| `dish_settings` | Serialised settings array (all plugin settings) |
| `dish_db_version` | Current schema version (semver string) |
| `dish_activation_redirect` | Flag for post-activation redirect |
| `dish_encrypt_key` | Random key for light encryption (QR data, etc.) |

---

### 4f. Recurrence Rule Structure (`dish_recurrence` post meta)

Stored as JSON on the parent class:

```json
{
  "type": "weekly",
  "interval": 1,
  "days": ["thursday"],
  "ends": "on",
  "end_date": "2026-12-31",
  "end_after": null,
  "child_ids": [123, 124, 125]
}
```

`type` values: `daily` | `weekly` | `monthly` | `yearly`  
`ends` values: `on` (specific date) | `after` (N occurrences) | `never`

---

## 5. Settings

All stored under `dish_settings`. Organised into logical groups.

### General

| Key | Type | Default | Description |
|---|---|---|---|
| `time_format` | string | `g:i a` | PHP time format string |
| `date_format` | string | `j F Y` | PHP date format string |
| `timezone_display` | bool | `true` | Show timezone note on class pages |
| `timezone_message` | string | template | Timezone notice template |
| `currency` | string | `CAD` | ISO currency code |
| `currency_symbol` | string | `$` | Display symbol |
| `currency_position` | string | `before` | `before` or `after` |
| `checkout_timer_minutes` | int | `10` | Cart expiry in minutes |
| `booking_close_offset` | int | `30` | Minutes before event start that bookings automatically close |

### Venue (single)

| Key | Type | Description |
|---|---|---|
| `venue_name` | string | Studio name |
| `venue_address` | string | Street address |
| `venue_suburb` | string | |
| `venue_state` | string | |
| `venue_postcode` | string | |
| `venue_google_maps_url` | string | Direct Google Maps link |
| `venue_lat` | string | Latitude |
| `venue_lng` | string | Longitude |
| `venue_gmap_api_key` | string | Google Maps embed API key |

### Studio / Organizer (single)

| Key | Type | Description |
|---|---|---|
| `studio_name` | string | Studio display name |
| `studio_email` | string | Contact/reply-to email |
| `studio_phone` | string | |
| `studio_website` | string | |
| `studio_instagram` | string | |
| `studio_facebook` | string | |

### Pages

| Key | Description |
|---|---|
| `classes_page` | Page ID hosting `[dish_classes]` |
| `booking_page` | Page ID hosting `[dish_booking]` |
| `booking_details_page` | Page ID hosting `[dish_booking_details]` |
| `profile_page` | Page ID hosting `[dish_profile]` |
| `login_page` | Page ID hosting `[dish_login]` |
| `register_page` | Page ID hosting `[dish_register]` |
| `chefs_page` | Page ID hosting `[dish_chefs]` |

### Calendar & Views

| Key | Type | Default | Description |
|---|---|---|---|
| `default_cal_view` | string | `month` | FullCalendar initial view |
| `available_views` | array | all | Which view toggles appear |
| `hide_past_classes` | bool | `false` | Exclude expired classes from listings |
| `classes_per_page` | int | `10` | Listing page size |
| `show_class_type_on_calendar` | bool | `true` | Show format filter on calendar |
| `calendar_title_format` | string | `MMMM YYYY` | FullCalendar header format |
| `max_classes_per_day` | int | `3` | Calendar cell overflow limit |
| `open_class_in_new_tab` | bool | `false` | |

### Payments

| Key | Type | Default | Description |
|---|---|---|---|
| `active_gateway` | string | `paypal` | Active payment gateway slug |
| `paypal_mode` | string | `sandbox` | `sandbox` or `live` |
| `paypal_client_id` | string | `''` | PayPal client ID |
| `gateway_order` | array | `['paypal']` | Gateway display order (future-proof) |

### Emails

| Key | Description |
|---|---|
| `email_from_name` | Sender name |
| `email_from_address` | Sender address |
| `email_admin_to` | Admin notification recipient |
| `disable_customer_emails` | Global kill switch |
| `disable_admin_emails` | Global kill switch |
| Confirmation: `booking_confirm_subject` / `_body` / `_cc` | |
| Pending: `booking_pending_subject` / `_body` / `_cc` | |
| Cancellation: `booking_cancel_subject` / `_body` / `_cc` | |
| Refund: `booking_refund_subject` / `_body` / `_cc` | |
| Admin copy: `admin_booking_subject` / `_body` | |
| Registration: `register_subject` / `_body` | |
| Password reset: `reset_password_subject` / `_body` | |

### SEO / URLs

| Key | Type | Default |
|---|---|---|
| `class_slug` | string | `class` |
| `chef_slug` | string | `chef` |
| `class_format_slug` | string | `class-format` |

### Features

| Key | Type | Default | Description |
|---|---|---|---|
| `google_calendar_link` | bool | `true` | Show "Add to Google Calendar" |
| `ical_download` | bool | `true` | Allow iCal file download |
| `show_qr_on_booking` | bool | `true` | QR code on booking confirmation |
| `guest_checkout` | bool | `true` | Allow bookings without account |
| `allow_account_creation` | bool | `true` | Offer account creation at checkout |

---

## 6. Frontend Shortcodes

| Shortcode | Replaces | Description |
|---|---|---|
| `[dish_classes]` | `[em_events]` | Classes listing / calendar |
| `[dish_class]` | `[em_event]` | Single class embed |
| `[dish_chefs]` | `[em_performers]` | Chefs listing |
| `[dish_chef]` | `[em_performer]` | Single chef profile |
| `[dish_class_formats]` | `[em_event_types]` | Class formats listing |
| `[dish_booking]` | `[em_booking]` | Checkout flow |
| `[dish_booking_details]` | `[em_booking_details]` | Booking confirmation / detail |
| `[dish_profile]` | `[em_profile]` | Customer profile & booking history |
| `[dish_login]` | `[em_login]` | Login form |
| `[dish_register]` | `[em_register]` | Registration form |

---

## 7. Admin Menu Structure

```
Dish Events  (dashicons-food)
├── All Classes
├── Add New Class
├── Class Formats         (taxonomy)
├── Chefs                 (CPT sub-menu)
├── Bookings              (CPT sub-menu)
├── Ticketing
│   ├── Ticket Types      (default landing, WP_List_Table)
│   └── Categories        (WP_List_Table)
├── Reports
└── Settings
```

---

## 8. Admin Screens

### Classes list
- Custom columns: date, chef(s), format, capacity, bookings count, status
- Filters: class format, date range, status
- Bulk action: duplicate class
- Sortable: date

### Class edit screen
Custom meta box (tabbed):
- **Date & Time** — start, end, recurrence
- **Tickets** — category dropdown, ticket type dropdown, read-only type summary, booking opens/closes overrides
- **Chefs** — multi-select from `dish_chef` posts
- **Details** — class type (public/corporate), min/max attendees
- **Checkout** — per-class checkout field overrides
- **Settings** — featured flag, external booking URL

### Bookings list
- Custom columns: booking ID, class, customer name/email, attendees, total, status, date
- Filters: class, status, date range
- Bulk action: export CSV
- Export all button (above list)

### Booking detail screen
- Read-only meta boxes: general info, tickets, attendees, checkout fields, notes, transaction log, actions (approve / cancel / refund)

### Reports
- Bookings tab: stat overview (total revenue, avg per day, total bookings) + filterable list
- Payments tab: revenue breakdown
- Attendees tab: per-class attendee list + CSV export

### Settings
Tabbed page: General | Venue | Studio | Pages | Calendar | Payments | Emails | URLs | Features

---

## 9. Booking Flow

```
1. Customer views class detail page
   → sees price and capacity remaining (sourced from the class's assigned ticket type)
   → booking window status checked against ticket type `booking_starts` config and global `booking_close_offset`

2. Customer clicks "Book Now"
   → redirect to booking page with class + date params
   → OR inline booking form on class page (TBD)

3. Checkout form
   → ticket quantity selector (respects ticket type `min_per_booking`; capacity is the upper bound)
   → custom checkout fields (global + per-class overrides)
   → customer details (name, email, phone)
   → checkbox: "Create an account?" (unchecked by default)
      → when checked: reveals username + password fields inline
      → account created on booking confirmation, not before
   → order summary with total
   → checkout timer starts

4. Payment
   → PayPal Smart Buttons render
   → On PayPal approval → AJAX verify + complete booking
   → On failure/cancel → booking remains pending, timer continues

5. Booking confirmed
   → `dish_booking` post created with status `dish_completed`
   → Confirmation email → customer
   → Admin notification email → studio
   → Redirect to booking details page with QR code + iCal/GCal links

6. If timer expires before payment
   → Booking cancelled
   → Capacity released
```

---

## 10. Notifications

All emails use `wp_mail()`. Templates are HTML, editable via settings. Token system: `{{customer_name}}`, `{{class_title}}`, `{{class_date}}`, `{{booking_id}}`, `{{total}}`, `{{studio_name}}`, etc.

| Email | Trigger | Recipient |
|---|---|---|
| Booking confirmed | Payment completed | Customer |
| Admin new booking | Payment completed | Studio |
| Booking pending | Payment initiated, not yet confirmed | Customer |
| Booking cancelled | Admin cancels or timer expires | Customer |
| Booking refunded | Admin marks refunded | Customer |
| Account created | Guest creates account at checkout | Customer |
| Password reset | User requests reset | Customer |

---

## 11. Payment Architecture

### Gateway interface

```php
namespace Dish\Events\Payments;

interface GatewayInterface {
    public function get_slug(): string;
    public function get_label(): string;
    public function is_configured(): bool;
    public function render_button( array $booking_data ): void;
    public function handle_callback( array $data ): bool;
}
```

`PayPalGateway` implements this interface. Future gateways (Stripe, etc.) each get their own class. Active gateway registered via `dish_payment_gateways` filter.

### PayPal flow
- PayPal JS SDK loaded conditionally on checkout page only
- Smart Buttons render client-side
- On approval: AJAX `dish_paypal_confirm` verifies order server-side via PayPal Orders API
- No IPN — uses JS SDK + server-side verification

---

## 12. Calendar (FullCalendar)

FullCalendar.js loaded on pages containing `[dish_classes]`. Class data fed via REST endpoint `GET /wp-json/dish/v1/classes` with date range params. Response includes class ID, title, start/end timestamps, format colour, capacity status, permalink.

Views available:
- Month (`dayGridMonth`)
- Week (`timeGridWeek`)
- Day (`timeGridDay`)
- List/Agenda (`listWeek`)
- Square Grid (custom)
- Staggered/Masonry (custom)
- Slider/Carousel (custom)
- Rows (custom)

---

## 13. AJAX Endpoints

All prefixed `dish_`. Auth-required endpoints return 403 for unauthenticated requests. Public (nopriv) endpoints are rate-limited via nonce.

| Action | Public | Purpose |
|---|---|---|
| `load_classes` | ✅ | Load-more for class listings |
| `load_class_detail` | ✅ | Single class content via AJAX |
| `load_class_dates` | ✅ | Available dates for a recurring class |
| `save_booking` | ✅ | Create/update booking during checkout |
| `paypal_confirm` | ✅ | Server-side PayPal order verification |
| `booking_timer_expire` | ✅ | Release capacity on timer expiry |
| `cancel_booking_process` | ✅ | User cancels mid-checkout |
| `validate_booking_fields` | ✅ | Live-validate checkout form fields |
| `update_ticket_quantity` | ✅ | Recalculate totals on qty change |
| `check_capacity` | ✅ | Check remaining spots |
| `login` | ✅ | Submit login form |
| `register` | ✅ | Submit register form |
| `load_chefs` | ✅ | Load-more for chef listings |
| `load_formats` | ✅ | Load-more for format listings |
| `get_calendar_classes` | ✅ | Calendar AJAX feed |
| `booking_cancel` | 🔒 | Customer cancels booking |
| `save_checkout_field` | 🔒 | Admin: save checkout field |
| `delete_checkout_field` | 🔒 | Admin: delete checkout field |
| `booking_update_status` | 🔒 | Admin: change booking status |
| `booking_add_note` | 🔒 | Admin: add internal note |
| `booking_export` | 🔒 | Admin: export bookings CSV |
| `calendar_create_class` | 🔒 | Admin: create class from calendar |
| `calendar_drag_date` | 🔒 | Admin: drag class to new date |
| `calendar_delete` | 🔒 | Admin: delete class from calendar |
| `reports_filter` | 🔒 | Admin: filter reports |
| `attendees_export` | 🔒 | Admin: export attendees |
| `send_attendees_email` | 🔒 | Admin: bulk email to class attendees |

---

## 14. REST Endpoints

| Route | Method | Auth | Purpose |
|---|---|---|---|
| `/dish/v1/classes` | GET | Public | Calendar feed (date range filter) |
| `/dish/v1/ping` | GET | Public | Health check |

---

## 15. Non-Functional Requirements

| Requirement | Detail |
|---|---|
| **No jQuery (frontend)** | All frontend JS must be vanilla. jQuery may be present (WP core) but must not be relied upon. |
| **No Gutenberg** | No block registration. No block editor on this install. |
| **Mobile-first** | All frontend templates responsive from 320px up. Checkout fully functional on mobile. |
| **Accessibility** | WCAG 2.1 AA target. Forms must have proper labels, error messaging, focus management. Calendar keyboard-navigable. |
| **Performance** | Scripts and styles enqueued conditionally — only on pages containing relevant shortcodes. REST endpoint response cached with `wp_cache`. |
| **Security** | All AJAX endpoints nonce-verified. All output escaped. All input sanitised. Capability checks on all admin actions. |
| **Naming** | Every slug, option key, function, class, hook, and CSS class is prefixed `dish_` or `dish-`. Zero `em_`, `ep_`, `eventprime_` references. |
| **Single install** | No multi-site support required. No license system. No extension marketplace. |
| **PHP 8.0+** | Typed properties, named arguments, match expressions permitted. |

---

## 16. Decisions Log

| # | Question | Decision |
|---|---|---|
| 1 | Booking confirmation — separate page or inline? | **Separate page.** Redirect to `booking_details_page` after payment completes. |
| 2 | Corporate bookings — same checkout or separate enquiry form? | **Standard checkout for now.** Separate corporate enquiry form = future feature. |
| 3 | Customer self-service cancellation? | **Pending — cancellation policy to be supplied.** Admin-only until policy is defined and built. |
| 4 | Refunds — manual or PayPal API? | **Pending — refund policy to be supplied.** Admin manually marks refunded at launch; PayPal Refunds API = future feature. |
| 5 | Account creation prompt — mandatory or optional? | **Optional.** Default is guest. Checkbox "Create an account?" (unchecked) reveals username + password fields inline. Account is created only after payment confirms. |
| 6 | Class detail URL structure? | **Standard WP hierarchical permalink** — e.g. `/class/pasta-night/`. Uses `single-dish_class.php` template, not `the_content` injection. |
| 7 | Venue map — embed or link? | **Address text always visible. "View Map" button lazy-loads the Google Maps iframe.** Matches existing site behaviour. |
| 8 | Email template design? | **HTML templates with studio branding.** Token-based (`{{customer_name}}` etc.), templates editable in Settings → Emails. |
| 9 | Ticketing model — per-class inline records or global reusable templates? | **Global templates (Option A).** Ticket types are created once and reused. Class stores a single `dish_ticket_type_id` FK. Price, capacity, and fees all live on the ticket type. |
| 10 | Where does booking close logic live? | **Two-layer rule.** Global setting `booking_close_offset` (default: 30 min before start) + per-class `dish_booking_closes_at` hard override. Sold-out auto-closes at runtime. No close config on the ticket type. |
| 11 | Social sharing? | **Removed entirely.** No social tab, no `dish_social_links` meta, no feature flag. |
| 12 | `max_per_booking` on ticket type? | **Removed.** Capacity is the natural upper bound. Only `min_per_booking` is stored. |

### Open questions (resolve before Phase 9 — Booking Engine)

| # | Question |
|---|---|
| OQ-1 | When both `dish_booking_opens_at` (on class) and `booking_starts` (on ticket type) are set, which takes precedence? Proposed: ticket type config applies first; class-level field acts as a hard outer boundary. |
| OQ-2 | Ticket type `description` field — customer-facing at checkout, internal admin note, or both with separate fields? |
| OQ-3 | Are sale prices ever time-bounded (early-bird pricing with an end date)? Not in scope now but worth flagging before Phase 9. |
| OQ-4 | When a booking is created, price snapshot must be stored. Is `dish_total_cents` on the booking sufficient, or is a line-item breakdown (base + per-ticket fees + per-booking fees) needed on the booking record? |
