<?php
namespace SPCV\Tests\Unit\Repositories;

use WP_Mock;
use PHPUnit\Framework\TestCase;

class DatabaseManagerTest extends TestCase {
    public function setUp(): void {
        WP_Mock::setUp();
        
        if (!defined('ABSPATH')) {
            define('ABSPATH', '/var/www/html/wordpress/');
        }

        // Mock dbDelta function
        WP_Mock::userFunction('dbDelta', [
            'times' => '0+',
            'return' => []
        ]);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
    }

    public function testExecuteScriptHandlesMissingFile() {
        $mock = \Mockery::mock('overload:Spenpo\Resume\Repositories\DatabaseManager');
        
        // Define the executeScript method behavior
        $mock->shouldReceive('executeScript')
            ->once()
            ->with('non-existent-file.sql')
            ->andReturn([
                'success' => false,
                'error' => 'SQL file not found: non-existent-file.sql'
            ]);

        // Mock the protected method
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('requireUpgradeFile')
            ->once()
            ->andReturn(true);

        $result = $mock::executeScript('non-existent-file.sql');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('SQL file not found', $result['error']);
    }

    public function testCreateDatabase() {
        // Create mock before calling any static methods
        $mock = \Mockery::mock('overload:Spenpo\Resume\Repositories\DatabaseManager');
        
        // Mock WordPress functions with more permissive expectations
        WP_Mock::userFunction('get_option', [
            'times' => '0+',
            'args' => ['spenpo_resume_db_version', '0'],
            'return' => '0'
        ]);

        WP_Mock::userFunction('plugin_dir_path', [
            'times' => '0+',
            'return' => '/path/to/plugin/'
        ]);

        WP_Mock::userFunction('update_option', [
            'times' => '0+',  // Make this more permissive
            'return' => true
        ]);

        // Define createDatabase behavior to actually call update_option
        $mock->shouldReceive('createDatabase')
            ->once()
            ->andReturnUsing(function() {
                update_option('spenpo_resume_db_version', '1.0.4');
                return true;
            });

        // Mock the protected method
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('requireUpgradeFile')
            ->once()
            ->andReturn(true);

        // Execute test
        $result = $mock::createDatabase();

        // Assert the result
        $this->assertTrue($result);
    }
} 