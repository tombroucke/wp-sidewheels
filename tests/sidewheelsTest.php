<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class WP_SidewheelsTest extends \WP_Mock\Tools\TestCase
{
    private $sidewheels;

    public function setUp(): void {
        \WP_Mock::setUp();

        $this->sidewheels = wp_sidewheels();
        \WP_Mock::expectFilterAdded('query_vars', array($this->sidewheels, 'custom_query_vars'));
        $this->sidewheels->init();
        \WP_Mock::assertHooksAdded();

        \WP_Mock::expectFilterAdded('query_vars', array($this->sidewheels, 'custom_query_vars'));
        \WP_Mock::expectActionAdded('template_redirect', array($this->sidewheels, 'frontend_init'), 0);
        \WP_Mock::expectActionAdded('admin_init', array($this->sidewheels, 'admin_init'));
        \WP_Mock::expectActionAdded('init', array($this->sidewheels, 'create_routes'));
        \WP_Mock::expectActionAdded('init', array($this->sidewheels, 'create_post_types'));
        \WP_Mock::expectActionAdded('acf/init', array($this->sidewheels, 'add_fields'));
        $this->sidewheels->init();
        \WP_Mock::assertHooksAdded();
    }

    public function tearDown(): void {
        \WP_Mock::tearDown();
    }

    public function test_custom_query_vars()
    {   
        $args = array();
        $vars = $this->sidewheels->custom_query_vars($args);
        $this->assertContains('lang', $vars );
        $this->assertContains('sidewheels_endpoint', $vars );
        $this->assertContains('sidewheels_object_id', $vars );
    }

    public function test_frontend_init() {
        // Not a sidewheels page
        $this->assertNull($this->sidewheels->frontend_init());

        // If it is a sidewheels page
        $settings = Mockery::mock( '\Sidewheels\Settings' )->makePartial();
        $settings->shouldReceive( 'is_sidewheels_page' )
            ->andReturn( true );

        $settings->shouldReceive( 'query_var' )
            ->with('sidewheels_endpoint')
            ->andReturn( 'calendar' );

        $settings->__construct();
        $this->setProtectedProperty($this->sidewheels, 'settings', $settings);

        $this->assertInstanceOf('\Sidewheels\Template_Controllers', $this->sidewheels->frontend_init());
    }

    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }
}
