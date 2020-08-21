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
	 * Initilize hooks, define settings
	 *
	 * @param array $config Configuration file.
	 */
	public function __construct( array $config ) {
		$this->settings = new Settings( $config );

		add_filter( 'query_vars', array( $this, 'custom_query_vars' ) );

		add_action( 'template_redirect', array( $this, 'frontend_init' ), 0 );

		add_action( 'init', array( $this, 'create_routes' ) );
		add_action( 'init', array( $this, 'create_post_types' ), 0 );
	}

	/**
	 * Add query args for custom routing
	 *
	 * @param  array $vars WP query vars.
	 * @return array
	 */
	public function custom_query_vars( $vars ) {
		$vars[] = 'sidewheels_endpoint';

		// All handles for [id] endpoints.
		$endpoints = $this->settings->get( 'endpoints' );
		array_walk_recursive(
			$endpoints,
			function( $endpoint, $key ) use ( &$vars ) {
				if ( 'handle' == $key ) {
					$vars[] = $endpoint;
				}
			}
		);
		return $vars;
	}

	/**
	 * If authenticated, load Sidewheels templates
	 */
	public function frontend_init() {
		if ( ! $this->settings()->is_sidewheels_page() ) {
			return;
		}

		// Check if user is authenticated.
		$this->authenticator = new Authenticator( $this->settings );
		if ( $this->authenticator->requires_authentication() && ! is_user_logged_in() ) {
			auth_redirect();
		}
		new Template_Controllers( $this->settings, $this->authenticator );
	}

	/**
	 * Create routes
	 */
	public function create_routes() {
		$routes = new Routes( $this->settings );
		$routes->create();
	}

	/**
	 * Create post types & taxonomies
	 */
	public function create_post_types() {
		$cpts = new Custom_Post_Types( $this->settings );
		$cpts->create_post_types();
		$cpts->create_taxonomies();
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

}
