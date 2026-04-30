# EventPrime — Admin & Settings Audit

**Source plugin:** `eventprime-event-calendar-management` v4.0.9.7  
**Audit date:** 2026-03-21  
**Status:** 🟢 Complete

---

## 1. Admin Menu Structure

```
EventPrime (top-level, icon: dashicons-tickets-alt)
├── All Events          → edit.php?post_type=em_event
├── Add New Event       → post-new.php?post_type=em_event
├── [Event Types]       → edit-tags.php?taxonomy=em_event_type&post_type=em_event
├── [Venues]            → edit-tags.php?taxonomy=em_venue&post_type=em_event
├── [Organizers]        → edit-tags.php?taxonomy=em_event_organizer&post_type=em_event
├── Performers          → edit.php?post_type=em_performer  (sub-menu of em_event)
├── Bookings            → edit.php?post_type=em_booking    (sub-menu of em_event)
├── Reports             → custom admin page
├── Shortcodes          → custom admin page
├── Bulk Emails         → custom admin page
├── Extensions          → custom admin page
└── Settings            → custom admin page (ep_setting_form)
```

> Labels for Event Types, Venues, Organizers are configurable via button labels settings.

---

## 2. Settings Pages

All settings are stored in a single `wp_options` row under the key `em_global_settings` (serialised array). The settings admin page (`ep_setting_form`) uses a tabbed interface. Below is the complete tab and key inventory.

### Tab: General

| Key | Type | Default | Description |
|---|---|---|---|
| `eventprime_theme` | string | `default` | Frontend theme selection |
| `time_format` | string | `h:mmt` | Time display format |
| `default_calendar_date` | timestamp | current date | Starting date for calendar |
| `datepicker_format` | string | `yy-mm-dd&Y-m-d` | Dual-format (JS&PHP) for date pickers |
| `required_booking_attendee_name` | bool | `0` | Require name field in checkout |
| `hide_0_price_from_frontend` | bool | `0` | Hide "Free" / $0 price label |
| `checkout_page_timer` | int | `4` | Minutes before cart expires |
| `enable_event_time_to_user_timezone` | bool | `1` | Convert times to user's local tz |
| `show_timezone_message_on_event_page` | bool | `1` | Show tz conversion notice |
| `timezone_related_message` | string | (template) | The message template |
| `ep_frontend_font_size` | int | `14` | Base font size in px |
| `hide_wishlist_icon` | bool | `0` | Hide wishlist heart icon |
| `enable_dark_mode` | bool | `0` | Dark mode toggle |

### Tab: Regular / SEO URLs

| Key | Type | Default | Description |
|---|---|---|---|
| `enable_seo_urls` | bool | `0` | Enable custom slug rewrites |
| `seo_urls` | array | (see below) | Per-entity slug overrides |
| `ep_desk_normal_screen` | string | `''` | Normal screen max-width |
| `ep_desk_large_screen` | string | `''` | Large screen max-width |

`seo_urls` array defaults:

```php
[
  'event_page_type_url'     => 'event',
  'performer_page_type_url' => 'performer',
  'organizer_page_type_url' => 'organizer',
  'venues_page_type_url'    => 'venue',
  'types_page_type_url'     => 'event-type',
  'sponsor_page_type_url'   => 'sponsor',
]
```

### Tab: Pages

Each setting maps to a WP page ID that renders the corresponding shortcode.

| Key | Description |
|---|---|
| `events_page` | `[em_events]` host page |
| `performers_page` | `[em_performers]` host page |
| `venues_page` | `[em_sites]` host page |
| `event_types` | `[em_event_types]` host page |
| `event_organizers` | `[em_event_organizers]` host page |
| `booking_page` | `[em_booking]` host page |
| `booking_details_page` | `[em_booking_details]` host page |
| `profile_page` | `[em_profile]` host page |
| `login_page` | `[em_login]` host page |
| `register_page` | `[em_register]` host page |
| `event_submit_form` | `[em_event_submit_form]` host page |

### Tab: Events (Frontend View)

