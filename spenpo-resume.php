<?php
/**
 * Plugin Name:       Spenpo Resume
 * Plugin URI:        https://github.com/spope851/spenpo-resume
 * Description:       store, serve, and display your resume data
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           1.0.4
 * Author:            spenpo
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       spenpo-resume
 *
 * @package spenpo-resume
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants - only if not already defined (for testing compatibility)
if (!defined('SPCV_PATH')) {
    define('SPCV_PATH', plugin_dir_path(__FILE__));
}
if (!defined('SPCV_URL')) {
    define('SPCV_URL', plugin_dir_url(__FILE__));
}

// Include the SpcvDatabaseManager class
require_once SPCV_PATH . 'includes/repositories/SpcvDatabaseManager.php';

// Register activation hook with full namespace
register_activation_hook(__FILE__, ['SPCV\Repositories\SpcvDatabaseManager', 'createDatabase']);

// Register deactivation hook with full namespace
register_deactivation_hook(__FILE__, ['SPCV\Repositories\SpcvDatabaseManager', 'teardownDatabase']);

// Load dependencies
require_once SPCV_PATH . 'includes/repositories/SpcvResumeRepository.php';
require_once SPCV_PATH . 'includes/api/SpcvResumeAPI.php';
require_once SPCV_PATH . 'includes/shortcodes/SpcvResumeShortcode.php';

// Register styles
function spcv_enqueue_styles() {
    wp_enqueue_style(
        'spcv-styles',
        plugins_url('style.css', __FILE__),
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'spcv_enqueue_styles');
add_action('init', ['SPCV\API\SpcvResumeAPI', 'registerRoutes']); 

// Move these constant definitions up and add checks
if (!defined('SPCV_VERSION')) {
    define('SPCV_VERSION', '1.0.4');
}
if (!defined('SPCV_MINIMUM_WP_VERSION')) {
    define('SPCV_MINIMUM_WP_VERSION', '6.6');
}
if (!defined('SPCV_PLUGIN_DIR')) {
    define('SPCV_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('SPCV_PLUGIN_URL')) {
    define('SPCV_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Version compatibility check
function spcv_compatibility_check() {
    global $wp_version;
    
    if (version_compare($wp_version, SPCV_MINIMUM_WP_VERSION, '<')) {
        throw new WP_Error(
            'plugin_dependency_error',
            sprintf(
                esc_html__(
                    'This plugin requires WordPress version %s or higher. You are running version %s.',
                    'spenpo-resume'
                ),
                SPCV_MINIMUM_WP_VERSION,
                $wp_version
            )
        );
    }
}

register_activation_hook(__FILE__, 'spcv_compatibility_check');

// Add admin menu and settings page
add_action('admin_menu', function() {
    add_options_page(
        'Spenpo Resume Plugin Settings',    // Page title
        'Spenpo Resume',          // Menu title
        'manage_options',           // Capability required
        'spcv_settings',          // Menu slug
        function() {                // Callback function to display the page
            ?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <form action="options.php" method="post">
                    <?php
                    settings_fields('spcv_settings');
                    wp_nonce_field( 'spcv_api_require_auth', 'spcv_require_auth_nonce' ); // Add a custom nonce.
                    do_settings_sections('spcv_settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
    );
});

function spcv_sanitize_api_require_auth( $input ) {
    return boolval( $input );
}

// Register settings
add_action('admin_init', function() {
    register_setting('spcv_settings', 'spcv_api_require_auth', array(
        'sanitize_callback' => 'spcv_sanitize_api_require_auth'
    ));
    
    add_settings_section(
        'spcv_api_settings',
        'API Settings',
        null,
        'spcv_settings'
    );
    
    add_settings_field(
        'spcv_api_require_auth',
        'Require Authentication',
        function() {
            $value = get_option('spcv_api_require_auth', false);
            echo '<input type="checkbox" name="spcv_api_require_auth" value="1" ' . checked(1, $value, false) . '/>';
            echo '<p class="description">If checked, API requests will require authentication via nonce.</p>';
        },
        'spcv_settings',
        'spcv_api_settings'
    );
});
