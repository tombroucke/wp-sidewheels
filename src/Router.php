<?php

namespace Otomaties\Sidewheels;

use Otomaties\Sidewheels\Exceptions\InvalidMethodException;

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
        $this->virtualPageQuery();
    }

    /**
     * Add sidewheels_route to query_vars.
     * This query var is used to determine whether a request should be routed through this router.
     *
     * @return void
     */
    private function init()
    {
        add_filter('query_vars', function ($queryVars) {
            if (!in_array('sidewheels_route', $queryVars)) {
                $queryVars[] = 'sidewheels_route';
            }
            return $queryVars;
        });
    }

    /**
     * Check if page is sidewheels page and call route controller function
     *
     * @return void
     */
    private function locateController() : void
    {
        add_action('template_include', function ($template) {
            $route = $this->currentSidewheelsRoute();
            if ($route) {
                $routeTemplate = $route->controller();

                if ($routeTemplate) {
                    $template = $routeTemplate;
                }

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
     * Find current sidewheels route (using query var & $_SERVER request method)
     *
     * @return Route|null
     */
    public function currentSidewheelsRoute() : ?Route
    {
        $route = $this->matchingRoute([
            'path' => get_query_var('sidewheels_route'),
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
        if (!$route) {
            return null;
        }
        if (!$route->hasAccess(get_current_user_id())) {
            if (is_user_logged_in()) {
                sidewheelsTrigger404();
            } else {
                auth_redirect();
            }
            return null;
        }
        return $route;
    }

    /**
     * Mock wp_query to show as page
     *
     * @return void
     */
    private function virtualPageQuery() : void
    {
        add_filter('the_posts', function ($posts) {
            $route = $this->currentSidewheelsRoute();
            if (!$route) {
                return $posts;
            }

            global $wp, $wp_query;
            $pageObject = $route->pageObject(sidewheelsRoute($route->path(), $route->parameters()));
            
            // update wp_query properties to simulate a found page
            // $wp_query->is_page = true; // Triggers Warning: Attempt to read property "post_parent" on null error
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            $wp->query = [];
            $wp_query->query_vars['error'] = '';
            $wp_query->is_404 = false;
        
            $wp_query->current_post = $pageObject->ID;
            $wp_query->found_posts = 1;
            $wp_query->post_count = 1;
            $wp_query->comment_count = 0;
            $wp_query->current_comment = null;
            $wp_query->is_singular = 1;
        
            $wp_query->post = $pageObject;
            $wp_query->posts = [$pageObject];
            $wp_query->queried_object = $pageObject;
            $wp_query->queried_object_id = $pageObject->ID;
            $wp_query->current_post = $pageObject->ID;
            $wp_query->post_count = 1;
            unset($wp_query->query['error']);
        
            return [$pageObject];
        });
    }

    /**
     * Register route to router
     *
     * @param Route $route
     * @return void
     */
    public function register(Route $route) : void
    {
        $this->routes[] = $route;
    }

    /**
     * Find a route that matches the given arguments
     *
     * @param array $arguments Can contain path, method, controller, pageObject, hasAccess
     * @return Route|null
     */
    public function matchingRoute(array $arguments) : ?Route
    {
        $matchingRoutes = array_filter($this->routes, function ($route) use ($arguments) {
            $matches = [];
            $validMethods = ['path', 'method', 'callback', 'capability', 'title', 'parameters'];
            foreach ($arguments as $key => $value) {
                if (in_array($key, $validMethods)) {
                    $matches[] = $route->$key() == $value;
                } else {
                    throw new InvalidMethodException(
                        sprintf('Method "%s" is not a valid method on Route object', $key),
                        1
                    );
                }
            }
            return !in_array(false, $matches);
        });
        if (!empty($matchingRoutes)) {
            return array_shift($matchingRoutes);
        }
        return null;
    }

    /**
     * Get instance of Router
     *
     * @return Router
     */
    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new Router();
        }
   
        return self::$instance;
    }
}
