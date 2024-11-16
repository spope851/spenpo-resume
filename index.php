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
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_sections';
    
    // Get all sections with their content in a single query based on content_type
    $text_sections = $wpdb->get_results("
        SELECT s.*, t.id as content_id, t.label, t.text 
        FROM {$table_name} s
        LEFT JOIN {$wpdb->prefix}resume_section_text_content t ON s.id = t.section_id
        WHERE s.content_type = 'text'
    ");

    $list_sections = $wpdb->get_results("
        SELECT s.*, i.id as item_id, i.text, i.link, i.year, i.year_link
        FROM {$table_name} s
        LEFT JOIN {$wpdb->prefix}resume_section_items i ON s.id = i.section_id
        WHERE s.content_type = 'list'
    ");

    $nested_sections = $wpdb->get_results("
        SELECT s.*, n.id as nested_id, n.title as nested_title, n.link_title, n.href, n.sub_title,
               d.id as detail_id, d.title as detail_title, d.sub_title as detail_sub_title, d.text as detail_text, d.indent
        FROM {$table_name} s
        LEFT JOIN {$wpdb->prefix}resume_nested_sections n ON s.id = n.section_id
        LEFT JOIN {$wpdb->prefix}resume_nested_section_details d ON n.id = d.nested_section_id
        WHERE s.content_type = 'nested'
        ORDER BY d.id, n.id
    ");

    // Group results by section_id for easier processing
    $grouped_text = array_reduce($text_sections, function($acc, $item) {
        $acc[$item->id][] = $item;
        return $acc;
    }, []);

    $grouped_list = array_reduce($list_sections, function($acc, $item) {
        $acc[$item->id][] = $item;
        return $acc;
    }, []);

    $grouped_nested = array_reduce($nested_sections, function($acc, $item) {
        $acc[$item->id][] = $item;
        return $acc;
    }, []);

    $results = $wpdb->get_results("SELECT * FROM $table_name");
    $dom = new DOMDocument('1.0', 'utf-8');
    
    foreach ($results as $section) {
        // Generate HTML using DOMDocument
        $section_div = $dom->createElement('div');

        // section
        $section_div->setAttribute('class', "spenpo-resume-section-$section->id");

        // title
        $section_title = $dom->createElement('p');
        $section_title->setAttribute('class', "spenpo-resume-section-title-$section->id");
        $section_title_text = $dom->createTextNode($section->title);
        $section_title->appendChild($section_title_text);
        $section_div->appendChild($section_title);

        // content
        $section_content = $dom->createElement('div');
        $section_content->setAttribute('class', "spenpo-resume-section-content-$section->id");

        // get content
        if ($section->content_type === 'text') {
            $section_query = $grouped_text[$section->id] ?? [];
            foreach ($section_query as $content) {
                $section_content_item = $dom->createElement('p');
                $section_content_item->setAttribute('class', "spenpo-resume-section-content-item-$content->id");
                $section_content_label = $dom->createElement('strong');
                $section_content_label->setAttribute('class', "spenpo-resume-section-content-label-$content->id");
                $section_content_label_text = $dom->createTextNode($content->label . ": ");
                $section_content_label->appendChild($section_content_label_text);
                $section_content_item->appendChild($section_content_label);
                $section_content_text_container = $dom->createElement('span');
                $section_content_text_container->setAttribute('class', "spenpo-resume-section-content-text-container-$content->id");
                $section_content_text = $dom->createTextNode($content->text);
                $section_content_text_container->appendChild($section_content_text);
                $section_content_item->appendChild($section_content_text_container);
                $section_content->appendChild($section_content_item);
            }
        }

        if ($section->content_type === 'list') {
            $section_content_list_items = $grouped_list[$section->id] ?? [];
            $section_content_list = $dom->createElement('ul');
            $section_content_list->setAttribute('class', "spenpo-resume-section-content-list-$section->id");
            foreach ($section_content_list_items as $item) {
                $section_content_list_item = $dom->createElement('li');
                $section_content_list_item->setAttribute('class', "spenpo-resume-section-content-list-item-$item->id");
                
                // content 
                $section_content_list_item_content;
                if (isset($item->link)) {
                    $section_content_list_item_content = $dom->createElement('a');
                    $section_content_list_item_content->setAttribute('href', $item->link);
                } else {
                    $section_content_list_item_content = $dom->createElement('span');
                    $section_content_list_item_content->setAttribute('class', "spenpo-resume-section-content-list-item-content-$item->id");
                }
                $section_content_list_item_text = $dom->createTextNode($item->text);
                $section_content_list_item_content->appendChild($section_content_list_item_text);
                $section_content_list_item->appendChild($section_content_list_item_content);
                
                // year
                $section_content_list_item_year;
                if (isset($item->year_link)) {
                    $section_content_list_item_year = $dom->createElement('a');
                    $section_content_list_item_year->setAttribute('href', $item->year_link);
                } else {
                    $section_content_list_item_year = $dom->createElement('span');
                    $section_content_list_item_year->setAttribute('class', "spenpo-resume-section-content-list-item-year-$item->id");
                }
                $section_content_list_item_year_text = $dom->createTextNode($item->year);
                $section_content_list_item_year->appendChild($section_content_list_item_year_text);
                $section_content_list_item->appendChild($section_content_list_item_year);
                
                $section_content_list->appendChild($section_content_list_item);
            }
            $section_div->appendChild($section_content_list);
        }

        if ($section->content_type === 'nested') {
            $section_content_nested = $dom->createElement('div');
            $section_content_nested->setAttribute('class', "spenpo-resume-section-content-nested-$section->id");
            $section_content_nested_items = $grouped_nested[$section->id] ?? [];

            $current_nested_item = null;
            foreach ($section_content_nested_items as $item) {
                // Only create new nested item container if we're on a new nested section
                if ($current_nested_item === null || $current_nested_item !== $item->nested_id) {
                    // container
                    $section_content_nested_item = $dom->createElement('div');
                    $section_content_nested_item->setAttribute('class', "spenpo-resume-section-content-nested-item-$item->nested_id");

                    // title
                    if ($item->nested_title) {
                        $section_content_nested_title = $dom->createElement('span');
                        $section_content_nested_title->setAttribute('class', "spenpo-resume-section-content-nested-item-title-$item->nested_id");
                        $section_content_nested_title_text = $dom->createTextNode($item->nested_title . ": ");
                        $section_content_nested_title->appendChild($section_content_nested_title_text);
                        $section_content_nested_item->appendChild($section_content_nested_title);
                    }

                    // link
                    if ($item->link_title) {
                        $section_content_nested_link = $dom->createElement('a');
                        $section_content_nested_link->setAttribute('class', "spenpo-resume-section-content-nested-item-link-$item->nested_id");
                        $section_content_nested_link->setAttribute('href', $item->href);
                        $section_content_nested_link_text = $dom->createTextNode($item->link_title);
                        $section_content_nested_link->appendChild($section_content_nested_link_text);
                        $section_content_nested_item->appendChild($section_content_nested_link);
                    }

                    // sub title
                    if ($item->sub_title) {
                        $section_content_nested_sub_title = $dom->createElement('span');
                        $section_content_nested_sub_title->setAttribute('class', "spenpo-resume-section-content-nested-item-sub-title-$item->nested_id");
                        $section_content_nested_sub_title_text = $dom->createTextNode($item->sub_title);
                        $section_content_nested_sub_title->appendChild($section_content_nested_sub_title_text);
                        $section_content_nested_item->appendChild($section_content_nested_sub_title);
                    }

                    $current_nested_item = $item->nested_id;
                }

                // Create detail content (previously in the nested query)
                if ($item->detail_id) {
                    $section_content_nested_item_content;
                    if (isset($item->detail_title)) {
                        $section_content_nested_item_content = $dom->createElement('div');
                        $section_content_nested_item_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-$item->detail_id");

                        // title
                        $section_content_nested_item_text_title_content = $dom->createElement('span');
                        $section_content_nested_item_text_title_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-title-$item->detail_id");
                        $section_content_nested_item_text_title_content_text = $dom->createTextNode($item->detail_title);
                        $section_content_nested_item_text_title_content->appendChild($section_content_nested_item_text_title_content_text);
                        $section_content_nested_item_content->appendChild($section_content_nested_item_text_title_content);

                        // sub title
                        $section_content_nested_item_text_sub_title_content = $dom->createElement('span');
                        $section_content_nested_item_text_sub_title_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-sub-title-$item->detail_id");
                        $section_content_nested_item_text_sub_title_content_text = $dom->createTextNode($item->detail_sub_title);
                        $section_content_nested_item_text_sub_title_content->appendChild($section_content_nested_item_text_sub_title_content_text);
                        $section_content_nested_item_content->appendChild($section_content_nested_item_text_sub_title_content);
                    } else {
                        $section_content_nested_item_content = $dom->createElement('p');
                        $section_content_nested_item_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-$item->detail_id");
                        $section_content_nested_item_text_content_text = $dom->createTextNode($item->detail_text);
                        $section_content_nested_item_content->appendChild($section_content_nested_item_text_content_text);
                    }
                    if ($item->indent) {
                        $existing_classes = $section_content_nested_item_content->getAttribute('class');
                        $section_content_nested_item_content->setAttribute('class', "$existing_classes"." ml-$item->indent");
                    }
                    $section_content_nested_item->appendChild($section_content_nested_item_content);
                }

                $section_content_nested->appendChild($section_content_nested_item);
            }
            $section_div->appendChild($section_content_nested);
        }

        $section_div->appendChild($section_content);
        $dom->appendChild($section_div);
    }
    
    // Convert DOMDocument to HTML string
    return $dom->saveHTML();
}

add_shortcode('spenpo_resume', 'my_custom_shortcode');

add_action( 'rest_api_init', function () {
    register_rest_route( 'spenpo/v1', '/resume', array(
        'methods' => 'GET',
        'callback' => 'fetch_resume',
        // 'permission_callback' => function () {
        //   return current_user_can( 'edit_others_posts' );
        // },
        'permission_callback' => '__return_true',
    ) );
} );

function fetch_resume() {
    global $wpdb;
    
    // Get all sections with their related content in one query for each type
    $text_sections = $wpdb->get_results("
        SELECT s.*, tc.label, tc.text as content_text
        FROM $wpdb->prefix"."resume_sections s
        LEFT JOIN $wpdb->prefix"."resume_section_text_content tc ON s.id = tc.section_id
        WHERE s.content_type = 'text'
    ");

    $list_sections = $wpdb->get_results("
        SELECT s.*, li.text, li.year, li.link, li.year_link
        FROM $wpdb->prefix"."resume_sections s
        LEFT JOIN $wpdb->prefix"."resume_section_items li ON s.id = li.section_id
        WHERE s.content_type = 'list'
    ");

    $nested_sections = $wpdb->get_results("
        SELECT s.*, ns.id as nested_id, ns.title as nested_title, ns.link_title, 
               ns.href, ns.sub_title as nested_sub_title,
               nsd.text, nsd.title as detail_title, 
               nsd.sub_title as detail_sub_title, nsd.indent
        FROM $wpdb->prefix"."resume_sections s
        LEFT JOIN $wpdb->prefix"."resume_nested_sections ns ON s.id = ns.section_id
        LEFT JOIN $wpdb->prefix"."resume_nested_section_details nsd ON ns.id = nsd.nested_section_id
        WHERE s.content_type = 'nested'
        ORDER BY s.id, ns.id, nsd.id
    ");

    // Group the results by section type
    $sections_by_id = [];
    
    // Process text sections
    foreach ($text_sections as $row) {
        if (!isset($sections_by_id[$row->id])) {
            $sections_by_id[$row->id] = [
                'title' => $row->title,
                'defaultExpanded' => (bool)$row->default_expanded,
                'content' => [
                    'type' => 'text',
                    'textContent' => []
                ]
            ];
        }
        if ($row->label) {
            $sections_by_id[$row->id]['content']['textContent'][] = [
                'label' => $row->label,
                'text' => $row->content_text
            ];
        }
    }

    // Process list sections
    foreach ($list_sections as $row) {
        if (!isset($sections_by_id[$row->id])) {
            $sections_by_id[$row->id] = [
                'title' => $row->title,
                'defaultExpanded' => (bool)$row->default_expanded,
                'content' => [
                    'type' => 'list',
                    'items' => []
                ]
            ];
        }
        if ($row->text) {
            $list_item = [
                'text' => $row->text,
                'year' => $row->year
            ];
            if ($row->link) $list_item['link'] = $row->link;
            if ($row->year_link) $list_item['yearLink'] = $row->year_link;
            $sections_by_id[$row->id]['content']['items'][] = $list_item;
        }
    }

    // Process nested sections
    foreach ($nested_sections as $row) {
        if (!isset($sections_by_id[$row->id])) {
            $sections_by_id[$row->id] = [
                'title' => $row->title,
                'defaultExpanded' => (bool)$row->default_expanded,
                'content' => [
                    'type' => 'nested',
                    'nestedSections' => []
                ]
            ];
        }
        
        if ($row->nested_id && !isset($sections_by_id[$row->id]['content']['nestedSections'][$row->nested_id])) {
            $sections_by_id[$row->id]['content']['nestedSections'][$row->nested_id] = [
                'title' => $row->nested_title,
                'linkTitle' => $row->link_title,
                'href' => $row->href,
                'subTitle' => $row->nested_sub_title,
                'details' => []
            ];
        }
        
        if ($row->nested_id && ($row->text || $row->detail_title || $row->detail_sub_title)) {
            $detail = [];
            if ($row->text) $detail['text'] = $row->text;
            if ($row->detail_title) $detail['title'] = $row->detail_title;
            if ($row->detail_sub_title) $detail['subTitle'] = $row->detail_sub_title;
            if ($row->indent) $detail['indent'] = $row->indent;
            $sections_by_id[$row->id]['content']['nestedSections'][$row->nested_id]['details'][] = $detail;
        }
    }

    // Convert nested sections associative arrays to indexed arrays
    foreach ($sections_by_id as &$section) {
        if ($section['content']['type'] === 'nested') {
            $section['content']['nestedSections'] = array_values($section['content']['nestedSections']);
        }
    }

    return new WP_REST_Response(array_values($sections_by_id), 200);
}

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
    $script_path = plugin_dir_path(__FILE__) . 'data/seed.sql';
    executeScript($script_path);
}

register_activation_hook(__FILE__, 'database_creation');

// Create a Database
function database_teardown() {
    global $wpdb;
    
    // Order matters due to foreign key constraints
    // Drop child tables first, then parent tables
    $tables = [
        // Child tables
        'resume_nested_section_details',  // depends on resume_nested_sections
        'resume_nested_sections',         // depends on resume_sections
        'resume_section_text_content',    // depends on resume_sections
        'resume_section_items',           // depends on resume_sections
        // Parent table
        'resume_sections'                 // no dependencies
    ];

    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
    }
}

register_deactivation_hook(__FILE__, 'database_teardown');

function enqueue_resume_styles() {
    wp_enqueue_style(
        'spenpo-resume-styles',
        plugins_url('style.css', __FILE__),
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_resume_styles');


