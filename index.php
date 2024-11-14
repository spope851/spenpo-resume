<?php
/**
 * Plugin Name:       Resume
 * Description:       Example block scaffolded with Create Block tool.
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

// Function to output content for a shortcode
function my_custom_shortcode() {
    return '<p>Hello, this is my custom plugin shortcode!</p>';
};

add_shortcode('my_shortcode', 'my_custom_shortcode');

add_action( 'rest_api_init', function () {
    register_rest_route( 'spenpo/v1', '/posts/(?P<id>\d+)/blocks', array(
        'methods' => 'GET',
        'callback' => 'my_awesome_func',
        // 'permission_callback' => function () {
        //   return current_user_can( 'edit_others_posts' );
        // },
        'permission_callback' => '__return_true',
    ) );
} );

function my_awesome_func( $data ) {
    $post_content = get_post_field('post_content', $data['id']);
    $blocks = parse_blocks($post_content);

    if ( empty( $blocks ) ) {
        return new WP_Error( 'no_blocks', 'Invalid post', array( 'status' => 404 ) );
    }

    return $blocks;
};

function executeScript(string $scriptPath): array {
    global $wpdb;
    
    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    
    if (!file_exists($scriptPath)) {
        error_log("SQL file not found at: " . $scriptPath);
        return [
            'success' => false,
            'error' => "SQL file not found: {$scriptPath}"
        ];
    }

    try {
        $sql = file_get_contents($scriptPath);
        if ($sql === false) {
            error_log("Failed to read SQL file");
            return [
                'success' => false,
                'error' => "Failed to read SQL file"
            ];
        }

        $sql = str_replace('{$wpdb->prefix}', $wpdb->prefix, $sql);
        
        error_log("SQL content after prefix replacement: " . $sql);

        $statements = array_filter(
            array_map(
                'trim',
                explode(';', $sql)
            ),
            'strlen'
        );

        foreach ($statements as $statement) {
            error_log("Executing statement: " . $statement);
            $result = dbDelta($statement);
            error_log("dbDelta result: " . print_r($result, true));
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
        }

        return [
            'success' => true,
            'message' => "Script executed successfully"
        ];

    } catch (Exception $e) {
        error_log("Error in executeScript: " . $e->getMessage());
        return [
            'success' => false,
            'error' => "Error executing script: " . $e->getMessage()
        ];
    }
}

// Create a Database
function database_creation() {
    $script_path = plugin_dir_path(__FILE__) . 'data/model.sql';
    executeScript($script_path);
}

register_activation_hook(__FILE__, 'database_creation');


