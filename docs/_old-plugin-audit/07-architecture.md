# Dish Events — Architecture

**Document:** `07-architecture.md`  
**Status:** 🟡 Draft  
**Last updated:** 2026-03-21  
**Depends on:** `06-prd.md`

---

## 1. Plugin Root

```
wp-content/plugins/dish-events/
├── dish-events.php          ← Plugin header + bootstrap call
├── uninstall.php            ← Cleanup on plugin delete
├── includes/                ← All PHP (autoloaded)
├── templates/               ← PHP view templates
├── assets/                  ← CSS / JS / vendor
└── languages/               ← .pot / .po / .mo
```

`dish-events.php` does three things only: defines constants, requires the autoloader, and calls `Dish\Events\Core\Plugin::run()`. No logic in the root file.

---

## 2. PHP Namespace Map

```
Dish\Events\
├── Core\
│   ├── Plugin               ← Bootstrap: instantiates all modules, fires dish_events_loaded
│   ├── Loader               ← Accumulates add_action / add_filter calls, registers them all at once
│   ├── Activator            ← DB table creation, option seeding, flush_rewrite_rules (once)
│   ├── Deactivator          ← Unschedule cron jobs (does NOT delete data)
│   └── Updater              ← DB migration runner keyed on dish_db_version
│
├── CPT\
│   ├── ClassPost            ← Register dish_class CPT + post statuses
│   ├── ChefPost             ← Register dish_chef CPT
│   └── BookingPost          ← Register dish_booking CPT + post statuses
│
├── Taxonomy\
│   └── ClassFormat          ← Register dish_class_format taxonomy, seed default terms
│
├── Admin\
│   ├── Admin                ← Admin hook registrar (ties all admin modules to the Loader)
│   ├── Settings             ← Settings page: tabbed WP Settings API, get/set helpers
│   ├── ClassMetaBox         ← Tabbed meta box on dish_class edit screen
│   ├── ChefMetaBox          ← Meta box on dish_chef edit screen
│   ├── BookingMetaBox       ← Read-only detail boxes on dish_booking edit screen
│   ├── ClassColumns         ← Custom columns + filters on dish_class list table
│   ├── BookingColumns       ← Custom columns + filters on dish_booking list table
│   └── Reports              ← Reports admin page (bookings, revenue, attendees)
│
├── Frontend\
│   ├── Frontend             ← Frontend hook registrar
│   ├── Shortcodes           ← Register all [dish_*] shortcodes
│   ├── Calendar             ← FullCalendar init, JS config localisation
│   ├── ClassView            ← Render single class / archive templates
│   ├── ChefView             ← Render chef templates
│   ├── BookingView          ← Render checkout form, timer, confirmation
│   └── Assets               ← Conditional enqueue of scripts and styles
│
├── Booking\
│   ├── BookingManager       ← Create, update, cancel, read bookings
│   ├── CapacityManager      ← Available spots: reads bookings, respects ticket caps
│   └── Timer                ← Checkout timer: WP transient-backed reservation hold
│
├── Payments\
│   ├── GatewayInterface     ← Contract all gateways must satisfy
│   ├── GatewayRegistry      ← dish_payment_gateways filter, active gateway resolution
│   └── PayPalGateway        ← PayPal JS SDK + Orders API verification
│
├── Notifications\
│   ├── NotificationService  ← Dispatch emails via wp_mail(); respects kill switches
│   ├── EmailTemplate        ← Load template file, replace {{tokens}}, wrap in HTML layout
│   └── Templates\
│       ├── BookingConfirmed ← Customer confirmation
│       ├── BookingPending   ← Customer pending
│       ├── BookingCancelled ← Customer cancellation
│       ├── BookingRefunded  ← Customer refund notice
│       ├── AdminNewBooking  ← Studio notification
│       ├── AccountCreated   ← Welcome + login link
│       └── PasswordReset    ← Reset link
│
├── Recurrence\
│   └── RecurrenceManager    ← Generate/update/delete recurring dish_class posts from a rule
│
├── REST\
│   └── ClassesEndpoint      ← GET /wp-json/dish/v1/classes (calendar feed, cached)
│
├── Ajax\
│   ├── PublicAjax           ← wp_ajax_nopriv_dish_* handlers
│   └── AdminAjax            ← wp_ajax_dish_* handlers (capability-checked)
│
├── Data\
│   ├── ClassRepository      ← WP_Query wrappers for dish_class
│   ├── BookingRepository    ← WP_Query + $wpdb for dish_booking + dish_tickets table
│   ├── ChefRepository       ← WP_Query wrappers for dish_chef
│   ├── TicketRepository     ← $wpdb CRUD for dish_tickets table
│   └── CheckoutFieldRepo    ← $wpdb CRUD for dish_checkout_fields table
│
└── Helpers\
    ├── DateHelper           ← UTC ↔ site-timezone conversion, formatting
    ├── MoneyHelper          ← cents ↔ display, currency symbol, formatting
    ├── QRHelper             ← Generate QR PNG via phpqrcode (bundled)
    └── ICalHelper           ← Build .ics file content for single class
```

