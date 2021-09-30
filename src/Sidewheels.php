<?php

namespace Otomaties\Sidewheels;

/**
 * Register routes, post types, taxonomies, roles etc. from configuration file
 */
class Sidewheels
{
    /**
     * Holds the instance of this class
     *
     * @var mixed
     */
    private static $instance = null;

    /**
     * Rootpath of the WordPress plugin
     *
     * @var string
     */
    private $rootPath = '';
    
    /**
     * Configuration object
     *
     * @var Config
     */
    private $config;

    /**
     * Initialize sidewheels
     */
    public function __construct()
    {
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        $this->rootPath = dirname($reflection->getFileName(), 3);

        $this->config = new Config($this->rootPath);

        $this->initRoutes();
        $this->initPostTypes();
        $this->initTaxonomies();
    }

    /**
     * Register routes
     *
     * @return void
     */
    public function initRoutes() : void
    {
        $routes = $this->config()->routes();
        if (!empty($routes)) {
            foreach ($routes as $route) {
                $path       = $route['path'];
                $callback   = $route['callback'];
                $capability = isset($route['capability']) ? $route['capability'] : null;
                $method     = isset($route['method']) ? $route['method'] : 'GET';

                switch ($method) {
                    case 'POST':
                        $route = Route::post($path, $callback);
                        break;
                    case 'DELETE':
                        $route = Route::delete($path, $callback);
                        break;
                    case 'PUT':
                        $route = Route::put($path, $callback);
                        break;
                    default:
                        $route = Route::get($path, $callback);
                        break;
                }

                if ($capability) {
                    $route->require($capability);
                }
            }
        }
    }

    /**
     * Register post types if present in config file
     *
     * @return void
     */
    public function initPostTypes() : void
    {
        $postTypes = $this->config()->postTypes();
        if (!empty($postTypes)) {
            $customPostTypes = new CustomPostTypes($this->config);
            foreach ($postTypes as $key => $postType) {
                $customPostTypes->add($key, $postType['args']);
            }
        }
    }

    /**
     * Register taxonomies if present in config file
     *
     * @return void
     */
    public function initTaxonomies() : void
    {
        $taxonomies = $this->config()->taxonomies();
        if (!empty($taxonomies)) {
            $customTaxonomies = new CustomTaxonomies($this->config);
            foreach ($taxonomies as $name => $taxonomy) {
                $customTaxonomies->add($name, $taxonomy['singular_label'], $taxonomy['plural_label'], $taxonomy['post_type'], $taxonomy['options']);
            }
        }
    }

    /**
     * Get app configuration
     *
     * @return Config
     */
    public function config() : Config
    {
        return $this->config;
    }

    /**
     * Get instance of Sidewheels
     *
     * @return Siidewheels
     */
    public static function getInstance() : Sidewheels
    {
        if (self::$instance == null) {
            self::$instance = new Sidewheels();
        }
   
        return self::$instance;
    }
}
