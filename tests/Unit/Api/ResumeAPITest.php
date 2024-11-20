<?php

namespace Spenpo\Resume\Tests\Unit\API;

use Spenpo\Resume\API\ResumeAPI;
use Spenpo\Resume\Repositories\ResumeRepository;
use PHPUnit\Framework\TestCase;
use WP_Mock;
use stdClass;

class ResumeAPITest extends TestCase {
    private $api;
    private $repository;

    public function setUp(): void {
        WP_Mock::setUp();
        
        // Mock WordPress functions
        WP_Mock::userFunction('register_rest_route', [
            'times' => '0+',
            'return' => true
        ]);
        
        WP_Mock::userFunction('get_option', [
            'times' => '0+',
            'return' => false
        ]);

        WP_Mock::userFunction('sanitize_text_field', [
            'times' => '0+',
            'return_arg' => true
        ]);

        WP_Mock::userFunction('wp_verify_nonce', [
            'times' => '0+',
            'return' => true
        ]);

        // Create mock repository
        $this->repository = $this->createMock(ResumeRepository::class);
        
        // Get API instance and inject mock repository using reflection
        $this->api = ResumeAPI::getInstance();
        $reflection = new \ReflectionClass($this->api);
        $property = $reflection->getProperty('repository');
        $property->setAccessible(true);
        $property->setValue($this->api, $this->repository);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        // Reset singleton instance
        $reflection = new \ReflectionClass(ResumeAPI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testGetInstance() {
        $instance1 = ResumeAPI::getInstance();
        $instance2 = ResumeAPI::getInstance();
        $this->assertSame($instance1, $instance2);
    }

    public function testCheckPermissionWithoutAuth() {
        WP_Mock::userFunction('get_option', [
            'args' => ['resume_api_require_auth', false],
            'return' => false
        ]);

        $result = $this->api->checkPermission();
        $this->assertTrue($result);
    }

    public function testCheckPermissionWithAuth() {
        WP_Mock::userFunction('get_option', [
            'args' => ['resume_api_require_auth', false],
            'return' => true
        ]);

        $_REQUEST['_wpnonce'] = 'test_nonce';

        WP_Mock::userFunction('wp_verify_nonce', [
            'args' => ['test_nonce', 'wp_rest'],
            'return' => true
        ]);

        $result = $this->api->checkPermission();
        $this->assertTrue($result);
    }

    public function testFetchResume() {
        // Mock repository methods
        $this->repository->expects($this->once())
            ->method('getTextSections')
            ->willReturn([
                $this->createSectionObject('text', 1)
            ]);

        $this->repository->expects($this->once())
            ->method('getListSections')
            ->willReturn([
                $this->createSectionObject('list', 2)
            ]);

        $this->repository->expects($this->once())
            ->method('getNestedSections')
            ->willReturn([
                $this->createSectionObject('nested', 3)
            ]);

        $result = $this->api->fetchResume();
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('text', $result[0]->content->type);
    }

    public function testGetResumeResponse() {
        $mockData = [$this->createSectionObject('text', 1)];
        
        // Create partial mock of ResumeAPI to mock fetchResume
        $apiMock = $this->getMockBuilder(ResumeAPI::class)
            ->onlyMethods(['fetchResume'])
            ->disableOriginalConstructor()
            ->getMock();

        $apiMock->expects($this->once())
            ->method('fetchResume')
            ->willReturn($mockData);

        $response = $apiMock->getResumeResponse();
        
        $this->assertInstanceOf('WP_REST_Response', $response);
        $this->assertEquals($mockData, $response->get_data());
        $this->assertEquals(200, $response->get_status());
    }

    private function createSectionObject($type, $id) {
        $section = new stdClass();
        $section->id = $id;
        $section->title = "Test Title $id";
        $section->content_type = $type;
        $section->display_order = $id;
        $section->default_expanded = true;
        
        // Add type-specific properties
        switch ($type) {
            case 'text':
                $section->content_id = 1;
                $section->label = "Test Label";
                $section->content_text = "Test Content";
                break;
            case 'list':
                $section->content_id = 1;
                $section->text = "Test Text";
                $section->year = "2024";
                $section->link = null;
                $section->year_link = null;
                break;
            case 'nested':
                $section->nested_id = 1;
                $section->nested_title = "Nested Title";
                $section->detail_id = 1;
                $section->text = "Detail Text";
                $section->link_title = null;
                $section->href = null;
                $section->nested_sub_title = null;
                $section->detail_title = null;
                $section->detail_sub_title = null;
                $section->indent = null;
                break;
        }
        
        return $section;
    }
}