| Key | Type | Default | Description |
|---|---|---|---|
| `default_cal_view` | string | `month` | Calendar default view |
| `enable_default_calendar_date` | bool | `0` | Pin calendar to a specific date |
| `calendar_title_format` | string | `MMMM, YYYY` | FullCalendar title format |
| `hide_calendar_rows` | bool | `0` | Collapse empty calendar rows |
| `hide_time_on_front_calendar` | bool | `0` | Hide time in calendar event bars |
| `show_event_types_on_calendar` | bool | `1` | Show type filter on calendar |
| `front_switch_view_option` | array | all views | Which view-switch tabs to show |
| `hide_past_events` | bool | `0` | Exclude expired events from listings |
| `show_no_of_events_card` | int | `10` | Default per-page count |
| `card_view_custom_value` | bool | `1` | Custom column count |
| `disable_filter_options` | bool | `0` | Hide frontend filter bar |
| `hide_old_bookings` | bool | `0` | Hide past-event booking button |
| `calendar_column_header_format` | string | `dddd` | Day header format |
| `shortcode_hide_upcoming_events` | bool | `0` | Hide upcoming panel on shortcode |
| `redirect_third_party` | bool | `0` | Redirect to external booking URL |
| `hide_event_custom_link` | bool | `0` | Hide "Book" link when external URL set |
| `show_qr_code_on_single_event` | bool | `1` | Show QR on event detail |
| `show_max_event_on_calendar_date` | int | `3` | Max events shown per day cell |
| `event_booking_status_option` | string | `''` | Filter events by booking status |
| `open_detail_page_in_new_tab` | bool | `0` | Event link target |
| `events_no_of_columns` | string | `''` | Override column count |
| `events_image_visibility_options` | string | `cover` | Image fit: cover/contain |
| `events_image_height` | string | `''` | Card image height |
| `show_trending_event_types` | bool | `0` | Show trending types section |
| `no_of_event_types_displayed` | int | `5` | Number of types to show |
| `show_events_per_event_type` | bool | `0` | Show event count on type cards |
| `sort_by_events_or_bookings` | string | `''` | Sort events listing by |
| `event_listings_date_format_std_option` | string | `''` | Date format standard option |
| `event_listings_date_format_val` | string | `''` | Custom date format value |

### Tab: Event Details (Single Event)

| Key | Type | Default | Description |
|---|---|---|---|
| `single_event_date_format_std_option` | string | `''` | Date format standard option |
| `single_event_date_format_val` | string | `''` | Custom date format value |
| `expand_venue_container` | bool | `1` | Expand venue section by default |
| `hide_weather_tab` | bool | `0` | Hide weather widget |
| `weather_unit_fahrenheit` | bool | `0` | Use Fahrenheit |
| `hide_map_tab` | bool | `0` | Hide Google Map tab |
| `hide_other_event_tab` | bool | `0` | Hide related events tab |
| `hide_age_group_section` | bool | `0` | Hide age group field |
| `hide_note_section` | bool | `0` | Hide internal notes section |
| `hide_performers_section` | bool | `0` | Hide performers on detail page |
| `hide_organizers_section` | bool | `0` | Hide organizers on detail page |
| `event_detail_image_width` | string | `''` | Detail image width |
| `event_detail_image_height` | string | `auto` | Detail image height |
| `event_detail_image_height_custom` | string | `''` | Custom height value |
| `event_detail_image_align` | string | `''` | Image alignment |
| `event_detail_image_auto_scroll` | bool | `0` | Auto-scroll gallery |
| `event_detail_image_slider_duration` | int | `4` | Gallery slide duration (seconds) |
| `event_detail_message_for_recap` | string | (template) | Message for past events |
| `event_detail_result_heading` | string | `Results` | Results section heading |
| `event_detail_result_button_label` | string | `View Results` | Results button label |

### Tab: Performers

| Key | Type | Default |
|---|---|---|
| `performer_display_view` | string | `card` |
| `performer_limit` | int | `0` (unlimited) |
| `pop_performer_limit` | int | `5` |
| `performer_no_of_columns` | int | `4` |
| `performer_load_more` | bool | `1` |
| `performer_search` | bool | `1` |
| `single_performer_show_events` | bool | `1` |
| `single_performer_event_display_view` | string | `mini-list` |
| `single_performer_event_limit` | int | `0` |
| `single_performer_event_column` | int | `4` |
| `single_performer_event_load_more` | bool | `1` |
| `single_performer_hide_past_events` | bool | `0` |
| `performer_box_color` | array | `['A6E7CF','DBEEC1','FFD3B6','FFA9A5']` |
| `single_performer_event_section_title` | string | `Upcoming Events` |

### Tab: Event Types

| Key | Type | Default |
|---|---|---|
| `type_display_view` | string | `card` |
| `type_limit` | int | `0` |
| `type_no_of_columns` | int | `4` |
| `type_load_more` | bool | `1` |
| `type_search` | bool | `1` |
| `single_type_show_events` | bool | `1` |
| `single_type_event_display_view` | string | `mini-list` |
| `single_type_event_limit` | int | `0` |
| `single_type_event_column` | int | `4` |
| `single_type_event_load_more` | bool | `1` |
| `single_type_hide_past_events` | bool | `0` |
| `type_box_color` | array | defaults |
| `single_type_event_order` | string | `asc` |
| `single_type_event_orderby` | string | `em_start_date_time` |
| `single_type_event_section_title` | string | `Upcoming Events` |

### Tab: Venues

*(Same pattern as performers/types — display view, limit, columns, load-more, search, single-venue event section settings, box colors.)*

### Tab: Organizers

