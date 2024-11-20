<?php
// Load test doubles first
require_once __DIR__ . '/TestDoubles/WPTestDoubles.php';

// Define that we're running PHPUnit
define('PHPUNIT_RUNNING', true);

// Load Composer's autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load WP function stubs
require_once __DIR__ . '/wp-functions.php';

// Initialize WP_Mock
\WP_Mock::bootstrap();

// Define WordPress constants if needed
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

require_once dirname(__DIR__) . '/includes/repositories/ResumeRepository.php';
require_once dirname(__DIR__) . '/includes/shortcodes/ResumeShortcode.php';