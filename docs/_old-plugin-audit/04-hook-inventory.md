# EventPrime — Hook Inventory

**Source plugin:** `eventprime-event-calendar-management` v4.0.9.7  
**Audit date:** 2026-03-21  
**Status:** 🟢 Complete

---

## Legend

- **WP hook** — standard WordPress hook
- **EP hook** — custom hook added by this plugin
- `(nopriv)` — available to non-logged-in users

---

## 1. `add_action` — WordPress Core Hooks

### Plugin Bootstrap

| Hook | Callback | Priority | Class |
|---|---|---|---|
| `plugins_loaded` | `load_plugin_textdomain` | 10 | `i18n` |
| `plugins_loaded` | `ep_on_plugins_loaded` (DB upgrade check) | 10 | Main |

### `init`

| Callback | Priority | Class | Purpose |
|---|---|---|---|
| `register_taxonomies` | 5 | Admin | Register em_event_type, em_venue, em_event_organizer |
| `register_post_types` | 4 | Admin | Register em_event, em_performer, em_booking |
| `register_post_status` | 9 | Admin | Register custom statuses |
| `remove_defult_fields` | 99 | Admin | Remove default editor fields |
| `register_shortcodes` | 10 | Public | Register all [em_*] shortcodes |
| `create_block_ep_blocks_block_init` | 10 | Blocks | Register Gutenberg blocks |
| `eventprime_block_register` | 10 | Blocks | Secondary block registration |
| `ep_apply_slug_rules` | 10 | Public | Apply SEO URL rewrite rules |
| `get_ical_file` | 9999 | Public | iCal download handler |

### Admin Hooks

| Hook | Callback | Priority | Class |
|---|---|---|---|
| `admin_enqueue_scripts` | `deregister_acf_timepicker_on_custom_post` | 999 | Admin |
| `admin_enqueue_scripts` | `enqueue_styles` | 10 | Admin |
| `admin_enqueue_scripts` | `enqueue_scripts` | 10 | Admin |
| `admin_menu` | `ep_admin_menus` | 10 | Admin |
| `admin_init` | `plugin_redirect` | 10 | Admin |
| `admin_notices` | `ep_print_notices` | 10 | Admin |
| `admin_notices` | `ep_dismissible_notice` | 10 | Admin |
| `admin_post_ep_setting_form` | `ep_setting_form_submit` | 10 | Admin |
| `admin_head-edit.php` | `ep_add_booking_export_btn` | 10 | Admin |
| `admin_footer` | `add_eventprime_admin_footer_banner` | 100 | Admin |
| `admin_footer` | `ep_deactivation_feedback_form` | 10 | Admin |

### Meta Boxes

| Hook | Callback | Priority | Class |
|---|---|---|---|
| `add_meta_boxes` | `ep_event_remove_meta_boxes` | 10 | Admin |
| `add_meta_boxes` | `ep_event_register_meta_boxes` | 1 | Admin |
| `add_meta_boxes` | `ep_performer_remove_meta_boxes` | 10 | Admin |
| `add_meta_boxes` | `ep_performer_register_meta_boxes` | 1 | Admin |
| `add_meta_boxes` | `ep_bookings_remove_meta_boxes` | 10 | Admin |
| `add_meta_boxes` | `ep_bookings_register_meta_boxes` | 1 | Admin |
| `save_post` | `ep_save_meta_boxes` | 1 | Admin |
| `save_post` | `ep_save_event_meta_boxes` | 1 | Admin |
| `save_post` | `allow_single_term_selection` | 10 | Admin |

### Taxonomy Hooks

| Hook | Callback | Class |
|---|---|---|
| `em_event_type_add_form_fields` | `add_event_type_fields` | Admin |
| `em_event_type_edit_form_fields` | `edit_event_type_fields` | Admin |
| `created_em_event_type` | `em_create_event_type_data` | Admin |
| `edited_em_event_type` | `em_create_event_type_data` | Admin |
| `em_venue_add_form_fields` | `add_event_venue_fields` | Admin |
| `em_venue_edit_form_fields` | `edit_event_venue_fields` | Admin |
| `created_em_venue` | `em_create_event_venue_data` | Admin |
| `edited_em_venue` | `em_create_event_venue_data` | Admin |
| `em_event_organizer_add_form_fields` | `add_event_organizer_fields` | Admin |
| `em_event_organizer_edit_form_fields` | `edit_event_organizer_fields` | Admin |
| `created_em_event_organizer` | `em_create_event_organizer_data` | Admin |
| `edited_em_event_organizer` | `em_edit_event_organizer_data` | Admin |

