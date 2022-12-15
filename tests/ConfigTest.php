<?php declare(strict_types=1);

use Otomaties\Sidewheels\Route;
use PHPUnit\Framework\TestCase;
use Otomaties\Sidewheels\Config;
use Otomaties\Sidewheels\Router;

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
        
        $routeArray = [
            'path' => 'dash/orders',
            'callback' => 'testCallback',
            'capability' => 'manage_options',
            'method' => 'GET',
            'title' => 'Orders',
        ];

        $route = Route::get('dash/orders', 'testCallback', 'GET')->require('manage_options')->setTitle('Orders');
        Router::instance()->register($route);
        
        $this->assertEquals($this->config->findRouteBy('path', 'dash/orders'), $routeArray);
        $this->assertEquals($this->config->findRouteBy('callback', 'testCallback'), $routeArray);
        $this->assertEquals($this->config->findRouteBy('title', 'Orders'), $routeArray);
    }
}
