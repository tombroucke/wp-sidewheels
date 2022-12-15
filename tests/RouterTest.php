<?php declare(strict_types=1);

use Otomaties\Sidewheels\Route;
use PHPUnit\Framework\TestCase;
use Otomaties\Sidewheels\Router;
use Otomaties\Sidewheels\Exceptions\InvalidMethodException;


final class RouterTest extends TestCase
{

    private $router;

    public function setUp(): void
    {
        $this->router = Router::instance();
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
        $reflection = new ReflectionClass($this->router);
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
        $this->assertNull($this->router->matchingRoute([
            'path' => 'orders',
            'method' => 'GET'
        ]));

        $route = Route::get('orders', 'callback');
        Router::instance()->register($route);
        $this->assertEquals($this->router->matchingRoute([
            'path' => 'orders',
            'method' => 'GET'
        ]), $route);
    }

    public function testIfExceptionIsThrownWhenRouteIsNotFound() {
        $this->expectException(InvalidMethodException::class);
        $this->router->matchingRoute([
            'path' => 'orders',
            'method' => 'GET',
            'unexisting_key' => 'unexisting_value'
        ]);
    }

    public function testCurrentSidewheelsRoute() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertInstanceOf(Route::class, $this->router->currentSidewheelsRoute());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertNull($this->router->currentSidewheelsRoute());
    }
}
