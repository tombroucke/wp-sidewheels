# WP Sidewheels

This package provides an easy way to create custom routes (MVC), roles, Custom post types, ... using a config file.

## Installation

### Add this package to your plugin dependencies

`composer require tombroucke/wp-sidewheels`

### Initialize sidewheels your plugin:

```php
use Otomaties\Sidewheels\Sidewheels;

add_action('init', function(){
	Sidewheels::getInstance();
});
```

### Add to your plugin activation hook:
```php
use Otomaties\Sidewheels\Sidewheels;

register_activation_hook(__FILE__, function(){
	Sidewheels::getInstance()->install();
});
```

### Add to your plugin deactivation hook:
```php
use Otomaties\Sidewheels\Sidewheels;

register_deactivation_hook(__FILE__, function(){
	Sidewheels::getInstance()-> uninstall();
});
```

### add config.php to your plugin root directory

## Important notice

After adding new routes, you need to manually flush your rewrite rules.

## Example configuration file

```php
<?php

use Namespace\Models\Order;

return [
    'templatePath' => __DIR__ . '/views', // This is the default template directory, so this could be omitted
    'textDomain' => 'plugin-textdomain', // Text domain for admin strings translation
    'routes' => [
        [
            'path' => 'public-page',
            'callback' => ['Namespace\Controllers\Frontend', 'index'], // Or string: 'Namespace\Controllers\Frontend@index'
            'title' => __('Public page', 'plugin-textdomain'),
        ],
        [
            'path' => 'private-page',
            'callback' => ['Namespace\Controllers\Admin', 'index'],
            'title' => __('Private page', 'plugin-textdomain'),
            'capability' => 'manage_my_plugin',
        ],
        [
            'path' => 'private-page',
            'callback' => ['Namespace\Controllers\Admin', 'create'],
            'capability' => 'manage_my_plugin',
            'method' => 'POST',
        ],
        [
            'path' => 'private-page/orders/{order_id}',
            'callback' => 'Namespaces\Controllers\Admin\Order@index',
            'method' => 'GET',
            'title' => __('Order #{order_id}', 'plugin-textdomain'),
        ],
        [
            'path' => 'private-page/orders/{order_id}/{order_item}',
            'callback' => 'Namespaces\Controllers\Admin\OrderItem@index',
            'method' => 'GET',
            'title' => __('Order item #{order_item}', 'plugin-textdomain'),
        ]
    ],
    'postTypes' => [ // See https://github.com/johnbillion/extended-cpts
        'shop_order' => array(
            'args' => array(
                'labels' => array(
                    'singular_name' => __('Order', 'plugin-textdomain'),
                    'plural_name' => __('Orders', 'plugin-textdomain'),
                ),
                'menu_icon' => 'dashicons-cart',
                'supports' => array( 'title', 'author' ),
                'show_in_menu' => true,
                'public' => false,
                'publicly_queryable' => false,
                'has_archive' => false,
                'admin_cols' => array(
                    'name' => array(
                        'title' => __('Name', 'plugin-textdomain'),
                        'meta_key' => 'name',
                    ),
                    'total' => array(
                        'title' => __('Total', 'plugin-textdomain'),
                        'function' => function () {
                            $order = new Order(get_the_ID());
                            echo $order->total();
                        },
                    ),
                ),
            ),
        ),
    ],
    'roles' => [
        'administrator' => array(
            'label' => __('Administrator', 'plugin-textdomain'),
            'capabilities' => array(
                'manage_my_plugin' => true,
            ),
        ),
    ]
];
```

## MVC


### Controller:
For each route, you need to define a callback. This can be an inline function or a controller. 

```php
<?php

namespace Namespace\Controllers;

use Otomaties\Sidewheels\Abstracts\Controller;

class Admin extends Controller
{
    public function index()
    {
        $this->route()->setTitle('Dashboard'); // Optional
        $this->render('admin/dash.html', [
            'website' => 'https://tombroucke.be'
        ]);
    }

    public function create() 
    {
        // Create a post or perform other action on POST
    }
    
    public function shout(string $string)
    {
    	return strtoupper($string);
    }
}

```

### View:

This package uses twig as it's templating engine. You can pass variable to your templates, e.g. 'website'. The route object is also passed in as a variable, so you can use {{ route.title }} for example. You can call public methods from your controller.

``` html
<!DOCTYPE html>
<html lang="en">
<head>
	<title>{{ route.title }}</title>
</head>
<body>
	<h1>{{ route.title }}</h1>
	<p>Check out this website: {{ website }}</p>
	<p>{{ shout('I\'m shouting'); }}</p>
</body>
</html>
```

## Filters

### Deny access to certain routes
```php
use Namespace\Models\Order;
add_filter('sidewheels_user_has_access', function ($access, $route) {
    if (current_user_can('manage_options')) {
        $access = true;
    }
    
    // Only give access if current user is author of {order_id}
    if ($route->path() == 'private-page/orders/{order_id}') {
        $order = new Order($route->parameter('order_id'));
        return $order->author() == get_current_user_id();
    }
    return $access;
}, 10, 2);
```

### Manipulate title, will override the title in config file

Title can also be set from controller `$this->route()->setTitle('title');`, but this filter will override everything.

```php
add_filter('sidewheels_route_title', function ($title, $route) {
    if ($route->path() == 'public-page') {
        $title = 'Custom public page title';
    }
    return $title;
}, 10, 2);
```

### Add custom functions to be used in your twig templates.
```php
use \Twig\TwigFunction;

add_filter('sidewheels_twig_functions', function ($functions) {
    $functions = new TwigFunction(
        'customFunction',
        function ($argument) {
            // Modify $argument
            return $argument;
        }
    );
    return $functions;
});
```

## Adding routes

You can add routes outside of the config file:

```php
Route::get('public-page', 'Namespace\Controllers\Frontend@index');
```

## Abstracts

There are 2 additional abstract classes which your models can extend: Post & User. These classes add generic functionality for posts and users.

### User
```php
class Customer extends User 
{
    public static function role() : string
    {
        return 'customer';
    }
}

$customer = new Customer(5); // Pass user id or WP_User object

$customer = Customer::insert([
    'user_pass' => '03071985',
    'user_login' => 'marty',
    'user_email' => 'marty@mcfly.com'
]);

$customer->getId();
$customer->name();
$customer->email();
...

```

```php
class Movie extends Post 
{
    public static function postType() : string
    {
        return 'movie';
    }
}

$movie = new Movie(55); // Movie id

$movie = Movie::find([
	'meta_query' => [
		[
			'key' => 'director',
			'value' => 'Robert Zemeckis'
		]
	]
]);

$movie->get('actors'); // get_post_meta(55, 'actors', true);
$movie->add('actors', 'Michael J. Fox'); // add_post_meta(55, 'actors', true);
$movie->set('rating', 5); // update_post_meta(55, 'rating', 5);
$movie->getField('cover'); // get_field('cover', 55)
$movie->title(); // get_the_title(55)
...

```