*(Same pattern as performers/types.)*

### Tab: Payments

| Key | Type | Default | Description |
|---|---|---|---|
| `currency` | string | `USD` | ISO currency code |
| `currency_position` | string | `before` | Symbol position |
| `paypal_processor` | string | `''` | PayPal mode (sandbox/live) |
| `paypal_client_id` | string | `''` | PayPal client ID |
| `default_payment_processor` | string | `''` | Active gateway slug |
| `payment_order` | array | `[]` | Gateway display order |

### Tab: Emails

| Key | Description |
|---|---|
| `disable_admin_email` | Global admin email kill switch |
| `disable_frontend_email` | Global customer email kill switch |
| `ep_admin_email_to` | Admin recipient address |
| `ep_admin_email_from` | Sender address |
| `registration_email_subject` / `_content` | Registration email |
| `reset_password_mail_subject` / `_mail` | Password reset |
| `booking_pending_email_subject` / `_email` / `_cc` | Pending booking |
| `booking_confirm_email_subject` / `booking_confirmed_email` / `_cc` | Confirmed booking |
| `booking_cancelation_email_subject` / `_email` / `_cc` | Cancellation |
| `booking_refund_email_subject` / `_email` / `_cc` | Refund |
| `event_submitted_email_subject` / `_email` / `_cc` | FES submitted |
| `event_approved_email_subject` / `_email` | FES approved |
| `admin_booking_confirmed_email_subject` / `_email` / `_cc` | Admin copy of booking |
| `admin_booking_confirm_email_attendees` | Include attendees in admin email |
| `send_booking_*` flags | Toggle per-email type (8 flags) |

### Tab: Forms (Login / Register / Checkout Register)

*(See feature inventory — full field configuration per form.)*

### Tab: Frontend Submission (FES)

| Key | Type | Default | Description |
|---|---|---|---|
| `ues_default_status` | string | `draft` | Post status after submission |
| `allow_submission_by_anonymous_user` | bool | `''` | Allow guests to submit |
| `frontend_submission_roles` | array | `[]` | Allowed user roles |
| `fes_allow_media_library` | bool | `''` | Allow media library access |
| `fes_allow_user_to_delete_event` | bool | `''` | User can delete own events |
| `fes_show_add_event_in_profile` | bool | `''` | Show add-event button in profile |
| `frontend_submission_sections` | array | (all on) | Which sections appear in FES form |
| `frontend_submission_required` | array | (all off) | Which FES fields are required |
| Various message strings | string | — | Confirmation/restriction messages |

### Tab: External Integrations

| Key | Description |
|---|---|
| `gmap_api_key` | Google Maps API key |
| `weather_api_key` | OpenWeatherMap API key |
| `social_sharing` | Enable social share icons |
| `gcal_sharing` | Enable Google Calendar add link |
| `google_cal_client_id` | Google Calendar OAuth client ID |
| `google_cal_api_key` | Google Calendar API key |
| `google_recaptcha` | Enable reCAPTCHA globally |
| `google_recaptcha_site_key` | reCAPTCHA site key |
| `google_recaptcha_secret_key` | reCAPTCHA secret key |

### Tab: Custom CSS

| Key | Description |
|---|---|
| `custom_css` | Free-form CSS injected into `<head>` via `wp_head` |

### Tab: Button Labels

| Key | Description |
|---|---|
| `button_titles` | Serialised array of renamed UI labels (e.g. "Performers" → "Speakers") |

### Tab: License

*(Stores license key, status, and item IDs for each tier — free, essential, professional, business, premium+, metabundle.)*

---

## 3. Meta Boxes — `em_event` Post Type

All meta boxes are custom (default WP boxes removed: excerpt, comments, comment status, custom fields).

| Meta Box ID | Panel | Key Fields |
|---|---|---|
| `ep_event_meta_box` | Main tabbed container | Renders all sub-panels |
| Date panel | `em_start_date`, `em_end_date`, `em_start_time`, `em_end_time`, `em_start_date_time`, `em_end_date_time`, `em_add_more_dates` array |
| Booking panel | `em_enable_booking`, `em_booking_price_type`, `em_event_external_link`, `em_edit_booking_date_*` |
| Tickets panel | `em_ticket_category_data` (JSON), individual ticket fields, category assignments |
| Checkout fields panel | `em_event_checkout_attendee_fields` (JSON), fixed fields, per-event overrides |
| Recurrence panel | `em_recurrence_interval`, `em_recurrence_ends`, `em_recurrence_limit`, `em_recurrence_*` settings |
| Schedule panel | Custom schedule/agenda data |
| Social panel | `em_social_links` (serialised array) |
| Countdown panel | Countdown timer settings |
| Theme panel | `em_event_theme` (template name) |
| Other settings panel | `em_is_featured`, `em_display_front`, `em_role`, `em_created_by`, `em_status` |
| Results panel | `ep_result_start_date`, `ep_result_end_date`, results page link |
| Restrictions panel | Age restrictions, role restrictions |

