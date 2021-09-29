<?php

namespace Otomaties\Sidewheels;

class Router
{
    /**
     * Holds the instance of this class
     *
     * @var mixed
     */
    private static $instance = null;

    private $routes;

    public function __construct()
    {
        $this->init();
        $this->locateController();
    }

    public function init()
    {
        add_filter('query_vars', function ($query_vars) {
            if (!in_array('sidewheels_route', $query_vars)) {
                $query_vars[] = 'sidewheels_route';
            }
            return $query_vars;
        });
    }

    public function locateController()
    {
        $router = $this;
        add_action('template_include', function ($template) use ($router) {
            $sidewheelsRoute = get_query_var('sidewheels_route');
            if (!$sidewheelsRoute) {
                return $template;
            }

            $route = $router->match($sidewheelsRoute, $_SERVER['REQUEST_METHOD']);
            if ($route && (!$route->capability() || current_user_can($route->capability()) || apply_filters('sidewheels_user_has_access', false, $route))) {
                $route->controller();

                // Only continue rendering template when method is GET
                if ($route->method() != 'GET') {
                    return;
                }
            }
            return $template;
        });
    }

    /**
     * Register route to router
     *
     * @param Route $route
     * @return void
     */
    public function registerRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * Find a route with matching path & method
     *
     * @param String $path
     * @param String $method
     * @return Route|false
     */
    public function match(String $path, String $method)
    {
        foreach ($this->routes as $route) {
            if ($route->method() == $method && $route->path() == $path) {
                return $route;
            }
        }
        return false;
    }

    /**
     * Get instance of Router
     *
     * @return Router
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Router();
        }
   
        return self::$instance;
    }
}
