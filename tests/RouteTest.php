<?php declare(strict_types=1);

use Otomaties\Sidewheels\Abstracts\Controller;
use Otomaties\Sidewheels\Route;
use PHPUnit\Framework\TestCase;

class Callback extends Controller
{
    public function test()
    {
        return testCallback();
    }
}

final class RouteTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        WP_Mock::setUp();
    }
    
    public function tearDown(): void
    {
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }
    
    public function testIfRewriteRulesAreAdded()
    {
        global $rewrite_rules;
        new Route('orders', 'callback', 'GET');
        $this->assertContains(['^orders?$', 'index.php?sidewheels_route=orders', 'top'], $rewrite_rules);

        \WP_Mock::expectFilterAdded('query_vars', function () {
        }, 10, 1);
        new Route('orders/{order_id}', 'callback', 'GET');
        $this->assertContains(['^orders/([0-9]+)?$', 'index.php?sidewheels_route=orders/{order_id}&sidewheels_order_id=$matches[1]', 'top'], $rewrite_rules);

    }

    public function testIfPathIsCorrect() {
        $route = new Route('orders', 'callback', 'GET');
        $this->assertEquals('orders', $route->path());

        $route = new Route('orders/{order_id}', 'callback', 'GET');
        $this->assertEquals('orders/{order_id}', $route->path());
    }
    
    public function testIfRouteIsProtected()
    {
        $route = new Route('orders', 'callback', 'GET');
        $this->assertTrue($route->hasAccess(0));
        $this->assertNull($route->capability());

        $route->require('manage_options');
        $this->assertEquals('manage_options', $route->capability());
        $this->assertTrue($route->hasAccess(1));
        $this->assertFalse($route->hasAccess(0));
    }

    public function testIfGetRouteCanBeAdded()
    {
        $route = Route::get('orders', 'customCallback');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($route->method(), 'GET');
    }

    public function testIfPostRouteCanBeAdded()
    {
        $route = Route::post('orders', 'customCallback');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($route->method(), 'POST');
    }

    public function testIfDeleteRouteCanBeAdded()
    {
        $route = Route::delete('orders/{order_id}', 'customCallback');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($route->method(), 'DELETE');
    }

    public function testIfPutRouteCanBeAdded()
    {
        $route = Route::put('orders/{order_id}', 'customCallback');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($route->method(), 'PUT');
    }

    public function testIfRouteHasParameters()
    {
        $route = Route::get('orders/{order_id}', 'customCallback');
        $this->assertArrayHasKey('order_id', $route->parameters());
        $this->assertEquals(69, $route->parameters()['order_id']);
    }

    public function testIfRouteParameterIsCorrect()
    {
        $route = Route::get('orders/{order_id}/products/{product_id}', 'customCallback');
        $this->assertEquals(69, $route->parameter('order_id'));
        $this->assertEquals(3, $route->parameter('product_id'));
        $this->assertNull($route->parameter('not_existing_id'));
    }

    public function testIfControllerIsCalled()
    {
        $route = Route::get('orders/{order_id}', 'testCallback');
        $this->assertEquals($route->controller(), 'callback_has_been_called');

        $route = Route::get('orders/{order_id}', [Callback::class, 'test']);
        $this->assertEquals($route->controller(), 'callback_has_been_called');

        $route = Route::get('orders/{order_id}', 'Callback@test');
        $this->assertEquals($route->controller(), 'callback_has_been_called');
    }

    public function testIfTitleCanBeSet()
    {

        $route = Route::get('orders/{order_id}', 'testCallback');
        $this->assertEquals('Test Title', $route->setTitle('Test Title')->title());

        \WP_Mock::onFilter('sidewheels_route_title')
        ->with('Test Title', $route)
        ->reply('Filtered Title', $route);
        $this->assertEquals('Filtered Title', $route->title());
    }

    public function testPageObjectIsCorrect()
    {
        $route = Route::get('orders/{order_id}', 'testCallback');
        $this->assertInstanceOf(stdClass::class, $route->pageObject());
        $this->assertEquals('orders/{order_id}', $route->pageObject()->post_name);
        $this->assertEquals('Orders', $route->pageObject()->post_title);
    }
}