---

## 3. Directory Structure

```
dish-events/
│
├── dish-events.php
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
│   │   ├── class-class-post.php
│   │   ├── class-chef-post.php
│   │   └── class-booking-post.php
│   │
│   ├── Taxonomy/
│   │   └── class-class-format.php
│   │
│   ├── Admin/
│   │   ├── class-admin.php
│   │   ├── class-settings.php
│   │   ├── class-class-metabox.php
│   │   ├── class-chef-metabox.php
│   │   ├── class-booking-metabox.php
│   │   ├── class-class-columns.php
│   │   ├── class-booking-columns.php
│   │   └── class-reports.php
│   │
│   ├── Frontend/
│   │   ├── class-frontend.php
│   │   ├── class-shortcodes.php
│   │   ├── class-calendar.php
│   │   ├── class-class-view.php
│   │   ├── class-chef-view.php
│   │   ├── class-booking-view.php
│   │   └── class-assets.php
│   │
│   ├── Booking/
│   │   ├── class-booking-manager.php
│   │   ├── class-capacity-manager.php
│   │   └── class-timer.php
│   │
│   ├── Payments/
│   │   ├── interface-gateway.php
│   │   ├── class-gateway-registry.php
│   │   └── class-paypal-gateway.php
│   │
│   ├── Notifications/
│   │   ├── class-notification-service.php
│   │   ├── class-email-template.php
│   │   └── Templates/
│   │       ├── booking-confirmed.php
│   │       ├── booking-pending.php
│   │       ├── booking-cancelled.php
│   │       ├── booking-refunded.php
│   │       ├── admin-new-booking.php
│   │       ├── account-created.php
│   │       └── password-reset.php
│   │
│   ├── Recurrence/
│   │   └── class-recurrence-manager.php
│   │
│   ├── REST/
│   │   └── class-classes-endpoint.php
│   │
│   ├── Ajax/
│   │   ├── class-public-ajax.php
│   │   └── class-admin-ajax.php
│   │
│   ├── Data/
│   │   ├── class-class-repository.php
│   │   ├── class-booking-repository.php
│   │   ├── class-chef-repository.php
│   │   ├── class-ticket-repository.php
│   │   └── class-checkout-field-repository.php
│   │
│   └── Helpers/
│       ├── class-date-helper.php
│       ├── class-money-helper.php
│       ├── class-qr-helper.php
│       └── class-ical-helper.php
│
├── templates/
│   ├── classes/
│   │   ├── archive.php          ← [dish_classes] default list/grid view
│   │   ├── single.php           ← [dish_class] + single-dish_class.php
│   │   ├── card.php             ← Partial: class card used in archive + calendar
│   │   └── calendar.php         ← FullCalendar container + view toggle UI
│   │
│   ├── chefs/
│   │   ├── archive.php          ← [dish_chefs] listing
│   │   ├── single.php           ← single-dish_chef.php
│   │   └── card.php             ← Partial: chef card
│   │
│   ├── booking/
│   │   ├── checkout.php         ← [dish_booking] checkout form
│   │   ├── timer.php            ← Partial: countdown timer bar
│   │   ├── confirmation.php     ← Inline confirmation state (pre-redirect)
│   │   └── details.php          ← [dish_booking_details] confirmation page
│   │
│   └── account/
│       ├── login.php            ← [dish_login]
│       ├── register.php         ← [dish_register]
│       └── profile.php          ← [dish_profile] booking history
│
├── assets/
│   ├── css/
│   │   ├── dish-events.scss     ← Frontend styles source
│   │   ├── dish-events.css      ← Compiled (Live Sass Compiler)
│   │   ├── dish-events.min.css  ← Minified (Auto-Minify)
│   │   ├── dish-admin.scss      ← Admin styles source
│   │   ├── dish-admin.css
│   │   └── dish-admin.min.css
│   │
│   ├── js/
│   │   ├── dish-calendar.js     ← FullCalendar init + view switching (vanilla)
│   │   ├── dish-calendar.min.js
│   │   ├── dish-booking.js      ← Checkout form: qty, timer, PayPal, account toggle
│   │   ├── dish-booking.min.js
│   │   ├── dish-admin.js        ← Admin meta box tabs, recurrence UI, AJAX saves
│   │   └── dish-admin.min.js
│   │
│   └── vendor/
│       ├── fullcalendar/        ← FullCalendar bundle (existing, kept from EventPrime)
│       └── phpqrcode/           ← QR code generator (existing, path fixed)
│
└── languages/
    └── dish-events.pot
```

