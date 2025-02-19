<?php
/**
 * Manages database operations for the resume plugin.
 * 
 * @package Spenpo Resume
 * @since 1.0.0
 */
namespace SPCV\Repositories;

class SpcvDatabaseManager {
    protected static function requireUpgradeFile() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    }

    /**
     * Gets the table prefix, falling back to a default if needed
     * 
     * @return string The table prefix to use
     */
    protected static function getTablePrefix(): string {
        global $wpdb;
        
        // First try $wpdb->prefix
        if (!empty($wpdb->prefix)) {
            return $wpdb->prefix;
        }
        
        // Then try the test prefix constant
        if (defined('WP_TESTS_TABLE_PREFIX')) {
            return WP_TESTS_TABLE_PREFIX;
        }
        
        // Finally, fall back to wp_ as a last resort
        return 'wp_';
    }

    /**
     * Executes an SQL script file.
     * 
     * @param string $scriptPath Path to the SQL script file
     * @param string $type Type of execution ('query' or 'init')
     * @return array Result array with 'success' boolean and 'message'/'error' string
     */
    public static function executeScript(string $scriptPath, string $type = 'query'): array {
        global $wpdb;
        
        static::requireUpgradeFile();
        
        if (!file_exists($scriptPath)) {
            // error_log("SQL file not found at: " . $scriptPath);
            return [
                'success' => false,
                'error' => "SQL file not found: {$scriptPath}"
            ];
        }

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        global $wp_filesystem;
        
        // Initialize WP_Filesystem
        if ( WP_Filesystem() ) {
            $plugin_dir = plugin_dir_path( __FILE__ );
        
            // Check if the file exists
            if ( $wp_filesystem->exists( $scriptPath ) ) {
                // Read the content
                $sql = $wp_filesystem->get_contents( $scriptPath );
        
                if ( $sql !== false ) {
                    try {
                        $prefix = static::getTablePrefix();
                        $sql = str_replace('{$wpdb->prefix}', esc_sql($prefix), $sql);
                        
                        // error_log("SQL content after prefix replacement: " . $sql);
                
                        $statements = array_filter(
                            array_map(
                                'trim',
                                explode(';', $sql)
                            ),
                            'strlen'
                        );
                
                        foreach ($statements as $statement) {
                            // error_log("Executing statement: " . $statement);
                            if ($type === 'init') {
                                $result = dbDelta($statement);
                                // error_log("dbDelta result: " . print_r($result, true));
                            } else if ($type === 'query') {
                                $query = $wpdb->prepare($statement);
                                $result = $wpdb->query($query);
                                // error_log("wpdb->query result: " . print_r($result, true));
                            } else {
                                // error_log("Unknown type: " . $type);
                            }
                            
                            if (is_wp_error($result)) {
                                throw new Exception($result->get_error_message());
                            }
                        }
                
                        return [
                            'success' => true,
                            'message' => "Script executed successfully"
                        ];
                    } catch (Exception $e) {
                        // error_log("Error in spcvExecuteScript: " . $e->getMessage());
                        return [
                            'success' => false,
                            'error' => "Error executing script: " . $e->getMessage()
                        ];
                    }
                } else {
                    // error_log("Failed to read SQL file");
                    return [
                        'success' => false,
                        'error' => "Failed to read SQL file"
                    ];
                }
            } else {
                echo 'File not found.';
            }
        } else {
            echo 'Failed to initialize WP_Filesystem.';
        }
    }

    /**
     * Creates or updates the database schema.
     * 
     * @return void
     */
    public static function createTestDatabase() {
        $script_path = plugin_dir_path(dirname(__FILE__)) . '../data/test-schema.sql';
        self::executeScript($script_path, 'init');
    }

    /**
     * Removes all plugin tables from the database.
     * 
     * @return void
     */
    public static function teardownDatabase() {
        global $wpdb;
        
        // Drop tables in reverse order to respect foreign key constraints
        $tables = array(
            'spcv_resume_nested_section_details',
            'spcv_resume_nested_sections',
            'spcv_resume_section_text_content',
            'spcv_resume_section_items',
            'spcv_resume_sections'
        );

        foreach ($tables as $table) {
            $preparedQuery = $wpdb->prepare("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
            $wpdb->query($preparedQuery);
        }
    }

    public static function createDatabase() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = array();

        // Resume Sections table
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}spcv_resume_sections` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `default_expanded` tinyint(1) DEFAULT 0,
            `content_type` enum('text','list','nested') NOT NULL,
            `display_order` int(11) NOT NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY `unique_display_order` (`display_order`)
        ) $charset_collate;";

        // Section Items table
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}spcv_resume_section_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `section_id` int(11) NOT NULL,
            `text` text NOT NULL,
            `year` YEAR DEFAULT NULL,
            `link` varchar(255) DEFAULT NULL,
            `year_link` varchar(255) DEFAULT NULL,
            `indent` int(11) DEFAULT 0,
            `display_order` int(11) DEFAULT NULL,
            PRIMARY KEY  (`id`),
            FOREIGN KEY (`section_id`) REFERENCES `{$wpdb->prefix}spcv_resume_sections` (`id`)
        ) $charset_collate;";

        // Section Text Content table
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}spcv_resume_section_text_content` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `section_id` int(11) NOT NULL,
            `label` varchar(255) DEFAULT NULL,
            `text` text NOT NULL,
            `display_order` int(11) NOT NULL,
            PRIMARY KEY  (`id`),
            FOREIGN KEY (`section_id`) REFERENCES `{$wpdb->prefix}spcv_resume_sections` (`id`)
        ) $charset_collate;";

        // Nested Sections table
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}spcv_resume_nested_sections` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `section_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL,
            `link_title` varchar(255) NOT NULL,
            `href` varchar(255) NOT NULL,
            `start_year` YEAR DEFAULT NULL,
            `end_year` YEAR DEFAULT NULL,
            `custom_sub_title` varchar(255),
            `sub_title` varchar(255) GENERATED ALWAYS AS (
                CASE 
                    WHEN custom_sub_title IS NOT NULL
                        THEN custom_sub_title
                    WHEN start_year IS NOT NULL AND end_year IS NOT NULL 
                        THEN CONCAT(start_year, ' - ', end_year)
                    WHEN start_year IS NOT NULL AND end_year IS NULL
                        THEN CONCAT(start_year, ' - present') 
                    ELSE NULL
                END
            ) STORED,
            `display_order` int(11) DEFAULT NULL,
            PRIMARY KEY  (`id`),
            FOREIGN KEY (`section_id`) REFERENCES `{$wpdb->prefix}spcv_resume_sections` (`id`)
        ) $charset_collate;";

        // Nested Section Details table
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}spcv_resume_nested_section_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nested_section_id` int(11) NOT NULL,
            `text` text DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `sub_title` varchar(255) DEFAULT NULL,
            `indent` int(11) DEFAULT 0,
            `display_order` int(11) NOT NULL,
            PRIMARY KEY  (`id`),
            FOREIGN KEY (`nested_section_id`) REFERENCES `{$wpdb->prefix}spcv_resume_nested_sections` (`id`)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Execute each CREATE TABLE query
        foreach ($sql as $query) {
            dbDelta($query);
        }
    }
} 