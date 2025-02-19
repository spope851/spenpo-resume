<?php
namespace SPCV\Tests\Unit\Repositories;

use WP_Mock;
use PHPUnit\Framework\TestCase;
use SPCV\Repositories\SpcvResumeRepository;

class ResumeRepositoryTest extends TestCase {
    private $repository;
    private $wpdb;

    public function setUp(): void {
        WP_Mock::setUp();
        
        // Create a mock object that preserves original properties
        $this->wpdb = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get_results', 'prepare'])
            ->setMockClassName('wpdb')
            ->getMock();
        
        // Set the properties after creating the mock
        $this->wpdb->prefix = 'wp_';
        $this->wpdb->last_error = '';
        
        // Make wpdb global BEFORE creating repository
        global $wpdb;
        $wpdb = $this->wpdb;
        
        // Now create the repository
        $this->repository = new SpcvResumeRepository();
        
        // Verify the mock setup
        // var_dump([
        //     'Mock WPDB:' => $this->wpdb,
        //     'Global WPDB:' => $GLOBALS['wpdb']
        // ]);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
    }

    public function testGetTextSections() {
        // Prepare test data
        $expected = [
            (object)[
                'id' => 1,
                'content_id' => 1,
                'label' => 'Test Label',
                'content_text' => 'Test Content'
            ]
        ];

        // Set up wpdb mock with specific query
        $expectedQuery = "
                SELECT s.*, tc.id as content_id, tc.label, tc.text as content_text
                FROM {$this->wpdb->prefix}spcv_resume_sections s
                LEFT JOIN {$this->wpdb->prefix}spcv_resume_section_text_content tc ON s.id = tc.section_id
                WHERE s.content_type = %s
                ORDER BY s.display_order, tc.display_order
            ";

        $preparedQuery = sprintf($expectedQuery, 'text');
        
        // var_dump([
        //     'Before setting up mock:' => [
        //         'WPDB object' => $this->wpdb,
        //         'Expected query' => $expectedQuery
        //     ]
        // ]);
        
        $this->wpdb->expects($this->once())
            ->method('prepare')
            ->with($expectedQuery, 'text')
            ->willReturn($preparedQuery);

        $this->wpdb->expects($this->once())
            ->method('get_results')
            ->with($preparedQuery)  // Verify the exact query being used
            ->willReturn($expected);

        // Execute test
        $result = $this->repository->getTextSections();
        
        // Debug output
        // var_dump([
        //     'Test results:' => [
        //         'Expected Query' => $expectedQuery,
        //         'Expected Results' => $expected,
        //         'Actual Results' => $result,
        //         'Last Error' => $this->wpdb->last_error
        //     ]
        // ]);
        
        // Assert
        $this->assertEquals($expected, $result);
    }

    public function testGetTextSectionsHandlesError() {
        // Set up wpdb mock to simulate error
        $this->wpdb->last_error = 'Test error';
        
        $this->wpdb->expects($this->once())
            ->method('get_results')
            ->willReturn(null);

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test error');

        // Execute test - this should throw an exception
        $this->repository->getTextSections();
    }
} 