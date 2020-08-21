<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels;

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
		$this->settings = $settings;
		$this->sidewheels_endpoint  = $this->settings->query_var( 'sidewheels_endpoint' );

		// Only load templates when user can view content.
		if ( $authenticator->user_can_view() && $this->post_type_correct() ) {
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
		$controllers             = $this->settings->get_matching( 'endpoints', 'controller', $this->sidewheels_endpoint );
		$settings               = $this->settings; // Is passed on to controller.

		if ( !empty( $controllers ) ) {
			$controller = $controllers[0];
			// Load controller from defined path.
			$file = sprintf( '%s/%s.php', $this->settings->get( 'controllers' ), $controller );
			if ( file_exists( $file ) ) {
				$controller = include_once( $file );
				$controller->render_template();
			} else {
				throw new \Exception( sprintf( 'Controller does not exist at %s.', $file ), 1 );
			}
		} else {
			throw new \Exception( sprintf( 'Controller is not defined for %s.', $this->sidewheels_endpoint ), 1 );
		}
	}

	/**
	 * Check if a post type & handle is defined. If they both are, check if the post type matches the requested post type
	 *
	 * @return Boolean Whether or not the post type is correct
	 */
	public function post_type_correct() {

		$post_types  = $this->settings->get_matching( 'endpoints', 'post_type', $this->sidewheels_endpoint );
		$handles     = $this->settings->get_matching( 'endpoints', 'handle', $this->sidewheels_endpoint );

		foreach ($handles as $key => $handle) {
			$post_type = $post_types[$key];
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