### Post Lifecycle

| Hook | Callback | Priority | Class |
|---|---|---|---|
| `before_delete_post` | `ep_before_delete_event_bookings` | 99 | Admin |
| `transition_post_status` | `ep_frontend_event_publish` | 10 | Public |
| `pre_get_posts` | `ep_sort_events_date` | 10 | Admin |
| `pre_get_posts` | `ep_modify_taxonomy_archive_query` | 99999999 | Public |

### Frontend

| Hook | Callback | Priority | Class |
|---|---|---|---|
| `wp_enqueue_scripts` | `enqueue_styles` | 10 | Public |
| `wp_enqueue_scripts` | `enqueue_scripts` | 10 | Public |
| `wp_head` | `ep_custom_styles` | 100 | Public |
| `body_class` | `ep_add_body_class` | 1 | Public |
| `template_redirect` | `ep_remove_post_navigation_action` | 10 | Public |

### Widgets

| Hook | Callback | Widget Class |
|---|---|---|
| `widgets_init` | `em_load_calendar_widget` (priority 0) | Event_Calendar |
| `widgets_init` | `em_load_event_countdown` | Event_Countdown |
| `widgets_init` | `em_load_slider_widget` | Event_Slider |
| `widgets_init` | `em_load_featured_organizer` | Featured_Event_Organizers |
| `widgets_init` | `em_load_featured_performer` | Featured_Event_Performers |
| `widgets_init` | `em_load_featured_type` | Featured_Event_Types |
| `widgets_init` | `em_load_featured_venue` | Featured_Event_Venues |
| `widgets_init` | `em_load_popular_organizer` | Popular_Event_Organizers |
| `widgets_init` | `em_load_popular_performer` | Popular_Event_Performers |
| `widgets_init` | `em_load_popular_type` | Popular_Event_Types |
| `widgets_init` | `em_load_popular_venue` | Popular_Event_Venues |

### REST API

| Hook | Callback | Class |
|---|---|---|
| `rest_api_init` | `ep_register_rest_route` | Blocks |

---

## 2. `add_filter` — WordPress Core Filters

| Hook | Callback | Priority | Class |
|---|---|---|---|
| `the_content` | `ep_load_single_template_dynamic` | 1000000000 | Public |
| `post_thumbnail_html` | `remove_thumbnail_on_event_post_type` | 10 | Public |
| `bulk_post_updated_messages` | `ep_bulk_post_updated_messages_filter` | 10 | Public |
| `get_the_post_navigation` | `ep_remove_post_navigation` | 10 | Public |
| `query_vars` | `ep_filter_query_vars` | 10 | Public |
| `logout_redirect` | `ep_handle_logout_redirect` | 10 | Public |
| `parent_file` | `admin_menu_separator` | 10 | Admin |
| `tag_row_actions` | `ep_add_custom_taxonomy_view_link` | 10 | Admin |
| `post_row_actions` | `ep_remove_actions` | 10 | Admin |
| `manage_em_performer_posts_columns` | `ep_performer_posts_columns` | 1 | Admin |
| `manage_em_booking_posts_columns` | `ep_filter_booking_columns` | 10 | Admin |
| `manage_em_event_posts_columns` | `ep_filter_event_columns` | 10 | Admin |
| `manage_edit-em_event_sortable_columns` | `ep_sortable_event_columns` | 10 | Admin |
| `manage_edit-em_event_type_columns` | `add_event_type_custom_columns` | 10 | Admin |
| `manage_edit-em_venue_columns` | `add_venue_custom_columns` | 10 | Admin |
| `manage_edit-em_event_organizer_columns` | `add_event_organizer_custom_columns` | 10 | Admin |
| `manage_em_event_type_custom_column` | `add_event_type_custom_column` | 10 | Admin |
| `manage_em_venue_custom_column` | `add_venue_custom_column` | 10 | Admin |
| `manage_em_event_organizer_custom_column` | `add_event_organizer_custom_column` | 10 | Admin |
| `bulk_actions-edit-em_event` | `ep_register_duplicate_event_actions` | 10 | Admin |
| `handle_bulk_actions-edit-em_event` | `ep_duplicate_event_bulk_action_handler` | 10 | Admin |
| `bulk_actions-edit-em_booking` | `ep_export_booking_bulk_list` | 10 | Admin |
| `handle_bulk_actions-edit-em_booking` | `ep_export_booking_bulk_action_handle` | 10 | Admin |
| `months_dropdown_results` | `ep_booking_filters_remove_date` | 10 | Admin |
| `parse_query` | `ep_booking_filters_argu` | 10 | Admin |
| `parse_query` | `ep_events_filters_arguments` | 100 | Admin |
| `in_plugin_update_message-{plugin_base}` | `ep_in_plugin_update_message` | 10 | Admin |

