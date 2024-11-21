<?php
// Determine test type from PHPUnit group annotation
$is_integration = in_array('integration', $_SERVER['argv']);

if ($is_integration) {
    require_once __DIR__ . '/wp-tests-config.php';
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    require_once dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
    
    $wp_tests_dir = dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';
    
    // Load functions first so we can use tests_add_filter
    require_once $wp_tests_dir . '/includes/functions.php';
    
    // Load the rest of WP test framework
    require_once $wp_tests_dir . '/includes/bootstrap.php';
    
    tests_add_filter('muplugins_loaded', function() {
        require dirname(__DIR__) . '/index.php';
    });
} else {
    // Unit test setup - unchanged
    require_once __DIR__ . '/TestDoubles/WPTestDoubles.php';
    define('PHPUNIT_RUNNING', true);
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    require_once __DIR__ . '/wp-functions.php';
    \WP_Mock::bootstrap();
    
    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(__DIR__) . '/');
    }
}

// Common includes
require_once dirname(__DIR__) . '/includes/repositories/ResumeRepository.php';
require_once dirname(__DIR__) . '/includes/shortcodes/ResumeShortcode.php';