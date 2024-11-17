<?php
class DatabaseManager {
    public static function executeScript(string $scriptPath, string $type = 'query'): array {
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
                if ($type === 'init') {
                    $result = dbDelta($statement);
                    error_log("dbDelta result: " . print_r($result, true));
                } else if ($type === 'query') {
                    $result = $wpdb->query($statement);
                    error_log("wpdb->query result: " . print_r($result, true));
                } else {
                    error_log("Unknown type: " . $type);
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
            error_log("Error in executeScript: " . $e->getMessage());
            return [
                'success' => false,
                'error' => "Error executing script: " . $e->getMessage()
            ];
        }
    }

    public static function createDatabase() {
        $script_path = plugin_dir_path(dirname(__FILE__)) . '../data/seed.sql';
        self::executeScript($script_path, 'init');
    }

    public static function teardownDatabase() {
        $script_path = plugin_dir_path(dirname(__FILE__)) . '../data/teardown.sql';
        self::executeScript($script_path, 'query');
    }
} 