---

## 4. Meta Boxes — `em_performer` Post Type

| Panel | Key Fields |
|---|---|
| Personal info | Name, bio, `em_name`, `em_performer_phones`, `em_performer_emails`, `em_performer_websites` |
| Settings | Featured flag, display settings |
| Social links | `em_social_links` (same structure as event) |

---

## 5. Meta Boxes — `em_booking` Post Type

| Meta Box | Key Content |
|---|---|
| General | Booking status, booking date, event reference |
| Tickets | Ticket breakdown, quantities, prices |
| Attendees | Per-ticket attendee data |
| Booking fields data | Submitted checkout field values |
| Notes | Admin-only internal notes |
| Booking action | Status change buttons (approve/cancel/refund) |
| Transaction log | Payment event log |

---

## 6. Custom List Table Columns

### `em_event` list

| Column | Sortable | Content |
|---|---|---|
| Event image | No | Thumbnail |
| Start date | Yes (`em_start_date_time`) | Formatted date |
| End date | No | Formatted date |
| Event type | No | Type name + color |
| Venue | No | Venue name |
| Bookings | No | Count |
| Status | No | Custom status |

### `em_booking` list

| Column | Filterable | Content |
|---|---|---|
| Booking ID | — | Post ID |
| Event | — | Event title link |
| Customer | — | Name + email |
| Tickets | — | Count |
| Total | — | Formatted price |
| Status | Yes (filter bar) | Status badge |
| Date | — | Booking date |

Filter bar: event selector, status selector. Month dropdown removed.

### `em_performer` list

| Column | Content |
|---|---|
| Image | Thumbnail |
| Email(s) | Comma-separated |
| Phone(s) | Comma-separated |

### `em_event_type` taxonomy list

| Column | Content |
|---|---|
| Color | Hex swatch |
| Image | Thumbnail |
| Event count | — |

### `em_venue` taxonomy list

| Column | Content |
|---|---|
| Image | Thumbnail |
| Address | Term meta |
| Event count | — |

### `em_event_organizer` taxonomy list

| Column | Content |
|---|---|
| Image | Thumbnail |
| Event count | — |

---

## 7. Admin Bulk Actions

| Post Type | Bulk Action | Handler |
|---|---|---|
| `em_event` | Duplicate | `ep_duplicate_event_bulk_action_handler` |
| `em_booking` | Export CSV | `ep_export_booking_bulk_action_handle` |
| `em_booking` | Export All | AJAX `booking_export_all` |

---

## 8. Admin Custom Screens

| Screen slug | Description |
|---|---|
| `ep-reports` | Bookings, payments, attendee reports with chart + list |
| `ep-shortcodes` | Shortcode reference page |
| `ep-bulk-emails` | Compose + send email to event attendees |
| `ep-extensions` | Extension marketplace listing |
| `ep-settings` | Main settings (tabbed, handled by `ep_setting_form` action) |

---

## 9. User Roles & Capabilities

Custom capability type `em_event` (maps to `edit_em_event`, `read_em_event`, `delete_em_event`, etc. via `map_meta_cap`).

Custom taxonomy capabilities (uniform across all three taxonomies):
- `manage_em_event_terms`
- `edit_em_event_terms`
- `delete_em_event_terms`
- `assign_em_event_terms`

The activator (`ep_add_custom_capabilities`) adds the full `em_event` and `em_booking` capability sets to the `administrator` role.

Capability checks in code use `manage_options` for settings pages and `current_user_can('edit_em_events')` for event editing operations.

---

## 10. Admin-Only Scripts & Styles (Enqueued)

Hooked to `admin_enqueue_scripts`; conditionally loaded on EventPrime screens.

| Handle | Type | Description |
|---|---|---|
| `ep-admin-css` | CSS | Main admin stylesheet |
| `ep-material-fonts` | CSS | Material icons font |
| `ep-admin-js` | JS | Main admin script |
| `ep-select2-js` / `-css` | JS/CSS | Select2 dropdown library |
| `ep-flatpickr-js` / `-css` | JS/CSS | Date picker |
| `ep-chart-js` | JS | Chart.js for reports |
| `ep-admin-toast-js` / `-css` | JS/CSS | Toast notification |
| `ep-acf-timepicker` | JS | Deregistered (priority 999) to avoid ACF conflicts |

---

## 11. Admin Notices

| Trigger | Condition | Type |
|---|---|---|
| Update notice | Plugin update available | Info |
| Dismissible notices | Various setup prompts | Info/warning |
| Plugin deactivation feedback form | Modal on deactivation | — |
| DB migration notice | When `ep_db_need_to_run_migration` flag set | Warning |
