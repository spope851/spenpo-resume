<?php
namespace SPCV\Repositories;

use Exception as Exception;

/**
 * Handles database operations for resume data.
 * 
 * @package Spenpo\Resume
 * @since 1.0.0
 */
class SpcvResumeRepository {
    /** @var wpdb WordPress database instance */
    private $wpdb;

    /**
     * Constructor initializes the WordPress database connection.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Retrieves all text sections from the database.
     * 
     * @return array Array of text section objects
     * @throws Exception When database error occurs
     */
    public function getTextSections() {
        try {
            $query = $this->wpdb->prepare("
                SELECT s.*, tc.id as content_id, tc.label, tc.text as content_text
                FROM {$this->wpdb->prefix}spcv_resume_sections s
                LEFT JOIN {$this->wpdb->prefix}spcv_resume_section_text_content tc ON s.id = tc.section_id
                WHERE s.content_type = %s
                ORDER BY s.display_order, tc.display_order
            ", 'text');
            
            // var_dump([
            //     'Before get_results:' => [
            //         'Query' => $query,
            //         'WPDB object exists?' => isset($this->wpdb),
            //         'WPDB methods' => get_class_methods($this->wpdb)
            //     ]
            // ]);
            
            $results = $this->wpdb->get_results($query);
            
            // var_dump([
            //     'After get_results:' => [
            //         'Results' => $results,
            //         'Last Error' => $this->wpdb->last_error
            //     ]
            // ]);
            
            if ($this->wpdb->last_error) {
                throw new Exception($this->wpdb->last_error);
            }
            
            return $results;
        } catch (Exception $e) {
            // Log the error
            // error_log('Resume Plugin - Error getting text sections: ' . $e->getMessage());
            // Re-throw the exception to let calling code handle it
            throw $e;
        }
    }

    /**
     * Retrieves all list sections from the database.
     * 
     * @return array Array of list section objects
     */
    public function getListSections() {
        try {
            $query = $this->wpdb->prepare("
                SELECT s.*, li.id as content_id, li.text, li.year, li.link, li.year_link
                FROM {$this->wpdb->prefix}spcv_resume_sections s
                LEFT JOIN {$this->wpdb->prefix}spcv_resume_section_items li ON s.id = li.section_id
                WHERE s.content_type = %s
                ORDER BY s.display_order, li.display_order, li.year DESC
            ", 'list');
                
            $results = $this->wpdb->get_results($query);
                
            if ($this->wpdb->last_error) {
                throw new Exception($this->wpdb->last_error);
            }
            
            return $results;
        } catch (Exception $e) {
            // Log the error
            // error_log('Resume Plugin - Error getting list sections: ' . $e->getMessage());
            // Re-throw the exception to let calling code handle it
            throw $e;
        }
    }

    /**
     * Retrieves all nested sections from the database.
     * 
     * @return array Array of nested section objects
     */
    public function getNestedSections() {
        try {
            $query = $this->wpdb->prepare("
                SELECT s.*, ns.id as nested_id, ns.title as nested_title, ns.link_title, 
                    ns.href, ns.sub_title as nested_sub_title, ns.start_year, ns.end_year,
                    nsd.id as detail_id, nsd.text, nsd.title as detail_title, 
                    nsd.sub_title as detail_sub_title, nsd.indent
                FROM {$this->wpdb->prefix}spcv_resume_sections s
                LEFT JOIN {$this->wpdb->prefix}spcv_resume_nested_sections ns ON s.id = ns.section_id
                LEFT JOIN {$this->wpdb->prefix}spcv_resume_nested_section_details nsd ON ns.id = nsd.nested_section_id
                WHERE s.content_type = %s
                ORDER BY s.display_order, ns.display_order, ns.start_year DESC, ns.end_year DESC, nsd.display_order
            ", 'nested');
                
            $results = $this->wpdb->get_results($query);
                
            if ($this->wpdb->last_error) {
                throw new Exception($this->wpdb->last_error);
            }
            
            return $results;
        } catch (Exception $e) {
            // Log the error
            // error_log('Resume Plugin - Error getting nested sections: ' . $e->getMessage());
            // Re-throw the exception to let calling code handle it
            throw $e;
        }
    }
} 