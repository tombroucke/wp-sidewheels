<?php declare(strict_types=1);

use Otomaties\Sidewheels\Route;
use Otomaties\Sidewheels\Router;
use PHPUnit\Framework\TestCase;


final class RouterTest extends TestCase
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

    public function testIfQueryVarsAreAdded() {
        \WP_Mock::expectFilterAdded('query_vars', function () {
        }, 10, 1);
        \WP_Mock::expectFilterAdded('the_posts', function () {
        }, 10, 1);
        \WP_Mock::expectActionAdded('template_include', function () {
        }, 10, 1);
        $router = Router::instance();
        $reflection = new ReflectionClass($router);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // now we can modify that :)
        $instance->setValue(null, null); // instance is gone
        $instance->setAccessible(false); // clean up

        // now recreate a fresh object
        $router = Router::instance();
        $this->assertInstanceOf(Router::class, $router);
    }

    public function testIfRouteCanBeFound() {
        // Order is important. No routes have been added at this point
        $router = Router::instance();
        $this->assertNull($router->match('orders', 'GET'));
        $route = new Route('orders', 'callback', 'GET');
        Router::instance()->register($route);
        $this->assertEquals($router->match('orders', 'GET'), $route);
    }

    public function testCurrentSidewheelsRoute() {
        $router = Router::instance();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertInstanceOf(Route::class, $router->currentSidewheelsRoute());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertNull($router->currentSidewheelsRoute());
    }
}
