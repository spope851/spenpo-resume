<?php
namespace Spenpo\Resume\Tests\Integration;

use WP_UnitTestCase;

/**
 * @group integration
 */
class ResumeIntegrationTest extends WP_UnitTestCase {
    protected function setUp(): void {
        parent::setUp();
        
        \Spenpo\Resume\Repositories\DatabaseManager::createTestDatabase();
        
        // Load test data if needed
        $this->loadTestData();
    }
    
    protected function loadTestData(): void {
        global $wpdb;
        
        // Insert a test section
        $wpdb->insert(
            $wpdb->prefix . 'resume_sections',
            [
                'title' => 'Test Section',
                'content_type' => 'list',
                'display_order' => 1
            ]
        );
        $section_id = $wpdb->insert_id;
        
        // Insert a test item
        $wpdb->insert(
            $wpdb->prefix . 'resume_section_items',
            [
                'section_id' => $section_id,
                'text' => 'Test Item',
                'display_order' => 1
            ]
        );
    }
    
    protected function assertValidSectionId($section_id): void {
        global $wpdb;
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}resume_sections WHERE id = %d",
            $section_id
        ));
        
        $this->assertEquals(1, $exists, "Section ID {$section_id} does not exist");
    }
    
    public function testDataIntegrity(): void {
        global $wpdb;
        
        // Test section items reference valid sections
        $items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_section_items");
        foreach ($items as $item) {
            $this->assertValidSectionId($item->section_id);
        }
    }
    
    // Add your other test methods here
} 