<?php declare(strict_types=1);

namespace Otomaties\Sidewheels;

use PHPUnit\Framework\TestCase;

class Role
{
    public function add_cap($cap)
    {
        return true;
    }
    public function remove_cap($cap)
    {
        return true;
    }
}

function add_role($roleName, $roleLabel)
{
    return SidewheelsTest::$functions->add_role($roleName, $roleLabel);
}

function get_role($roleName)
{
    return new Role();
}

final class SidewheelsTest extends TestCase
{
    private $sidewheels;
    public static $functions;

    public function setUp(): void
    {
        $this->sidewheels = Sidewheels::init(dirname(__FILE__));
        parent::setUp();
        \WP_Mock::setUp();
        self::$functions = \Mockery::mock();
    }

    public function tearDown(): void
    {
        \WP_Mock::tearDown();
        \Mockery::close();
        parent::tearDown();
    }

    public function testConfigIsQueryable()
    {
        $this->assertInstanceOf(Config::class, $this->sidewheels->config());
    }

    public function testRolesAreAdded()
    {
        self::$functions->shouldReceive('add_role')->with(\Mockery::any(), \Mockery::any())->twice();
        $this->sidewheels->initRoles();

        // The test was to see if add_role is called twice
        $this->assertTrue(true);
    }
}
