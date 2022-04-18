<?php declare(strict_types=1);

namespace Otomaties\Sidewheels;

use Otomaties\Sidewheels\Config;
use Otomaties\Sidewheels\CustomPostTypes;
use PHPUnit\Framework\TestCase;

function register_extended_post_type($postType, $args) {
    return CustomPostTypesTest::$functions->register_extended_post_type($postType, $args);
}


final class CustomPostTypesTest extends TestCase
{
	public static $functions;

    public function setUp(): void
    {
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

    public function testIfCustomPostTypeIsAdded()
    {
		$config = new Config(dirname(__FILE__));
        $cpts = new CustomPostTypes($config);
		$firstPostType = array_key_first($config->postTypes());
		
		self::$functions->shouldReceive('register_extended_post_type')->with($firstPostType, \Mockery::any())->once();
        $cpts->add($firstPostType, $config->postTypes()[$firstPostType]['args']);

		// The test is passed, register_extended_post_type has been called
		$this->assertTrue(true);
    }
}
