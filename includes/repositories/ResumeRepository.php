<?php
class ResumeRepository {
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function getTextSections() {
        return $this->wpdb->get_results("
            SELECT s.*, tc.id as content_id, tc.label, tc.text as content_text
            FROM {$this->wpdb->prefix}resume_sections s
            LEFT JOIN {$this->wpdb->prefix}resume_section_text_content tc ON s.id = tc.section_id
            WHERE s.content_type = 'text'
            ORDER BY s.display_order, tc.display_order
        ");
    }

    public function getListSections() {
        return $this->wpdb->get_results("
            SELECT s.*, li.id as content_id, li.text, li.year, li.link, li.year_link
            FROM {$this->wpdb->prefix}resume_sections s
            LEFT JOIN {$this->wpdb->prefix}resume_section_items li ON s.id = li.section_id
            WHERE s.content_type = 'list'
            ORDER BY s.display_order, li.display_order, li.year DESC
        ");
    }

    public function getNestedSections() {
        return $this->wpdb->get_results("
            SELECT s.*, ns.id as nested_id, ns.title as nested_title, ns.link_title, 
                ns.href, ns.sub_title as nested_sub_title, ns.start_year, ns.end_year,
                nsd.id as detail_id, nsd.text, nsd.title as detail_title, 
                nsd.sub_title as detail_sub_title, nsd.indent
            FROM {$this->wpdb->prefix}resume_sections s
            LEFT JOIN {$this->wpdb->prefix}resume_nested_sections ns ON s.id = ns.section_id
            LEFT JOIN {$this->wpdb->prefix}resume_nested_section_details nsd ON ns.id = nsd.nested_section_id
            WHERE s.content_type = 'nested'
            ORDER BY s.display_order, ns.display_order, ns.start_year DESC, ns.end_year DESC, nsd.display_order
        ");
    }
} 