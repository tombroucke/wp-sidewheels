<?php

namespace Otomaties\Sidewheels;

class Route
{

    private $path;
    private $callback;
    private $capability = false;
    private $method;
    private $params = [];

    /**
     * Undocumented variable
     *
     * @var Router
     */
    private $router;

    public function __construct(string $path, mixed $callback, string $method)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->method = $method;
        $this->router = Router::getInstance();
        
        $this->registerRoute();
    }

    public static function get(string $path, mixed $callback) {
        return new Route($path, $callback, 'GET');
    }

    public static function post(string $path, mixed $callback) {
        return new Route($path, $callback, 'POST');
    }

    public static function delete(string $path, mixed $callback) {
        return new Route($path, $callback, 'DELETE');
    }

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
     * Get supported methods
     *
     * @return string
     */
    public function method() : string
    {
        return $this->method;
    }

    public function callback() : mixed 
    {
        return $this->callback;
    }

    public function capability() : mixed 
    {
        return $this->capability;
    }
    
    /**
     * Get value of route parameter
     *
     * @param string $param
     * @return mixed
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
    public function requireCapability(string $capability)
    {
        $this->capability = $capability;
    }

    public function controller() {
        if (is_array($this->callback())) {
            @list($className, $method) = $this->callback();
            $controller = new $className($this->parameters());
            return call_user_func_array([$controller, $method], array_values($this->parameters()));
        } elseif (is_string($this->callback()) && strpos($this->callback(), '@') !== false) {
            @list($className, $method) = explode('@', $this->callback());
            $controller = new $className();
            return call_user_func_array([$controller, $method], array_values($this->parameters()));
            $controller->$method(extract($this->parameters()));
        } elseif (is_object($this->callback())) {
            return call_user_func_array($this->callback(), array_values($route->parameters()));
        }
    }
}