---

## 4. Bootstrap & Loading Order

`dish-events.php`:
```
constants → autoloader → Plugin::run()
```

`Plugin::run()` (fired on `plugins_loaded`):
```
1. Loader instantiated
2. Settings           → loaded first (all other modules may read settings)
3. CPT modules        → ClassPost, ChefPost, BookingPost  (hooks: init)
4. Taxonomy           → ClassFormat                        (hooks: init)
5. Updater            → run migrations if dish_db_version out of date
6. Admin modules      → only when is_admin()
7. Frontend modules   → only when !is_admin()
8. Booking            → BookingManager, CapacityManager, Timer
9. Payments           → GatewayRegistry (registers PayPalGateway by default)
10. Notifications     → NotificationService
11. Recurrence        → RecurrenceManager
12. REST              → ClassesEndpoint (hooks: rest_api_init)
13. Ajax              → PublicAjax, AdminAjax
14. Loader::run()     → all add_action/add_filter calls registered at once
15. do_action('dish_events_loaded')
```

`flush_rewrite_rules()` is called **only** in `Activator::activate()` via `register_activation_hook`. Never on `init`.

---

## 5. Autoloader

PSR-4 manual autoloader (no Composer dependency) in `dish-events.php`:

```php
spl_autoload_register( function( string $class ): void {
    $prefix = 'Dish\\Events\\';
    if ( ! str_starts_with( $class, $prefix ) ) return;

    $relative = substr( $class, strlen( $prefix ) );
    $parts    = explode( '\\', $relative );
    $filename = 'class-' . strtolower( str_replace( '_', '-', array_pop( $parts ) ) ) . '.php';

    // interface- prefix for GatewayInterface
    if ( str_ends_with( $filename, 'interface.php' ) ) {
        $filename = str_replace( 'class-', 'interface-', $filename );
    }

    $path = plugin_dir_path( __FILE__ ) . 'includes/' . implode( '/', $parts ) . '/' . $filename;
    if ( file_exists( $path ) ) require_once $path;
} );
```

---

## 6. Settings Module

