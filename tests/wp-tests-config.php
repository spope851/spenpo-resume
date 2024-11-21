<?php
/* Path to the WordPress codebase you'd like to test */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__DIR__)))) . '/');
}

/* Test database settings */
if (!defined('DB_NAME')) {
    define('DB_NAME', 'wordpress_test');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', 'password');
}
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost:8080');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8');
}
if (!defined('DB_COLLATE')) {
    define('DB_COLLATE', '');
}

/* WordPress test suite settings */
if (!defined('WP_TESTS_DOMAIN')) {
    define('WP_TESTS_DOMAIN', 'localhost');
}
if (!defined('WP_TESTS_EMAIL')) {
    define('WP_TESTS_EMAIL', 'admin@example.org');
}
if (!defined('WP_TESTS_TITLE')) {
    define('WP_TESTS_TITLE', 'Test Blog');
}
if (!defined('WP_TESTS_NETWORK_TITLE')) {
    define('WP_TESTS_NETWORK_TITLE', 'Test Network');
}
if (!defined('WP_PHP_BINARY')) {
    define('WP_PHP_BINARY', 'php');
} 