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
     * @var string|false
     */
    private $capability = false;

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
     * Router instance
     *
     * @var Router
     */
    private $router;

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
        $this->router = Router::getInstance();
        
        $this->registerRoute();
    }

    /**
     * GET request
     *
     * @param string $path
     * @param mixed $callback
     * @return Route
     */
    public static function get(string $path, mixed $callback) {
        return new Route($path, $callback, 'GET');
    }

    /**
     * POST request
     *
     * @param string $path
     * @param mixed $callback
     * @return Route
     */
    public static function post(string $path, mixed $callback) {
        return new Route($path, $callback, 'POST');
    }

    /**
     * DELETE request
     *
     * @param string $path
     * @param mixed $callback
     * @return Route
     */
    public static function delete(string $path, mixed $callback) {
        return new Route($path, $callback, 'DELETE');
    }

    /**
     * PUT request
     *
     * @param string $path
     * @param mixed $callback
     * @return Route
     */
    public static function put(string $path, mixed $callback) {
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
    public function callback() : mixed 
    {
        return $this->callback;
    }

    /**
     * Get route capability
     *
     * @return mixed
     */
    public function capability() : mixed 
    {
        return $this->capability;
    }
    
    /**
     * Get value of route parameter
     *
     * @param string $param
     * @return string|false
     */
    public function parameter(string $param) : mixed
    {
        $params = $this->parameters();
        return isset($params[$param]) ? $params[$param] : false;
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
                $pathPart = '([0-9]+)';
                $queryVarName = "sidewheels_{$name}";
                $matchesIndex = substr_count($redirect, '$matches');

                $redirect .= sprintf('&%s=$matches[%d]', $queryVarName, ++$matchesIndex);
                $this->addQueryVar($queryVarName);
                $this->addParameter($queryVarName);
            }
        }
        
        $regex = sprintf('^%s?$', implode('/', $pathParts));
        add_rewrite_rule($regex, $redirect, 'top');
        $this->router->registerRoute($this);
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
    public function require(string $capability)
    {
        $this->capability = $capability;
    }

    /**
     * Call controller method
     *
     * @return void
     */
    public function controller() {
        $callback = $this->callback();
        if (is_array($callback)) {
            @list($className, $method) = $callback;
            $controller = new $className();
            $callback = [$controller, $method];
        } elseif (is_string($callback) && strpos($callback, '@') !== false) {
            @list($className, $method) = explode('@', $callback);
            $controller = new $className();
            $callback = [$controller, $method];
        }
        return call_user_func_array($callback, array_values($this->parameters()));
    }
}
