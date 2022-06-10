<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Otomaties\WpModels\PostType;
use Otomaties\Sidewheels\Abstracts\Post as AbstractsPost;

function get_post_type()
{
    return 'shop_order';
}

function get_post_meta($id, $key, $single)
{
    if ($key == 'meta_key' && $id = 69) {
        return 'meta_value';
    }
    return false;
}

function get_posts($args)
{
    PostTest::$functions->get_posts($args);
    return [99,100];
}

function wp_insert_post($args)
{
    PostTest::$functions->wp_insert_post($args);
    return 42;
}

class Order extends PostType
{
    public static function postType() : string
    {
        return 'shop_order';
    }
}

final class PostTest extends TestCase
{
    private $order;
    public static $functions;

    public function setUp(): void
    {
        $this->order = new Order(69);
        parent::setUp();
        WP_Mock::setUp();
        self::$functions = \Mockery::mock();
    }
    
    public function tearDown(): void
    {
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    public function testIdIsCorrect()
    {
        $this->assertEquals($this->order->getId(), 69);
    }

    public function testPostTypeCorrect()
    {
        $this->assertEquals($this->order::postType(), 'shop_order');
    }

    public function testGetMeta()
    {
        $this->assertEquals($this->order->meta()->get('meta_key'), 'meta_value');
        $this->assertFalse($this->order->meta()->get('unexisting_meta_key'));
    }

    public function testFindOrders()
    {
        self::$functions->shouldReceive('get_posts')->with([
            'post_type' => 'shop_order',
            'posts_per_page' => -1,
            'paged' => 0,
            'meta_query' => [
                [
                    'key' => 'meta_key',
                    'value' => 'meta_value'
                ]
            ],
            'fields' => 'ids',
        ])->once();
        $args = [
            'meta_query' => [
                [
                    'key' => 'meta_key',
                    'value' => 'meta_value'
                ]
            ]
        ];
        $orders = Order::find($args);
        $this->assertCount(2, $orders);
        $this->assertInstanceOf(Order::class, $orders->first());
    }

    public function testInsertOrder()
    {
        self::$functions->shouldReceive('wp_insert_post')->with([
            'post_type' => 'shop_order',
            'post_status' => 'publish',
            'post_title' => '',
            'post_content' => '',
        ])->twice();
        $this->assertInstanceOf(Order::class, Order::insert([]));
        $this->assertEquals(Order::insert([])->getId(), 42);
        
        self::$functions->shouldReceive('wp_insert_post')->with([
            'post_type' => 'shop_order',
            'post_status' => 'publish',
            'post_title' => 'Order title',
            'post_content' => '',
        ])->once();
        $this->assertInstanceOf(Order::class, Order::insert(['post_title' => 'Order title']));
    }
}