---

## 3. AJAX Hooks (`wp_ajax_` / `wp_ajax_nopriv_`)

All AJAX actions are prefixed `ep_`. The table below uses the unprefixed action name.

| Action | Auth Only | nopriv | Handler Method |
|---|---|---|---|
| `save_checkout_field` | ✅ | ❌ | `save_checkout_field` |
| `delete_checkout_field` | ✅ | ❌ | `delete_checkout_field` |
| `submit_payment_setting` | ✅ | ❌ | `submit_payment_setting` |
| `load_more_events` | ✅ | ✅ | `load_more_events` |
| `submit_login_form` | ✅ | ✅ | `submit_login_form` |
| `submit_register_form` | ✅ | ✅ | `submit_register_form` |
| `load_more_event_types` | ✅ | ✅ | `load_more_event_types` |
| `load_more_event_performer` | ✅ | ✅ | `load_more_event_performer` |
| `load_more_event_venue` | ✅ | ✅ | `load_more_event_venue` |
| `load_more_event_organizer` | ✅ | ✅ | `load_more_event_organizer` |
| `load_event_single_page` | ✅ | ✅ | `load_event_single_page` |
| `save_event_booking` | ✅ | ✅ | `save_event_booking` |
| `booking_timer_complete` | ✅ | ✅ | `booking_timer_complete` |
| `paypal_sbpr` | ✅ | ✅ | `paypal_sbpr` |
| `event_booking_cancel` | ✅ | ❌ | `event_booking_cancel` |
| `booking_add_notes` | ✅ | ❌ | `booking_add_notes` |
| `booking_update_status` | ✅ | ❌ | `booking_update_status` |
| `event_wishlist_action` | ✅ | ✅ | `event_wishlist_action` |
| `save_frontend_event_submission` | ✅ | ✅ | `save_frontend_event_submission` |
| `load_event_dates` | ✅ | ✅ | `load_event_dates` |
| `load_more_upcomingevent_performer` | ✅ | ✅ | `load_more_upcomingevent_performer` |
| `load_more_upcomingevent_venue` | ✅ | ✅ | `load_more_upcomingevent_venue` |
| `load_more_upcomingevent_organizer` | ✅ | ✅ | `load_more_upcomingevent_organizer` |
| `load_more_upcomingevent_eventtype` | ✅ | ✅ | `load_more_upcomingevent_eventtype` |
| `filter_event_data` | ✅ | ✅ | `filter_event_data` |
| `load_event_offers_date` | ✅ | ✅ | `load_event_offers_date` |
| `update_user_timezone` | ✅ | ✅ | `update_user_timezone` |
| `validate_user_details_booking` | ✅ | ✅ | `validate_user_details_booking` |
| `get_attendees_email_by_event_id` | ✅ | ❌ | `get_attendees_email_by_event_id` |
| `send_attendees_email` | ✅ | ❌ | `send_attendees_email` |
| `upload_file_media` | ✅ | ✅ | `upload_file_media` |
| `rg_check_user_name` | ✅ | ✅ | `rg_check_user_name` |
| `rg_check_email` | ✅ | ✅ | `rg_check_email` |
| `export_submittion_attendees` | ✅ | ✅ | `export_submittion_attendees` |
| `eventprime_run_migration` | ✅ | ❌ | `eventprime_run_migration` |
| `eventprime_cancel_migration` | ✅ | ❌ | `eventprime_cancel_migration` |
| `reload_checkout_user_section` | ✅ | ❌ | `reload_checkout_user_section` |
| `eventprime_reports_filter` | ✅ | ❌ | `eventprime_reports_filter` |
| `set_default_payment_processor` | ✅ | ❌ | `set_default_payment_processor` |
| `booking_export_all` | ✅ | ❌ | `booking_export_all` |
| `calendar_event_create` | ✅ | ❌ | `calendar_event_create` |
| `calendar_events_drag_event_date` | ✅ | ❌ | `calendar_events_drag_event_date` |
| `calendar_events_delete` | ✅ | ❌ | `calendar_events_delete` |
| `eventprime_activate_license` | ✅ | ❌ | `eventprime_activate_license` |
| `eventprime_deactivate_license` | ✅ | ❌ | `eventprime_deactivate_license` |
| `update_event_booking_action` | ✅ | ❌ | `update_event_booking_action` |
| `event_print_all_attendees` | ✅ | ❌ | `event_print_all_attendees` |
| `load_edit_booking_attendee_data` | ✅ | ❌ | `load_edit_booking_attendee_data` |
| `sanitize_input_field_data` | ✅ | ✅ | `sanitize_input_field_data` |
| `send_plugin_deactivation_feedback` | ✅ | ❌ | `send_plugin_deactivation_feedback` |
| `delete_user_fes_event` | ✅ | ❌ | `delete_user_fes_event` |
| `cancel_current_booking_process` | ✅ | ✅ | `cancel_current_booking_process` |
| `edit_booking_attendee_data_save` | ✅ | ❌ | `edit_booking_attendee_data_save` |
| `get_calendar_event` | ✅ | ✅ | `get_calendar_event` |
| `check_offer_applied` | ✅ | ✅ | `check_offer_applied` |
| `update_tickets_data` | ✅ | ✅ | `update_tickets_data` |
| `ep_dismissible_notice` (admin) | ✅ | ❌ | `ep_dismissible_notice_ajax` |

