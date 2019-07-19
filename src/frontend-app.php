<?php
class WP_Sidewheels
{
	private $settings;
	private static $instance = null;

	public static function get_instance()
	{	
		if (null === self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct()
	{
		$this->includes();
		$this->init();
	}

	private function includes()
	{
		include 'includes/abstracts/abstract-class-post-type.php';
		include 'includes/abstracts/abstract-class-post-type-controller.php';

		include 'includes/class-authenticator.php';
		include 'includes/class-admin.php';
		include 'includes/class-settings.php';
		include 'includes/class-custom-post-types.php';
		include 'includes/class-routes.php';
		include 'includes/class-template-controllers.php';
	}

	private function init()
	{

		$this->settings = new WP_Sidewheels_Settings(WP_FRONTEND_APP_CONFIG);
		
		if ($errors = $this->settings()->errors()) {
            // Display config errors
			foreach ($errors as $error) {
				printf('<h1>%s</h1>', $error['title']);
				if (isset($error['message'])) {
					printf('<pre>%s</pre>', $error['message']);
				}
			}
			return;
		}

		add_filter('query_vars', array( $this, 'custom_query_vars' ));

		add_action('admin_init', array( $this, 'admin_init' ));
		add_action('template_redirect', array( $this, 'frontend_init' ), 0);
		add_filter( 'wpml_is_redirected', function(){
			return false;
		}, 10, 3 );

		add_action('init', array($this, 'create_routes'));
		add_action('init', array($this, 'create_post_types'));

	}

	public function settings() {
		return $this->settings;
	}

	public function admin_init()
	{
		new Sidewheels_Admin();
	}

	public function frontend_init() {

		$authenticator = new WP_Sidewheels_Authenticator();
		switch ($authenticator->authentication_status()) {
			case 'loggedout':
				auth_redirect();
				exit();
				break;
			case 'unauthenticated':
				break;
			case 'authenticated':
				new WP_Sidewheels_Template_Controllers();
				break;
		}
	}

	public function create_routes()
	{
		$routes = new WP_Sidewheels_Routes();
		$routes->create();
	}

	public function create_post_types()
	{
		$cpts = new WP_Sidewheels_Cpts();
		$cpts->create_post_types();
	}

	public function custom_query_vars($vars)
	{
		$vars[] = 'lang';
		$vars[] = 'fa_endpoint';
		$vars[] = 'fa_object_id';
		return $vars;
	}

	public function add_roles()
	{
		foreach ($this->settings()->get('roles') as $role_name => $role) {
			add_role($role_name, $role['label'], $role['capabilities']);
		}
	}

	public function remove_roles()
	{
		foreach ($this->settings()->get('roles') as $role_name => $role) {
			if (get_role($role_name)) {
				remove_role($role_name);
			}
		}
	}

	public function install()
	{
		$this->create_routes();
		$this->add_roles();
		flush_rewrite_rules();
	}

	public function uninstall()
	{
		$this->remove_roles();
		flush_rewrite_rules();
	}
}