<?php
/**
 * Plugin Name: Resume
 * Description: Example block scaffolded with Create Block tool.
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       resume
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Load dependencies
require_once plugin_dir_path(__FILE__) . 'includes/repositories/DatabaseManager.php';
require_once plugin_dir_path(__FILE__) . 'includes/repositories/ResumeRepository.php';
require_once plugin_dir_path(__FILE__) . 'includes/api/ResumeAPI.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/ResumeShortcode.php';

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_resume_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_resume_block_init' );

// Register activation/deactivation hooks
register_activation_hook(__FILE__, ['DatabaseManager', 'createDatabase']);
// register_deactivation_hook(__FILE__, ['DatabaseManager', 'teardownDatabase']);

// Register styles
function enqueue_resume_styles() {
    wp_enqueue_style(
        'spenpo-resume-styles',
        plugins_url('style.css', __FILE__),
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_resume_styles');
add_action('init', ['\Spenpo\Resume\API\ResumeAPI', 'registerRoutes']); 

// Define plugin constants
define('SPENPO_RESUME_VERSION', '1.0.0');
define('SPENPO_RESUME_MINIMUM_WP_VERSION', '6.6');
define('SPENPO_RESUME_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SPENPO_RESUME_PLUGIN_URL', plugin_dir_url(__FILE__));

// Version compatibility check
function spenpo_resume_compatibility_check() {
    global $wp_version;
    
    if (version_compare($wp_version, SPENPO_RESUME_MINIMUM_WP_VERSION, '<')) {
        deactivate_plugins(basename(__FILE__));
        wp_die(
            sprintf(
                'This plugin requires WordPress version %s or higher. You are running version %s.',
                SPENPO_RESUME_MINIMUM_WP_VERSION,
                $wp_version
            )
        );
    }
}

register_activation_hook(__FILE__, 'spenpo_resume_compatibility_check');

// Add this to your plugin's initialization
add_action('admin_init', function() {
    register_setting('your_plugin_settings', 'resume_api_require_auth');
    
    add_settings_section(
        'resume_api_settings',
        'Resume API Settings',
        null,
        'your_plugin_settings'
    );
    
    add_settings_field(
        'resume_api_require_auth',
        'Require Authentication',
        function() {
            $value = get_option('resume_api_require_auth', false);
            echo '<input type="checkbox" name="resume_api_require_auth" value="1" ' . checked(1, $value, false) . '/>';
            echo '<p class="description">If checked, API requests will require authentication via nonce.</p>';
        },
        'your_plugin_settings',
        'resume_api_settings'
    );
}); 

// Add admin menu and settings page
add_action('admin_menu', function() {
    add_options_page(
        'Resume Plugin Settings',    // Page title
        'Resume Settings',          // Menu title
        'manage_options',           // Capability required
        'resume-settings',          // Menu slug
        function() {                // Callback function to display the page
            ?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <form action="options.php" method="post">
                    <?php
                    settings_fields('resume_plugin_settings');
                    do_settings_sections('resume_plugin_settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
    );
});

// Register settings
add_action('admin_init', function() {
    register_setting('resume_plugin_settings', 'resume_api_require_auth');
    
    add_settings_section(
        'resume_api_settings',
        'API Settings',
        null,
        'resume_plugin_settings'
    );
    
    add_settings_field(
        'resume_api_require_auth',
        'Require Authentication',
        function() {
            $value = get_option('resume_api_require_auth', false);
            echo '<input type="checkbox" name="resume_api_require_auth" value="1" ' . checked(1, $value, false) . '/>';
            echo '<p class="description">If checked, API requests will require authentication via nonce.</p>';
        },
        'resume_plugin_settings',
        'resume_api_settings'
    );
});


