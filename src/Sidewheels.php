<?php

namespace Otomaties\Sidewheels;

class Sidewheels
{

    private static $instance = null;

    private $rootPath;
    private $config;

    public function __construct()
    {
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        $this->rootPath = dirname($reflection->getFileName(), 3);

        $this->config = new Config($this->rootPath);

        $this->initRoutes();
        $this->initPostTypes();
        $this->initTaxonomies();
    }

    public function initRoutes()
    {
        if (!empty($this->config()->routes())) {
            foreach ($this->config()->routes() as $route) {
                $endpoint = $route['endpoint'];
                $controller = $route['controller'];
                $capability = isset($route['capability']) ? $route['capability'] : null;
                $method = isset($route['method']) ? $route['method'] : 'GET';

                Router::addRoute($endpoint, $controller, $capability, $method);
            }
        }
    }

    public function initPostTypes()
    {
        if (!empty($this->config()->postTypes())) {
            foreach ($this->config()->postTypes() as $key => $postType) {
                CustomPostType::add($key, $postType['args']);
            }
        }
    }

    public function initTaxonomies()
    {
        if (!empty($this->config()->taxonomies())) {
            foreach ($this->config()->taxonomies() as $name => $taxonomy) {
                CustomTaxonomy::add($name, $taxonomy['singular_label'], $taxonomy['plural_label'], $taxonomy['post_type'], $taxonomy['options']);
            }
        }
    }

    public function config()
    {
        return $this->config;
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Sidewheels();
        }
   
        return self::$instance;
    }
}
