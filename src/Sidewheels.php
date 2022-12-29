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
    public function __construct(string $rootPath = null)
    {
        if (!$rootPath) {
            $ReflectionClass = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
            $this->rootPath = dirname($ReflectionClass->getFileName(), 3);
        }
        $this->rootPath = $rootPath;

        $this->config = new Config($this->rootPath);

        $this->initRoutes();
        $this->initPostTypes();
        $this->initTaxonomies();
        $this->initAdmin();
        $this->addFilters();
    }

    /**
     * Register routes
     *
     * @return void
     */
    private function initRoutes() : void
    {
        // Custom template for sidewheels routes
        add_filter('template_include', function ($template) {
            $sidewheelsTemplatePaths = [
                locate_template('sidewheels.php'),
                $this->config()->templatePath() . '/sidewheels.php',
                dirname(__FILE__, 2) . '/templates/sidewheels.php',
            ];
            if (Router::instance()->currentSidewheelsRoute()) {
                $foundTemplate = null;
                foreach ($sidewheelsTemplatePaths as $path) {
                    if (file_exists($path)) {
                        $foundTemplate = $path;
                        break;
                    }
                }
                $template = $foundTemplate ?? $template;
            }
            return $template;
        });
        
        $routes = $this->config()->routes();
        if (!empty($routes)) {
            foreach ($routes as $route) {
                $path       = $route['path'];
                $callback   = $route['callback'];
                $capability = isset($route['capability']) ? $route['capability'] : null;
                $method     = isset($route['method']) ? $route['method'] : 'GET';
                $title      = isset($route['title']) ? $route['title'] : null;

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

                if ($title) {
                    $route->setTitle($title);
                }

                Router::instance()->register($route);

                if ($capability) {
                    $route->require($capability);
                }
            }
        }
    }

    /**
     * Register post types if present in config file
     * Should be public in order to invoke during activation
     *
     * @return void
     */
    private function initPostTypes() : void
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
    private function initTaxonomies() : void
    {
        $taxonomies = $this->config()->taxonomies();
        if (!empty($taxonomies)) {
            $customTaxonomies = new CustomTaxonomies($this->config);
            foreach ($taxonomies as $name => $taxonomy) {
                $customTaxonomies->add(
                    $name,
                    $taxonomy['singular_label'],
                    $taxonomy['plural_label'],
                    $taxonomy['post_type'],
                    $taxonomy['options']
                );
            }
        }
    }

    private function initAdmin()
    {
        $admin = new Admin($this->config);
        add_action('add_meta_boxes', [$admin, 'addMetaBoxes']);
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
     * Add roles
     * Should be public in order to invoke during activation
     *
     * @return void
     */
    public function initRoles() : void
    {

        foreach ($this->config()->roles() as $role_name => $role) {
            add_role($role_name, $role['label']);
            $role_obj = get_role($role_name);
            if (isset($role['capabilities']) && ! empty($role['capabilities'])) {
                foreach ($role['capabilities'] as $cap => $hasCap) {
                    if ($hasCap) {
                        $role_obj->add_cap($cap);
                    } else {
                        $role_obj->remove_cap($cap);
                    }
                }
            }
        }
    }

    public function addFilters()
    {
        add_filter('pre_get_shortlink', function ($shortlink, $id, $context, $allow_slugs) {
            if (get_query_var('sidewheels_route')) {
                return true;
            }
            return $shortlink;
        }, 10, 4);
    }

    public static function init(string $rootPath = null) : Sidewheels
    {
        return self::instance($rootPath);
    }

    /**
     * Create routes, add roles & flush rewrite rules on installation
     */
    public static function install($rootPath = null) : void
    {
        $sidewheels = self::instance($rootPath);
        $sidewheels->initPostTypes();
        $sidewheels->initRoles();
        flush_rewrite_rules();
    }

    /**
     * Remove roles on uninstall
     */
    public static function uninstall() : void
    {
        flush_rewrite_rules();
    }

    /**
     * Get instance of Sidewheels
     *
     * @return Sidewheels
     */
    public static function instance(string $rootPath = null) : Sidewheels
    {
        if (self::$instance == null) {
            self::$instance = new Sidewheels($rootPath);
        }
   
        return self::$instance;
    }
}
