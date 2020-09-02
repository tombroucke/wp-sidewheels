<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels;

/**
 * Main class for our framework.
 */
class Sidewheels {

	/**
	 * Sidewheel settings object
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Loader obkect
	 *
	 * @var Loader
	 */
	private $loader;

	/**
	 * Initilize hooks, define settings
	 *
	 * @param array $config Configuration file.
	 */
	public function __construct( array $config ) {

		$this->loader = new Loader();
		$this->settings = new Settings( $config );
		$this->init();
		$this->run();

	}

	/**
	 * Add actions and filters
	 *
	 * @return void
	 */
	public function init() {

		$this->initialize_templates();
		$this->create_post_types();
		$this->configure_routes();

	}

	/**
	 * Add query vars and create endpoints
	 *
	 * @return void
	 */
	public function configure_routes() {

		$routes = new Routes( $this->settings() );
		$this->loader->add_filter( 'query_vars', $routes, 'custom_query_vars' );
		$this->loader->add_action( 'init', $routes, 'create' );

	}

	/**
	 * If authenticated, initialize Sidewheels templates
	 */
	public function initialize_templates() {

		$authenticator       = new Authenticator( $this->settings() );
		$template_controller = new Template_Controllers( $this->settings(), $authenticator );

		$this->loader->add_action( 'template_redirect', $template_controller, 'initialize', 0 );

	}

	/**
	 * Create post types & taxonomies
	 */
	public function create_post_types() {

		$cpts = new Custom_Post_Types( $this->settings() );
		$this->loader->add_action( 'init', $cpts, 'create_post_types' );
		$this->loader->add_action( 'init', $cpts, 'create_taxonomies' );

	}

	/**
	 * Get Sidewheels settings
	 *
	 * @return Object Settings
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * Add roles
	 */
	public function add_roles() {

		foreach ( $this->settings()->get( 'roles' ) as $role_name => $role ) {
			add_role( $role_name, $role['label'] );
			$role_obj = get_role( $role_name );
			if ( isset( $role['capabilities'] ) && ! empty( $role['capabilities'] ) ) {
				foreach ( $role['capabilities'] as $cap => $has_cap ) {
					if ( $has_cap ) {
						$role_obj->add_cap( $cap );
					} else {
						$role_obj->remove_cap( $cap );
					}
				}
			}
		}

	}

	/**
	 * Create routes, add roles & flush rewrite rules on installation
	 */
	public function install() {

		$this->create_routes();
		$this->add_roles();
		flush_rewrite_rules();

	}

	/**
	 * Remove roles on uninstall
	 */
	public function uninstall() {

		flush_rewrite_rules();

	}

	/**
	 * Let loader object register the filters and actions with WordPress.
	 *
	 * @return void
	 */
	public function run() {

		$this->loader->run();

	}

	/**
	 * Get loader object
	 *
	 * @return Loader
	 */
	public function get_loader() {

		return $this->loader;

	}

}