`Dish\Events\Admin\Settings` is the single source of truth for all plugin configuration.

```php
// Read a setting
Settings::get( 'paypal_client_id' );
Settings::get( 'currency', 'AUD' );

// Write a setting (admin only)
Settings::set( 'paypal_mode', 'live' );

// All settings flushed from object cache on save
```

Stored as a single serialised array under `dish_settings` in `wp_options`. All keys and defaults are declared in a `$defaults` property on the class — this is the definitive registry of every setting.

---

## 7. Hook Architecture

### Actions (plugin fires)

| Hook | When | Args |
|---|---|---|
| `dish_events_loaded` | After full bootstrap | — |
| `dish_before_class_archive` | Before archive template renders | `$args` array |
| `dish_after_class_archive` | After archive template renders | `$args` array |
| `dish_before_single_class` | Before single class template | `WP_Post $class` |
| `dish_after_single_class` | After single class template | `WP_Post $class` |
| `dish_before_checkout` | Before checkout form renders | `$booking_data` array |
| `dish_after_checkout` | After checkout form renders | `$booking_data` array |
| `dish_booking_created` | After `dish_booking` post inserted | `int $booking_id` |
| `dish_booking_confirmed` | After payment verified | `int $booking_id` |
| `dish_booking_cancelled` | After booking cancelled | `int $booking_id`, `string $reason` |
| `dish_booking_refunded` | After booking marked refunded | `int $booking_id` |
| `dish_capacity_updated` | After confirmed bookings count changes | `int $class_id`, `int $remaining` |
| `dish_send_notification` | Before `wp_mail()` is called | `string $type`, `array $data` |
| `dish_recurrence_generated` | After recurring classes created | `int $parent_id`, `array $child_ids` |

### Filters (plugin exposes)

| Filter | What it modifies | Args |
|---|---|---|
| `dish_payment_gateways` | Array of `GatewayInterface` instances | `array $gateways` |
| `dish_booking_data` | Booking array before post insert | `array $data` |
| `dish_checkout_fields` | Fields array shown at checkout | `array $fields`, `int $class_id` |
| `dish_calendar_class_data` | Single class array in REST response | `array $data`, `WP_Post $class` |
| `dish_class_capacity` | Computed available spots | `int $remaining`, `int $class_id` |
| `dish_notification_tokens` | Token → value map for email | `array $tokens`, `string $type`, `int $booking_id` |
| `dish_notification_subject` | Email subject string | `string $subject`, `string $type` |
| `dish_notification_body` | Email HTML body | `string $body`, `string $type` |
| `dish_enqueue_frontend_scripts` | Array of script handles to enqueue | `array $handles`, `string $context` |
| `dish_class_card_classes` | CSS classes on class card element | `array $classes`, `WP_Post $class` |
| `dish_ical_event_data` | iCal VEVENT properties | `array $props`, `WP_Post $class` |
| `dish_qr_payload` | String encoded in QR code | `string $payload`, `int $booking_id` |

---

## 8. Data Layer

