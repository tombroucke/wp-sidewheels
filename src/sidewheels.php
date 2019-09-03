<?php
namespace SideWheels;

/**
 * Entry file for WP Sidewheels
 */

class WP_Sidewheels
{
    /**
     * Settings
     * @var Settings Object
     */
    private $settings;

    /**
     * WP_Sidewheels instance
     * @var Instance of WP_Sidewheels
     */
    private static $instance = null;

    public static function get_instance()
    {
    	if (null === self::$instance) {
    		self::$instance = new self;
    	}

    	return self::$instance;
    }

    /**
     * Add includes, init package & assign settings
     */
    public function __construct()
    {
    	$this->includes();
    	$this->init();

    	$this->settings = new Settings();
    }

    /**
     * Include files
     */
    private function includes()
    {

        // Abstracts
    	include 'includes/abstracts/abstract-class-post-type.php';
    	include 'includes/abstracts/abstract-class-post-type-controller.php';

        // Classes
    	include 'includes/class-authenticator.php';
    	include 'includes/class-admin.php';
    	include 'includes/class-custom-post-types.php';
    	include 'includes/class-fields.php';
    	include 'includes/class-routes.php';
    	include 'includes/class-settings.php';
    	include 'includes/class-template-controllers.php';
    }

    /**
     * Add actions and filters
     */
    public function init()
    {
    	add_filter('query_vars', array( $this, 'custom_query_vars' ));

    	add_action('template_redirect', array( $this, 'frontend_init' ), 0);

    	add_action('admin_init', array( $this, 'admin_init' ));

    	add_action('init', array($this, 'create_routes'));
    	add_action('init', array($this, 'create_post_types'));

    	add_action('acf/init', array($this, 'add_fields'));
    }

    /**
     * Add query args for custom routing
     * @param  Array $vars
     * @return Array
     */
    public function custom_query_vars($vars)
    {
    	$vars[] = 'sidewheels_endpoint';

    	// All handles for [id] endpoints
    	$endpoints = wp_sidewheels()->settings()->get('endpoints');
    	array_walk_recursive($endpoints, function($endpoint, $key) use(&$vars){
    		if( $key == 'handle' ){
    			$vars[] = $endpoint;
    		}
    	});
    	return $vars;
    }

    /**
     * Load the Admin class
     */
    public function admin_init()
    {
    	new Admin();
    }

    /**
     * If authenticated, load Sidewheels templates
     */
    public function frontend_init() {
    	if( !$this->settings()->is_sidewheels_page() ) {
    		return;
    	}
        // Check if user is authenticated
    	$this->authenticator = new Authenticator();
    	if( $this->authenticator->requires_authentication() && !is_user_logged_in() ) {
    		auth_redirect();
    	}
    	return new Template_Controllers();
    }

    /**
     * Create routes
     */
    public function create_routes()
    {
    	$routes = new Routes();
    	$routes->create();
    }

    /**
     * Create post types & taxonomies
     */
    public function create_post_types()
    {
    	$cpts = new Cpts();
    	$cpts->create_post_types();
    	$cpts->create_taxonomies();
    }

    /**
     * Add ACF support
     */
    public function add_fields() {
    	new Fields();
    }

    /**
     * Get Sidewheels settings
     * @return Object Settings
     */
    public function settings()
    {
    	return $this->settings;
    }

    /**
     * Add roles
     */
    public function add_roles()
    {
    	foreach ($this->settings()->get('roles') as $role_name => $role) {
    		add_role($role_name, $role['label'], $role['capabilities']);
    	}
    }

    /**
     * Remove roles
     */
    public function remove_roles()
    {
    	foreach ($this->settings()->get('roles') as $role_name => $role) {
    		if (get_role($role_name)) {
    			remove_role($role_name);
    		}
    	}
    }

    /**
     * Create routes, add roles & flush rewrite rules on installation
     */
    public function install()
    {
    	$settings = new Settings();
    	$settings->validate();
    	$this->create_routes();
    	$this->add_roles();
    	flush_rewrite_rules();
    }

    /**
     * Remove roles on uninstall
     */
    public function uninstall()
    {
    	$this->remove_roles();
    	flush_rewrite_rules();
    }
}
