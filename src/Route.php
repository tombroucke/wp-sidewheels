<?php

namespace Otomaties\Sidewheels;

class Route
{

    private $path;
    private $callback;
    private $capability;
    private $methods;
    private $params = [];

    public function __construct(string $path, mixed $callback, mixed $capability, mixed $method)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->capability = $capability;
        $this->methods = (array)$method;
        
        $this->registerRoute();
        $this->templateController();
    }

    public function path() {
        return $this->path;
    }

    public function get($param) {
        $params = $this->params();
        return isset($params[$param]) ? $params[$param] : false;
    }

    public function params()
    {
        $return = [];
        foreach ($this->params as $param) {
            $key = str_replace('sidewheels_', '', $param);
            $return[$key] = get_query_var($param);
        }
        return $return;
    }

    public function addParam($param)
    {
        if (!in_array($param, $this->params)) {
            $this->params[] = $param;
        }
    }

    public function registerRoute()
    {
        $this->addQueryVar('sidewheels_endpoint');

        $pathParts = explode('/', $this->path);
        $redirect = 'index.php?sidewheels_endpoint=' . $this->path;

        foreach ($pathParts as &$pathPart) {
            $pattern = '/{(.*?)}/';
            if (preg_match($pattern, $pathPart, $matches)) {
                $name = $matches[1];
                $pathPart = '([0-9]+)';
                $queryVarName = "sidewheels_{$name}";
                $matchesIndex = substr_count($redirect, '$matches');

                $redirect .= sprintf('&%s=$matches[%d]', $queryVarName, ++$matchesIndex);
                $this->addQueryVar($queryVarName);
                $this->addParam($queryVarName);
            }
        }
        
        $regex = '^' . implode('/', $pathParts) . '?$';
        add_rewrite_rule($regex, $redirect, 'top');
    }

    public function addQueryVar($var)
    {
        add_filter('query_vars', function ($query_vars) use ($var) {
            if (!in_array($var, $query_vars)) {
                $query_vars[] = $var;
            }
            return $query_vars;
        });
    }

    public function templateController()
    {
        $route = $this;
        add_action('template_include', function ($template) use ($route) {
            
            if (get_query_var('sidewheels_endpoint') != $route->path) {
                return $template;
            }
            if (!in_array($_SERVER['REQUEST_METHOD'], $route->methods)) {
                printf('This route does not support the %s method.', $_SERVER['REQUEST_METHOD']);
                die();
            }

            if ($route->capability && !current_user_can($route->capability)) {
                printf('You don\'t have sufficient permissions to access this resource.', $_SERVER['REQUEST_METHOD']);
                die();
            }

            if (!apply_filters('sidewheels_user_has_access', true, $route)) {
                printf('You don\'t have sufficient permissions to access this resource.', $_SERVER['REQUEST_METHOD']);
                die();  
            }
            
            if (is_array($route->callback)) {
                @list($className, $method) = $route->callback;
                $controller = new $className($route->params());
                call_user_func_array([$controller, $method], array_values($route->params()));
            } elseif (is_string($route->callback) && strpos($route->callback, '@') !== false) {
                @list($className, $method) = explode('@', $route->callback);
                $controller = new $className();
                call_user_func_array([$controller, $method], array_values($route->params()));
                $controller->$method(extract($route->params()));
            } elseif (is_object($route->callback)) {
                call_user_func_array($route->callback, array_values($route->params()));
            }
            return $template;
        });
    }
}
