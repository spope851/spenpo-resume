<?php
namespace SPCV\Tests\Unit\Shortcodes;

use SPCV\API\SpcvResumeAPI;
use SPCV\Shortcodes\SpcvResumeShortcode;
use PHPUnit\Framework\TestCase;
use WP_Mock;
use ReflectionClass;

// Test-specific subclass
class TestSpcvResumeShortcode extends SpcvResumeShortcode {
    private $apiMock;

    public function __construct(SpcvResumeAPI $apiMock) {
        $this->apiMock = $apiMock;
        
        // Use reflection to set the private api property in parent class
        $reflection = new ReflectionClass(SpcvResumeShortcode::class);
        $property = $reflection->getProperty('api');
        $property->setAccessible(true);
        $property->setValue($this, $apiMock);
    }

    protected function getAPI() {
        return $this->apiMock;
    }
}

class SpcvResumeShortcodeTest extends TestCase {
    private $apiMock;
    private $shortcode;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();

        // Mock WordPress functions
        WP_Mock::userFunction('do_action', [
            'times' => '0+',
            'return' => null
        ]);

        WP_Mock::userFunction('apply_filters', [
            'times' => '0+',
            'return_arg' => 1
        ]);

        // Create API mock
        $this->apiMock = $this->createMock(SpcvResumeAPI::class);
        
        // Create shortcode instance
        $this->shortcode = new TestSpcvResumeShortcode($this->apiMock);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
    }

    public function testRender() {
        // Mock resume data
        $mockData = [
            (object)[
                'id' => 1,
                'title' => 'Test Section',
                'content' => (object)[
                    'type' => 'text',
                    'textContent' => [
                        (object)[
                            'id' => 1,
                            'label' => 'Test Label',
                            'text' => 'Test Content'
                        ]
                    ]
                ]
            ]
        ];

        $this->apiMock->expects($this->once())
            ->method('fetchResume')
            ->willReturn($mockData);

        $result = $this->shortcode->render();

        $this->assertStringContainsString('spenpo-resume-container', $result);
        $this->assertStringContainsString('spenpo-resume-section-1', $result);
        $this->assertStringContainsString('Test Section', $result);
        $this->assertStringContainsString('Test Label', $result);
        $this->assertStringContainsString('Test Content', $result);
    }

    public function testRenderList() {
        // Mock resume data with list type
        $mockData = [
            (object)[
                'id' => 1,
                'title' => 'Test List Section',
                'content' => (object)[
                    'type' => 'list',
                    'items' => [
                        (object)[
                            'id' => 1,
                            'text' => 'List Item',
                            'year' => '2024',
                            'link' => 'https://example.com',
                            'yearLink' => 'https://example.com/2024'
                        ]
                    ]
                ]
            ]
        ];

        $this->apiMock->expects($this->once())
            ->method('fetchResume')
            ->willReturn($mockData);

        $result = $this->shortcode->render();

        $this->assertStringContainsString('spenpo-resume-section-content-list', $result);
        $this->assertStringContainsString('List Item', $result);
        $this->assertStringContainsString('2024', $result);
        $this->assertStringContainsString('href="https://example.com"', $result);
        $this->assertStringContainsString('href="https://example.com/2024"', $result);
    }

    public function testRenderNested() {
        // Mock resume data with nested type
        $mockData = [
            (object)[
                'id' => 1,
                'title' => 'Test Nested Section',
                'content' => (object)[
                    'type' => 'nested',
                    'nestedSections' => [
                        (object)[
                            'id' => 1,
                            'title' => 'Nested Title',
                            'linkTitle' => 'Link Text',
                            'href' => 'https://example.com',
                            'subTitle' => 'Sub Title',
                            'details' => [
                                (object)[
                                    'id' => 1,
                                    'text' => 'Detail Text',
                                    'indent' => 2
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->apiMock->expects($this->once())
            ->method('fetchResume')
            ->willReturn($mockData);

        $result = $this->shortcode->render();

        $this->assertStringContainsString('spenpo-resume-section-content-nested', $result);
        $this->assertStringContainsString('Nested Title', $result);
        $this->assertStringContainsString('Link Text', $result);
        $this->assertStringContainsString('Sub Title', $result);
        $this->assertStringContainsString('Detail Text', $result);
        $this->assertStringContainsString('ml-2', $result);
    }
} 