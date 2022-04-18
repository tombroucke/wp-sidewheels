<?php declare(strict_types=1);

namespace Otomaties\Sidewheels;

use Otomaties\Sidewheels\Config;
use Otomaties\Sidewheels\CustomPostTypes;
use PHPUnit\Framework\TestCase;

function register_extended_taxonomy($name, $postType, $args) {
    return CustomTaxonomiesTest::$functions->register_extended_taxonomy($name, $postType, $args);
}


final class CustomTaxonomiesTest extends TestCase
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

    public function testIfTaxonomyIsAdded()
    {
		$config = new Config(dirname(__FILE__));
        $cpts = new CustomTaxonomies($config);
		$firstTaxonomySlug = array_key_first($config->taxonomies());
		$args = $config->taxonomies()[$firstTaxonomySlug];
		
		self::$functions->shouldReceive('register_extended_taxonomy')->with($firstTaxonomySlug, $args['post_type'], \Mockery::any())->once();
        $cpts->add($firstTaxonomySlug, $args['singular_label'], $args['plural_label'], $args['post_type']);

		// The test is passed, register_extended_taxonomy has been called
		$this->assertTrue(true);
    }
}
