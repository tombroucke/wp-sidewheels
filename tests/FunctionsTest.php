<?php declare(strict_types=1);

namespace Otomaties\Sidewheels;

use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        \WP_Mock::setUp();
    }

    public function tearDown(): void
    {
        \WP_Mock::tearDown();
        \Mockery::close();
        parent::tearDown();
    }
    
    public function testIfRouteParametersAreReplace()
    {
        $route = 'orders/{order_id}/product/{product_id}';
        $replacements = [
            'order_id' => 69,
            'product_id' => 420
        ];
        $this->assertEquals(sidewheelsReplaceRouteParameters($route, $replacements), 'orders/69/product/420');
    }

    public function testIfSidewheelsRouteIsCorrect()
    {
        $route = 'orders/{order_id}/product/{product_id}';
        $replacements = [
            'order_id' => 69,
            'product_id' => 420
        ];
        $this->assertEquals(sidewheelsRoute($route, $replacements), 'https://example.com/orders/69/product/420/');
    }

    public function testIfTemplateIncludeFilterIsAdded()
    {
        \WP_Mock::expectFilterAdded('template_include', function () {
        }, 99999, 1);
        sidewheelsTrigger404();

        // The test is to see if a filter is added
        $this->assertTrue(true);
    }
}
