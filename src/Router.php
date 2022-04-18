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
    private function locateController() : void
    {
        add_action('template_include', function ($template) {
            $route = $this->currentSidewheelsRoute();
            if ($route) {
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
     * Find current sidewheels route (using query var & $_SERVER request method)
     *
     * @return Route|null
     */
    public function currentSidewheelsRoute() : ?Route
    {
        $route = $this->match(get_query_var('sidewheels_route'), $_SERVER['REQUEST_METHOD']);
        if (!$route) {
            return null;
        }
        if (!$route->hasAccess(get_current_user_id())) {
            if (is_user_logged_in()) {
                sidewheelsTrigger404();
            } else {
                auth_redirect();
            }
            return 'sqdf qsd' . null;
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
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            $wp->query = array();
            $wp_query->query_vars['error'] = '';
            $wp_query->is_404 = false;
        
            $wp_query->current_post = $pageObject->ID;
            $wp_query->found_posts = 1;
            $wp_query->post_count = 1;
            $wp_query->comment_count = 0;
            $wp_query->current_comment = null;
            $wp_query->is_singular = 1;
        
            $wp_query->post = $pageObject;
            $wp_query->posts = array($pageObject);
            $wp_query->queried_object = $pageObject;
            $wp_query->queried_object_id = $pageObject->ID;
            $wp_query->current_post = $pageObject->ID;
            $wp_query->post_count = 1;
            unset($wp_query->query['error']);
        
            return array($pageObject);
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
     * Find a route with matching path & method
     *
     * @param String $path
     * @param String $method
     * @return Route|false
     */
    public function match(String $path, String $method) : ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->method() == $method && $route->path() == $path) {
                return $route;
            }
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