> **Total AJAX endpoints:** 52 (28 public + nopriv, 24 admin/auth-only)

---

## 4. Custom Hooks Exposed by the Plugin (`do_action` / `apply_filters`)

These are the extension points the plugin offers to child themes, extensions, and third-party code.

### Actions

| Hook | Args | Where Fired | Purpose |
|---|---|---|---|
| `eventprime_register_taxonomy` | — | Before taxonomy registration | Extensions can register additional taxonomies in sequence |
| `eventprime_after_register_taxonomy` | — | After taxonomy registration | |
| `eventprime_register_post_type` | — | Before CPT registration | |
| `eventprime_after_register_post_type` | — | After CPT registration | |
| `ep_add_custom_banner` | — | Various admin footer positions | Inject premium upsell banner |
| `ep_add_custom_support_text` | — | Admin footer | |
| `ep_setting_submit_button` | — | Settings form | Render submit button area |
| `ep_reports_tabs_content` | `$tab` | Reports page | Render per-tab content |
| `ep_bookings_report_stat` | — | Reports bookings tab | Render stat summary |
| `ep_bookings_report_bookings_list` | `$data` | Reports bookings tab | Render booking list |
| `ep_booking_reports_booking_list_load_more` | `$data` | Reports load-more | |
| `ep_event_view_event_booking_button` | `$event` | Event list/card | Render booking button |
| `ep_events_list_before_render_content` | — | Before event list renders | Inject content above list |
| `ep_event_view_wishlist_icon` | `$event`, `$args` | Event card | Render wishlist icon |
| `ep_event_view_social_sharing_icon` | `$event`, `$args` | Event detail | Render social share icons |
| `ep_event_view_event_dates` | `$event`, `$args` | Event card | Render date display |
| `ep_event_view_event_price` | `$event`, `$args` | Event card | Render price |
| `ep_event_detail_weather_data` | `$event` | Event detail page | Render weather widget |
| `ep_events_booking_count_slider` | `$event` | Slider view | Render booking count |
| `ep_event_detail_right_event_dates_section` | — | Event detail sidebar | Render date section |
| `ep_dequeue_event_scripts` | — | Conditional | Dequeue scripts on non-EP pages |
| `ep_add_loader_section` | — | Loading states | Render full-page loader |
| `ep_add_internal_loader_section` | `$type`, `$args` | Inline loading | Render inline loader |
| `ep_event_view_calendar_icon` | `$event`, `$args` | Event card | Render add-to-calendar icon |
| `ep_event_booking_event_total` | `$event`, `$tickets`, `$total`, `$args` | Checkout | Render total price block |
| `eventprime_before_{template_name}` | `$template_name`, `$atts` | Before any template | Template lifecycle hook |
| `eventprime_after_{template_name}` | — | After any template | Template lifecycle hook |
| `em_after_organizer_created` | `$term_id`, `$data` | After organizer save | |

### Filters

