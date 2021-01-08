<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels;

use Otomaties\WP_Sidewheels\Controllers\SimpleController;

/**
 * Load templates and controllers
 */
class Template_Controllers {

	/**
	 * Sidewheel settings object
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Sidewheel authenticator object
	 *
	 * @var Authenticator
	 */
	private $authenticator;

	/**
	 * Current endpoint
	 *
	 * @var string
	 */
	private $sidewheels_endpoint;

	/**
	 * Check if user can view the requested page & if post type is correct, display template
	 *
	 * @param Settings      $settings      Sidewheels settings.
	 * @param Authenticator $authenticator Sidweheels authenticator.
	 */
	public function __construct( Settings $settings, Authenticator $authenticator ) {
		
		$this->settings             = $settings;
		$this->authenticator        = $authenticator;

	}

	public function initialize() {

		if ( ! $this->settings->is_sidewheels_page() ) {
			return;
		}

		if ( $this->authenticator->requires_authentication() && ! is_user_logged_in() ) {
			auth_redirect();
		}

		$this->sidewheels_endpoint  = $this->settings->query_var( 'sidewheels_endpoint' );
		// Only load templates when user can view content.
		if ( $this->authenticator->user_can_view() && $this->post_type_correct() ) {
			add_action( 'sidewheels_custom_template_content', array( $this, 'template_content' ) );
			add_filter( 'template_include', array( $this, 'template_include' ), 9999 );
		} else {
			wp_sidewheels_trigger_404();
		}

	}

	/**
	 * Include template & controller
	 *
	 * @return void
	 * @throws \Exception Default exception.
	 */
	public function template_content() {
		
		$controller_class = $this->settings->endpoint_value( 'controller', $this->sidewheels_endpoint );
		$template 		  = $this->settings->endpoint_value( 'template', $this->sidewheels_endpoint );

		if ( $controller_class ) {

			$namespace = $this->settings->get( 'namespace' );
			if ( $namespace ) {
				$controller_namespace = apply_filters( 'sidewheels_controller_namespace', $namespace . '\\Controllers\\' );
				$controller_class = $controller_namespace . $controller_class;
			}

			// Load controller from defined path.
			$controller = new $controller_class();
			$controller->set_settings( $this->settings );
			$controller->render_template();
		} 
		elseif ( $template ) {
			$controller = new SimpleController( $template );
			$controller->set_settings( $this->settings );
			$controller->render_template();
		}
		else {
			throw new \Exception( sprintf( 'Controller is not defined for %s.', $this->sidewheels_endpoint ), 1 );
		}

	}

	/**
	 * Check if a post type & handle is defined. If they both are, check if the post type matches the requested post type
	 *
	 * @return Boolean Whether or not the post type is correct
	 */
	public function post_type_correct() {

		$post_types  = $this->settings->matching_endpoint_values( 'post_type', $this->sidewheels_endpoint, false );
		$handles     = $this->settings->matching_endpoint_values( 'handle', $this->sidewheels_endpoint, false );
		
		foreach ( $handles as $key => $handle ) {
			$post_type = $post_types[ $key ];
			if ( $post_type && $handle ) {
				if ( get_post_type( $this->settings->query_var( $handle ) ) != $post_type ) {
					return false;
				}
			}
		}

		return true;

	}

	/**
	 * Get main template
	 *
	 * @param  string $template current template.
	 * @return string custom template
	 */
	public function template_include( $template ) {

		do_action( 'sidewheels_template_include' );

		// Check if template == false for endpoints without views.
		if( $this->settings->endpoint_value( 'template', $this->sidewheels_endpoint, null ) === false ) {
			$controller_class = $this->settings->endpoint_value( 'controller', $this->sidewheels_endpoint );
			if ( $controller_class ) {

				$namespace = $this->settings->get( 'namespace' );
				if ( $namespace ) {
					$controller_namespace = apply_filters( 'sidewheels_controller_namespace', $namespace . '\\Controllers\\' );
					$controller_class = $controller_namespace . $controller_class;
				}
	
				// Load controller from defined path.
				$controller = new $controller_class();
				$controller->set_settings( $this->settings );
			} 
			else {
				throw new \Exception( sprintf( 'Controller is not defined for %s.', $this->sidewheels_endpoint ), 1 );
			}
			return '';
		}

		// Add main template file.
		$template = sprintf( '%s/%s.php', $this->settings->get( 'templates' ), 'layout' );
		if ( ! file_exists( $template ) ) {
			$template = plugin_dir_path( __FILE__ ) . '../templates/layout.php';
		}

		// Allow hooks.
		$template = apply_filters( 'sidewheels_main_template', $template );

		return $template;
	}
}