Repositories are thin wrappers — no business logic. Business logic lives in the `Booking\` and `Recurrence\` modules.

### ClassRepository

```php
get( int $id ): ?WP_Post
query( array $args ): array          // wraps WP_Query
get_upcoming( int $limit ): array
get_by_format( int $term_id ): array
get_chef_ids( int $class_id ): array
get_booked_count( int $class_id ): int
```

### BookingRepository

```php
get( int $id ): ?WP_Post
get_for_class( int $class_id ): array
get_for_customer( string $email ): array
create( array $data ): int|WP_Error
update_status( int $id, string $status ): bool
add_note( int $id, string $note ): void
export_csv( array $filters ): string  // returns file path
```

### TicketRepository

Uses `$wpdb` directly. Tables use `{prefix}dish_tickets`.

```php
get_for_class( int $class_id ): array
save( array $ticket ): int           // insert or update
delete( int $id ): bool
reorder( array $ordered_ids ): void
```

### CheckoutFieldRepository

Uses `$wpdb` directly. Table `{prefix}dish_checkout_fields`.

```php
get_active(): array
save( array $field ): int
delete( int $id ): bool
reorder( array $ordered_ids ): void
```

---

## 9. Database Schema (DDL)

Run in `Activator::create_tables()` via `dbDelta()`.

```sql
CREATE TABLE {prefix}dish_tickets (
    id                  bigint(20)   NOT NULL AUTO_INCREMENT,
    class_id            bigint(20)   NOT NULL,
    name                varchar(255) NOT NULL DEFAULT '',
    description         text,
    price_cents         int(11)      NOT NULL DEFAULT 0,
    special_price_cents int(11)               DEFAULT NULL,
    capacity            int(11)               DEFAULT NULL,
    min_per_booking     int(11)      NOT NULL DEFAULT 1,
    max_per_booking     int(11)               DEFAULT NULL,
    booking_opens_at    datetime              DEFAULT NULL,
    booking_closes_at   datetime              DEFAULT NULL,
    visibility_roles    text                  DEFAULT NULL,
    is_active           tinyint(1)   NOT NULL DEFAULT 1,
    priority            int(11)      NOT NULL DEFAULT 0,
    created_at          datetime     NOT NULL,
    updated_at          datetime              DEFAULT NULL,
    PRIMARY KEY (id),
    KEY class_id (class_id)
) {charset_collate};

CREATE TABLE {prefix}dish_ticket_categories (
    id          bigint(20)   NOT NULL AUTO_INCREMENT,
    class_id    bigint(20)   NOT NULL,
    name        varchar(100) NOT NULL DEFAULT '',
    capacity    int(11)               DEFAULT NULL,
    priority    int(11)      NOT NULL DEFAULT 0,
    is_active   tinyint(1)   NOT NULL DEFAULT 1,
    created_at  datetime     NOT NULL,
    updated_at  datetime              DEFAULT NULL,
    PRIMARY KEY (id),
    KEY class_id (class_id)
) {charset_collate};

CREATE TABLE {prefix}dish_checkout_fields (
    id                bigint(20)   NOT NULL AUTO_INCREMENT,
    field_type        varchar(50)  NOT NULL DEFAULT 'text',
    label             varchar(255) NOT NULL DEFAULT '',
    options           text                  DEFAULT NULL,
    is_required       tinyint(1)   NOT NULL DEFAULT 0,
    apply_per_attendee tinyint(1)  NOT NULL DEFAULT 0,
    priority          int(11)      NOT NULL DEFAULT 0,
    is_active         tinyint(1)   NOT NULL DEFAULT 1,
    created_at        datetime     NOT NULL,
    updated_at        datetime              DEFAULT NULL,
    PRIMARY KEY (id)
) {charset_collate};
```

Migration versioning: `dish_db_version` option stores semver string (e.g. `1.0.0`). `Updater` runs sequential migration methods: `migrate_1_0_0()`, `migrate_1_1_0()` etc. Never uses `ALTER TABLE` destructively without a version gate.

---

## 10. Payment Gateway Architecture

```php
namespace Dish\Events\Payments;

interface GatewayInterface {
    public function get_slug(): string;
    public function get_label(): string;
    public function is_configured(): bool;
    public function enqueue_scripts(): void;            // conditionally loaded on checkout page
    public function render_button( array $booking ): void;
    public function handle_confirm( array $payload ): bool; // server-side verify
    public function handle_cancel( array $payload ): void;
}
```

`GatewayRegistry` resolves the active gateway:

```php
// Registration (PayPal registered by default, others via filter)
add_filter( 'dish_payment_gateways', function( array $gateways ): array {
    $gateways['paypal'] = new PayPalGateway();
    return $gateways;
} );