| Hook | Args | Default Returns | Purpose |
|---|---|---|---|
| `ep_payments_gateways_list` | `$gateways` | `[]` | Register payment gateways |
| `em_cpt_event` | `$post_type` | `em_event` | Override event post type slug |
| `ep_add_pages_options` | `$pages` | default pages array | Add custom page mappings |
| `ep_add_emailer_options` | `$email_options` | — | Add email settings (used by extensions) |
| `ep_add_general_options` | `$general_options` | — | Add general settings |
| `ep_performers_options` | `$options` | — | Modify performer display defaults |
| `ep_venues_options` | `$options` | — | Modify venue display defaults |
| `ep_organizers_options` | `$options` | — | Modify organizer display defaults |
| `ep_add_global_setting_options` | `$settings`, `$raw` | `$settings` | Modify merged settings object |
| `ep_get_template_part` | `$file`, `$slug`, `$name` | file path | Override any template file |
| `ep_filter_front_events` | `$posts`, `$atts` | query results | Modify events shown in listing |
| `ep_filter_event_all_tickets_data` | `$tickets`, `$event` | ticket array | Modify tickets shown |
| `ep_events_render_attribute_data` | `$params`, `$atts` | shortcode params | Modify shortcode render params |
| `ep_filter_load_event_common_options_events_data_obj` | `$events_data`, `$atts` | data object | Modify the full events data object |
| `ep_filter_frontend_event_submission_options` | `$fes_data`, `$atts` | FES config | Modify FES form options |
| `{shortcode}_shortcode` | `$shortcode` | shortcode tag | Override any shortcode tag name |
| `ep_extend_global_exclude_fields` | `$exclude_fields`, `$options` | field list | Exclude settings fields from processing |
| `ep_check_event_ticket_applied_offers` | `$applied`, `$offer`, `$ticket`, `$event_id`, `$qty` | bool | Override offer application logic |
| `ep_checkout_fields_options` | `$field_types` | array | Add custom checkout field types |
| `ep_settings_language_labels` | `$labelsections` | array | Add/modify label section keys |
| `ep_settings_language_buttons` | `$buttonsections` | array | Add/modify button label sections |
| `ep_event_views` | `$event_views` | views array | Register additional frontend views |
| `ep_perfomers_url_modify` | `$url`, `$performer_id` | permalink | Override performer URL |
| `ep_booking_qr_code_url` | `$url`, `$booking` | QR data string | Override QR code content |
| `ep_extend_buy_ticket_text` | `$text`, `$tickets`, `$event` | string | Override "Buy Ticket" label |
| `ep_update_new_data_before_validating_cart` | `$newdata`, `$data` | cart data | Modify cart before validation |
| `ep_filter_booking_attendee_field_labels` | `$labels`, `$attendees` | labels array | Modify attendee field labels |
| `ep_performers_render_argument` | `$args`, `$input` | query args | Modify performer query args |
| `ep_check_ticket_visibility_response` | `$response`, `$ticket`, `$event` | response object | Override ticket visibility |
| `ep_update_remaining_capacity` | `$remaining`, `$event`, `$ticket` | int | Override remaining capacity |
| `ep_is_payment_gayway_enabled` | `$processor` | processor name | Check/override gateway availability |
| `ep_filter_event_booking_by_user` | (used in booking check) | — | Customise booking deduplication |
| `event_magic_booking_get_final_price` | `$price`, `$order_info` | decimal | Override final checkout price |

---

## 5. REST API Endpoints

| Namespace | Route | Method | Callback | Auth |
|---|---|---|---|---|
| `eventprime/v1` | `/events` | GET | `ep_load_events` | None (always returns true) |

> **Note:** No nonce or auth check on this endpoint. Anyone can query it. Rebuild must add proper permission callbacks.

---

## 6. Notable Hook Issues for Rebuild

| Issue | Location | Recommendation |
|---|---|---|
| `the_content` filter at priority `1000000000` | Public class | Use `template_include` instead |
| `flush_rewrite_rules()` inside `register_post_types()` (runs on every `init`) | Admin class | Move to `register_activation_hook` only |
| Inline `<script>` injected via `admin_footer-edit.php` anonymous closure | Main class `define_admin_hooks()` | Move to enqueued JS asset |
| Multiple `load-edit-tags.php` anonymous closures registered inline | Main class | Move to named callbacks |
| REST endpoint has no authentication | Blocks class | Add nonce or capability check |
| AJAX handler `sanitize_input_field_data` is publicly accessible (nopriv) | AJAX class | Verify this is intentional |
| `ep_add_custom_banner` and upsell actions are wired into core bootstrap | Main class | Remove entirely in rebuild |
