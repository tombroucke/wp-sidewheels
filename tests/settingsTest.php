<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use \SideWheels\Settings;

final class SettingsTest extends \WP_Mock\Tools\TestCase
{

    private $settings;
    private $sidewheels;

    public function setUp(): void {
        \WP_Mock::setUp();
        $this->sidewheels = wp_sidewheels();
        $this->settings = new Settings();
    }

    public function tearDown(): void {
        \WP_Mock::tearDown();
    }

    public function test_validate() {
        $this->assertTrue($this->settings->validate());

        $this->setProtectedProperty($this->settings, 'config', array());

        $this->expectException(Exception::class);
        $this->settings->validate();
    }

    public function test_get() {
        $this->assertEquals($this->settings->get('text-domain'), 'test');
    }

    public function test_get_first_matching() {
        $this->assertEquals($this->settings->get_first_matching('endpoints', 'capability', 'dashboard/contacts'), 'read_contacts');
        $this->assertEquals($this->settings->get_first_matching('endpoints', 'capability', 'dashboard/calendar'), 'read_dashboard');
        $this->assertFalse($this->settings->get_first_matching('endpoints', 'unexisting', 'dashboard/calendar'));
    }

    public function test_is_sidewheels_page() {
        $this->assertFalse($this->settings->is_sidewheels_page());

        $settings = Mockery::mock( '\Sidewheels\Settings' )->makePartial();
        $settings->shouldReceive( 'query_var' )
            ->with('sidewheels_endpoint')
            ->andReturn( 'calendar' );

        $this->assertTrue($settings->is_sidewheels_page());

    }

    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }
}