// Retrieve
GatewayRegistry::get_active(): GatewayInterface
GatewayRegistry::get_all(): array
```

### PayPal flow detail

```
Frontend (dish-booking.js)
  1. PayPal JS SDK loaded with client-id from Settings (deferred, checkout page only)
  2. paypal.Buttons({
       createOrder → AJAX dish_paypal_create_order
                   → server creates PayPal order via Orders API
                   → returns PayPal order ID
       onApprove   → AJAX dish_paypal_confirm
                   → server: capture order via Orders API
                   → on success: BookingManager::confirm( $booking_id )
                   → returns { success, redirect_url }
       onCancel    → AJAX dish_cancel_booking_process (capacity released)
       onError     → show inline error, timer continues
     }).render('#dish-paypal-btn')

Backend (PayPalGateway)
  - No IPN / webhook (JS SDK onApprove is reliable for synchronous capture)
  - Orders API v2: POST /v2/checkout/orders, POST /v2/checkout/orders/{id}/capture
  - Credentials: client_id + secret (secret never exposed to frontend)
  - Sandbox / Live toggled via Settings → Payments → PayPal Mode
```

---

## 11. Recurrence Architecture

All recurring classes are **real `dish_class` posts**. The parent holds the recurrence rule; each child is a fully independent post.

```
dish_class (parent)
  dish_recurrence = {
    "type": "weekly",
    "interval": 1,
    "days": ["thursday"],
    "ends": "on",
    "end_date": "2026-12-31",
    "child_ids": [101, 102, 103, ...]
  }

dish_class (child 101)
  dish_start_at            = 1745280000   (UTC)
  dish_end_at              = 1745287200   (UTC)
  dish_recurrence_parent_id = 99
  (all other meta inherited from parent at creation time, then independent)
```

`RecurrenceManager::generate( int $parent_id, array $rule ): array`
- Reads parent post meta for price, capacity, chef IDs etc.
- Creates one `dish_class` post per occurrence
- Stores `child_ids` back on parent
- Respects `end_date` / `end_after` limits
- Called from admin AJAX `dish_recurrence_save`

`RecurrenceManager::update_series( int $parent_id, array $changed_meta ): void`
- Pushes meta changes to all future children (past children left alone)
- "This and following" update: detaches prior children, creates new parent from target date

`RecurrenceManager::delete_series( int $parent_id ): void`
- Deletes all child posts + parent
- Refuses if any child has confirmed bookings (returns `WP_Error`)

---

## 12. Notifications Architecture

```
Action fired (e.g. dish_booking_confirmed)
  → NotificationService::dispatch( string $type, int $booking_id )
     → check Settings: disable_customer_emails / disable_admin_emails
     → EmailTemplate::render( $type, $tokens )
        → load templates/{type}.php
        → apply dish_notification_tokens filter
        → replace {{tokens}} in subject + body
        → wrap body in HTML layout (header, footer, studio branding)
        → apply dish_notification_subject / dish_notification_body filters
     → wp_mail( $to, $subject, $body, $headers )
```

**Token registry** (resolved by `NotificationService::build_tokens()`):

| Token | Source |
|---|---|
| `{{customer_name}}` | `dish_customer_name` booking meta |
| `{{customer_email}}` | `dish_customer_email` booking meta |
| `{{booking_id}}` | Booking post ID (zero-padded: `#00042`) |
| `{{class_title}}` | Class post title |
| `{{class_date}}` | Formatted `dish_start_at` in site timezone |
| `{{class_time}}` | Formatted time portion |
| `{{class_url}}` | Class permalink |
| `{{booking_url}}` | Booking details page URL + `?booking_id=` |
| `{{total}}` | Formatted total from `dish_total_cents` |
| `{{studio_name}}` | `Settings::get('studio_name')` |
| `{{studio_email}}` | `Settings::get('studio_email')` |
| `{{studio_phone}}` | `Settings::get('studio_phone')` |
| `{{qr_code}}` | `<img>` tag of QR PNG (inline base64 or URL) |
| `{{ical_link}}` | iCal download URL |
| `{{gcal_link}}` | Google Calendar add URL |
| `{{account_password}}` | One-time only in `account-created.php` |

