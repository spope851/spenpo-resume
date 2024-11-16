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
register_deactivation_hook(__FILE__, ['DatabaseManager', 'teardownDatabase']);

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


