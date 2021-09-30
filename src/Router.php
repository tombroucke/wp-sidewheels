<?php

namespace Otomaties\Sidewheels;

/**
 * The router object holds all registered routes, and will try to match requests to routes
 */
class Router
{
    /**
     * Holds the instance of this class
     *
     * @var mixed
     */
    private static $instance = null;
    
    /**
     * Collection of Route objects
     *
     * @var array
     */
    private $routes = [];

    /**
     * Initialize router
     */
    public function __construct()
    {
        $this->init();
        $this->locateController();
    }

    /**
     * Add sidewheels_route to query_vars.
     * This query var is used to determine whether a request should be routed through this router.
     *
     * @return void
     */
    public function init()
    {
        add_filter('query_vars', function ($query_vars) {
            if (!in_array('sidewheels_route', $query_vars)) {
                $query_vars[] = 'sidewheels_route';
            }
            return $query_vars;
        });
    }

    /**
     * Check if page is sidewheels page and call route controller function
     *
     * @return void
     */
    public function locateController()
    {
        add_action('template_include', function ($template) {
            $sidewheelsRoute = get_query_var('sidewheels_route');
            if (!$sidewheelsRoute) {
                return $template;
            }

            $route = $this->match($sidewheelsRoute, $_SERVER['REQUEST_METHOD']);
            // Check if route is found and if user has required capability
            if ($route && (!$route->capability() || current_user_can($route->capability()) || apply_filters('sidewheels_user_has_access', false, $route))) {
                $route->controller();

                // Only continue rendering template when method is GET.
                // POST, PUT & DELETE request shouldn't render out content
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