---

## 13. Frontend JavaScript Architecture

No jQuery. Three compiled files, each self-contained IIFE.

### `dish-calendar.js`

Responsibilities:
- Initialize FullCalendar with config localised from PHP via `wp_localize_script`
- Fetch class data from `GET /wp-json/dish/v1/classes?start=&end=` on calendar date navigation
- Handle view toggle buttons (month/week/day/list/grid)
- Render class cards in grid/list/carousel views (non-FullCalendar views fetch via `dish_load_classes` AJAX)
- Filter bar: class format filter, date picker

Localized data object `dishCalendar`:
```js
{
  ajaxUrl:     '/wp-admin/admin-ajax.php',
  restUrl:     '/wp-json/dish/v1/classes',
  nonce:       '...',
  defaultView: 'dayGridMonth',
  views:       ['dayGridMonth','timeGridWeek','listWeek','grid'],
  i18n:        { ... }
}
```

### `dish-booking.js`

Responsibilities:
- Ticket quantity stepper: increment/decrement, enforce min/max, update totals via AJAX `dish_update_ticket_quantity`
- Countdown timer: reads expiry from `dishBooking.timerExpiry` (UTC epoch), renders HH:MM:SS, fires `dish_booking_timer_expire` AJAX on zero
- Account creation toggle: watches `#dish-create-account` checkbox, shows/hides `#dish-account-fields` with CSS transition
- PayPal SDK init: calls `paypal.Buttons()`, wires `createOrder` and `onApprove` to AJAX endpoints
- Form validation: inline error messages on required fields before PayPal renders
- Capacity check: polls `dish_check_capacity` if user is idle > 2 minutes

Localized data object `dishBooking`:
```js
{
  ajaxUrl:    '/wp-admin/admin-ajax.php',
  nonce:      '...',
  classId:    42,
  classDate:  1745280000,
  timerExpiry: 1745286000,
  paypalClientId: '...',
  paypalMode: 'sandbox',
  redirectUrl: '/booking-details/?booking_id=',
  i18n:       { ... }
}
```

### `dish-admin.js`

Responsibilities:
- Meta box tab switching (data-tab pattern, no jQuery UI)
- Recurrence UI: show/hide fields based on recurrence type select
- Date/time pickers: native `<input type="datetime-local">` (no Flatpickr dependency)
- Ticket repeater: add/remove/reorder ticket rows, AJAX save on change
- Checkout field repeater: same pattern
- Reports page: date range filter AJAX, simple vanilla bar chart (or defer to Chart.js if needed)

---

## 14. Asset Enqueue Strategy

All enqueue logic lives in `Dish\Events\Frontend\Assets`.

**Frontend scripts/styles loaded only when the relevant shortcode is present on the current page:**

```php
// Assets checks for shortcode in post content
private function page_has_shortcode( string $tag ): bool {
    global $post;
    return $post && has_shortcode( $post->post_content, $tag );
}
```

| Asset | Condition |
|---|---|
| `dish-events.min.css` | Any `[dish_*]` shortcode, OR `is_singular('dish_class')`, OR `is_singular('dish_chef')` |
| `dish-calendar.min.js` + FullCalendar | `[dish_classes]` with calendar view |
| `dish-booking.min.js` + PayPal SDK | `[dish_booking]` |
| `dish-admin.min.css` + `dish-admin.min.js` | `is_admin()` on plugin screens only |

PayPal JS SDK URL constructed dynamically:
```
https://www.paypal.com/sdk/js?client-id={id}&currency={currency}&intent=capture
```
Enqueued with `wp_enqueue_script` deferred, no dependency on jQuery.

---

## 15. REST Endpoint

`GET /wp-json/dish/v1/classes`

