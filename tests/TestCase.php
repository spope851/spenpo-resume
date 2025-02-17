<?php
namespace SPCV\Tests;

use WP_Mock\Tools\TestCase as WPMockTestCase;

class TestCase extends WPMockTestCase {
    public function setUp(): void {
        \WP_Mock::setUp();
    }

    public function tearDown(): void {
        \WP_Mock::tearDown();
        parent::tearDown();
    }
} 