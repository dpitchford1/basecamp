<?php
/**
 * SEO — Master loader for Basecamp theme.
 *
 * Loads and boots all SEO modules. Each module defers automatically
 * to Yoast SEO or Rank Math if either plugin is active.
 *
 * @package basecamp
 */

require_once __DIR__ . '/basecamp-title-functions.php';
require_once __DIR__ . '/basecamp-meta-description-functions.php';
require_once __DIR__ . '/basecamp-social-meta-functions.php';
require_once __DIR__ . '/class-basecamp-schema.php';

Basecamp_Schema::init();
