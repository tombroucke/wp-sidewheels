<?php

namespace Otomaties\Sidewheels;

class Route
{

    /**
     * The route path
     *
     * @var string
     */
    private $path;

    /**
     * The route
     *
     * @var mixed
     */
    private $callback;

    /**
     * Optional capability to protect this route
     *
     * @var string|null
     */
    private $capability = null;

    /**
     * GET, POST, PUT or DELETE
     *
     * @var [type]
     */
    private $method;

    /**
     * Route parameters
     *
     * @var array
     */
    private $params = [];

    /**
     * Route title
     *
     * @var string
     */
    private $title = '';

    /**
     * Initialize route
     *
     * @param string $path
     * @param mixed $callback
     * @param string $method
     */
    public function __construct(string $path, mixed $callback, string $method)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->method = strtoupper($method);

        $pathArray = explode('/', $this->path());
        $this->title = !empty($pathArray) ? ucfirst($pathArray[0]) : '';
        
        $this->registerRoute();
    }

    /**
     * GET request
     *
     * @param string $path
     * @param mixed $callback
     * @return Route
     */
    public static function get(string $path, mixed $callback) : Route
    {
        return new Route($path, $callback, 'GET');
    }

    /**
     * POST request
     *
     * @param string $path
     * @param mixed $callback
     * @return Route
     */
    public static function post(string $path, mixed $callback) : Route
    {
        return new Route($path, $callback, 'POST');
    }

    /**
     * DELETE request
     *
     * @param string $path
     * @param mixed $callback
     * @return Route
     */
    public static function delete(string $path, mixed $callback) : Route
    {
        return new Route($path, $callback, 'DELETE');
    }

    /**
     * PUT request
     *
     * @param string $path
     * @param mixed $callback
     * @return Route
     */
    public static function put(string $path, mixed $callback) : Route
    {
        return new Route($path, $callback, 'PUT');
    }

    /**
     * Get path for this route
     *
     * @return string
     */
    public function path() : string
    {
        return $this->path;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function method() : string
    {
        return $this->method;
    }

    /**
     * Get route callback
     *
     * @return mixed
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * Get route capability
     *
     * @return mixed
     */
    public function capability()
    {
        return $this->capability;
    }
    
    /**
     * Get value of route parameter
     *
     * @param string $param
     * @return string|null
     */
    public function parameter(string $param) : ?string
    {
        $params = $this->parameters();
        return isset($params[$param]) ? $params[$param] : null;
    }

    /**
     * Add parameter to params
     *
     * @param string $param
     * @return void
     */
    private function addParameter(string $param) : void
    {
        if (!in_array($param, $this->params)) {
            $this->params[] = $param;
        }
    }
    
    /**
     * Get key->value pair of route parameters
     *
     * @return array
     */
    public function parameters() : array
    {
        $return = [];
        foreach ($this->params as $param) {
            $key = str_replace('sidewheels_', '', $param);
            $return[$key] = get_query_var($param);
        }
        return $return;
    }

    /**
     * Set route title
     *
     * @param string $title
     * @return Route
     */
    public function setTitle(string $title) : Route
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get route title
     *
     * @return void
     */
    public function title() : string
    {
        return apply_filters('sidewheels_route_title', $this->title, $this);
    }

    /**
     * Add route parameters to query vars & route parameters, add rewrite rule
     *
     * @return void
     */
    private function registerRoute() : void
    {
        $pathParts = explode('/', $this->path);
        $redirect = 'index.php?sidewheels_route=' . $this->path;

        foreach ($pathParts as &$pathPart) {
            $pattern = '/{(.*?)}/';
            if (preg_match($pattern, $pathPart, $matches)) {
                $name = $matches[1];
                $pathPart = '([0-9]+)'; // TODO: also allow strings, not just post ids
                $queryVarName = "sidewheels_{$name}";
                $matchesIndex = substr_count($redirect, '$matches');

                $redirect .= sprintf('&%s=$matches[%d]', $queryVarName, ++$matchesIndex);
                $this->addQueryVar($queryVarName);
                $this->addParameter($queryVarName);
            }
        }
        
        $regex = sprintf('^%s?$', implode('/', $pathParts));
        add_rewrite_rule($regex, $redirect, 'top');
    }

    /**
     * Add query var
     *
     * @param string $var
     * @return void
     */
    private function addQueryVar(string $var) : void
    {
        add_filter('query_vars', function ($query_vars) use ($var) {
            if (!in_array($var, $query_vars)) {
                $query_vars[] = $var;
            }
            return $query_vars;
        });
    }
    
    /**
     * Require the user to be authenticated
     *
     * @param string $capability
     * @return void
     */
    public function require(string $capability) : void
    {
        $this->capability = $capability;
    }

    /**
     * Call controller method
     *
     * @return void
     */
    public function controller()
    {
        $callback = $this->callback();// eg. function(){echo "test"}
        $controller = null;
        if (is_array($callback)) { // eg. ['Namespace\Controllers\Admin', 'index']
            @list($className, $method) = $callback;
            $controller = new $className();
            $callback = [$controller, $method];
        } elseif (is_string($callback) && strpos($callback, '@') !== false) { // eg. ['Namespace\Controllers\Admin@index']
            @list($className, $method) = explode('@', $callback);
            $controller = new $className();
            $callback = [$controller, $method];
        }
        if ($controller) {
            $controller->setRoute($this);
        }
        return call_user_func_array($callback, array_values($this->parameters()));
    }

    /**
     * Post object for custom route
     *
     * @param string $guid
     * @return void
     */
    public function pageObject(string $guid = null) : \stdClass
    {
        if (!$guid) {
            $guid = home_url('/');
        }

        $post                        = new \stdClass;
        $post->ID                    = -1;
        $post->post_author           = 1;
        $post->post_date             = current_time('mysql');
        $post->post_date_gmt         = current_time('mysql', 1);
        $post->post_content          = '';
        $post->post_title            = $this->title();
        $post->post_excerpt          = '';
        $post->post_status           = 'publish';
        $post->comment_status        = 'closed';
        $post->ping_status           = 'closed';
        $post->post_password         = '';
        $post->post_name             = $this->path();
        $post->to_ping               = '';
        $post->pinged                = '';
        $post->modified              = $post->post_date;
        $post->modified_gmt          = $post->post_date_gmt;
        $post->post_content_filtered = '';
        $post->post_parent           = 0;
        $post->guid                  = $guid;
        $post->menu_order            = 0;
        $post->post_type             = 'page';
        $post->post_mime_type        = '';
        $post->comment_count         = 0;
        return $post;
    }

    /**
     * Check if user has access to this route
     *
     * @param integer $userId
     * @return boolean
     */
    public function hasAccess(int $userId) : bool
    {
        // If this->capability is null, users should have access
        $hasAccess = $this->capability() ? user_can($userId, $this->capability()) : true;
        return apply_filters('sidewheels_user_has_access', $hasAccess, $this);
    }
}