Query params:
- `start` — ISO 8601 date (required for calendar feed)
- `end` — ISO 8601 date (required)
- `format` — term ID filter (optional)
- `type` — `public` | `corporate` (optional)

Response: JSON array of class objects

```json
[
  {
    "id": 42,
    "title": "Pasta Night",
    "start": "2026-04-10T18:00:00+10:00",
    "end": "2026-04-10T21:00:00+10:00",
    "url": "/class/pasta-night/",
    "format": { "id": 3, "name": "Hands-On", "color": "#e85d26" },
    "capacity": 12,
    "spots_remaining": 5,
    "status": "available",
    "thumbnail": "https://..."
  }
]
```

Response cached via `wp_cache_set( 'dish_calendar_' . md5( serialize($params) ), $data, '', 300 )`. Cache flushed on booking confirmation and class update.

Authentication: Public (no auth required — read-only, no PII). Rate-limited by WP nonce on AJAX calls; REST endpoint uses standard WP REST nonce for JS clients.

---

## 16. Capacity Management

`CapacityManager` is the single authority on available spots. Never calculated in templates.

```
Available spots =
  dish_capacity (class meta)
  MINUS
  SUM( dish_attendee_count ) WHERE booking status IN ('dish_pending', 'dish_completed')
  AND dish_class_id = $class_id
  AND dish_class_date = $session_date
```

Pending bookings count against capacity for the duration of the checkout timer. Timer expiry (AJAX `dish_booking_timer_expire`) triggers `BookingManager::cancel()` → `CapacityManager` recalculates.

`CapacityManager::reserve( int $class_id, int $date, int $count ): bool|WP_Error`
- Checks available spots atomically (SELECT FOR UPDATE in transaction)
- Returns `WP_Error` with code `dish_no_capacity` if insufficient spots
- Creates a `dish_booking` post with status `dish_pending` as the reservation

---

## 17. Checkout Timer

The checkout timer is a **WP transient** reservation, not a database row, so it expires automatically if the server-side cleanup job never fires.

```
Timer start:
  Transient key: dish_timer_{booking_id}
  Value:         expiry epoch (time() + checkout_timer_minutes * 60)
  TTL:           checkout_timer_minutes * 60

Frontend:
  dish-booking.js reads dishBooking.timerExpiry (localised from transient value)
  On expiry: AJAX dish_booking_timer_expire → server confirms expiry, cancels booking

Server-side safety net:
  WP Cron job: dish_cleanup_expired_bookings (runs every 15 minutes)
  Finds dish_booking posts with status dish_pending older than (timer + 5 min buffer)
  Cancels them, releases capacity
```

---

## 18. Single Class Template Override

Uses WP's template hierarchy. Themes can override plugin templates by placing files in `{theme}/dish-events/`:

```
{theme}/dish-events/classes/single.php   overrides   plugin/templates/classes/single.php
{theme}/dish-events/classes/card.php     overrides   plugin/templates/classes/card.php
```

Resolution in `ClassView::get_template( string $relative_path ): string`:
```php
$theme_override = get_stylesheet_directory() . '/dish-events/' . $relative_path;
if ( file_exists( $theme_override ) ) return $theme_override;
return plugin_dir_path( DISH_EVENTS_FILE ) . 'templates/' . $relative_path;
```

`single-dish_class.php` in the theme root calls `$class_view->render_single( get_the_ID() )`. No `the_content` injection. Content is rendered directly by the template.

---

## 19. Open / Pending

| Item | Notes |
|---|---|
| Cancellation policy rules | User to supply; will determine whether self-service cancel UI is built |
| Refund policy rules | User to supply; will determine whether PayPal Refunds API is wired or admin-manual only |
| Corporate enquiry form | Deferred to future milestone |
| PayPal Refunds API | Deferred to future milestone |
| Stripe stub | `includes/Payments/class-stripe-gateway.php` created as stub only at launch |
