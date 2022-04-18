<?php declare(strict_types=1);

use Otomaties\Sidewheels\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{

	private $config;

    public function setUp(): void
    {
		$this->config = new Config(dirname(__FILE__));
        parent::setUp();
        WP_Mock::setUp();
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    public function testIfTemplatePathInConfig()
    {
        
        $this->assertEquals($this->config->templatePath(), 'custom-directory/views');
    }

    public function testTextDomainIsDefined()
    {
        
        $this->assertEquals($this->config->textDomain(), 'test');
    }

    public function testRoutesAreDefined()
    {
        
        $this->assertCount(2, $this->config->routes());
        $this->assertEquals($this->config->routes()[0], [
            'path' => 'dash/orders',
            'callback' => 'testCallback',
            'capability' => 'manage_options',
            'method' => 'GET',
            'title' => 'Orders',
        ]);
    }

    public function testPostTypesAreDefined()
    {
        
        $this->assertCount(1, $this->config->postTypes());
        $this->assertArrayHasKey('shop_order', $this->config->postTypes());
    }

    public function testRolesAreDefined()
    {
        
        $this->assertCount(2, $this->config->roles());
        $this->assertArrayHasKey('administrator', $this->config->roles());
    }

    public function testIfRouteIsFound()
    {
        $route = [
            'path' => 'dash/orders',
            'callback' => 'testCallback',
            'capability' => 'manage_options',
            'method' => 'GET',
            'title' => 'Orders',
        ];
        
        $this->assertEquals($this->config->findRouteBy('path', 'dash/orders'), $route);
        $this->assertEquals($this->config->findRouteBy('callback', 'testCallback'), $route);
        $this->assertEquals($this->config->findRouteBy('title', 'Orders'), $route);
    }
}
