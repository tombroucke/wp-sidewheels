<?php
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
	 *
	 *
	 * @param Settings $settings Sidewheel settings.
	 */
	public function __construct( $settings, $authenticator ) {
		// Only load templates when user can view content.
		if ( $authenticator->user_can_view() ) {
			$this->settings = $settings;
			$this->sidewheels_endpoint  = $this->settings->query_var( 'sidewheels_endpoint' );

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
	 */
	public function template_content() {
		$template_path          = $this->settings->get_first_matching( 'endpoints', 'template', $this->sidewheels_endpoint );
		$controller             = $this->settings->get_first_matching( 'endpoints', 'controller', $this->sidewheels_endpoint );

		$settings = $this->settings; // Is passed on to controller.

		if ( $controller ) {
			// Load controller from defined path.
			$file = sprintf( '%s/%s.php', $this->settings->get( 'controllers' ), $controller );
			if ( file_exists( $file ) ) {
				$controller = include_once( $file );
				$controller->render_template( 'shop-manager/dashboard' );
			} else {
				throw new \Exception( sprintf( 'Controller does not exist at %s.', $file ), 1 );
			}
		} else {
			// Load controller from default path.
			$template_path_array = explode( '/', $template_path );

			end( $template_path_array );
			$template_path_array[ key( $template_path_array ) ] = ucwords( $template_path_array[ key( $template_path_array ) ] );

			$controller_path = implode( $template_path_array, '/' );
			$file = sprintf( '%s/%s.php', $this->settings->get( 'controllers' ), $controller_path . 'Controller' );

			if ( file_exists( $file ) ) {
				include_once( $file );
			} else {
				// No controller found, only twig file.
				$file = apply_filters( 'sidewheels_partial_template', sprintf( '%s/%s.twig', $this->settings->get( 'templates' ), $template_path ), $template_path, $this->settings->get( 'templates' ) );
				if ( $file ) {
					if ( file_exists( $file ) ) {
						wp_sidewheels_render_template( $template_path . '.twig', array() );
					} else {
						throw new \Exception( sprintf( 'Template does not exist at %s.', $file ), 1 );
					}
				}
			}
		}
	}

	/**
	 * Get main template
	 *
	 * @param  string $template current template
	 * @return string custom template
	 */
	public function template_include( $template ) {

		do_action( 'sidewheels_template_include' );

		// Check if post type is correct, should not be done here
		/*
		 if ($this->sidewheels_object_id) {
			$post_type = $this->settings->get_first_matching('endpoints', 'post_type', $this->sidewheels_endpoint);
			if ($post_type && $post_type != get_post_type($this->sidewheels_object_id)) {
				wp_sidewheels_trigger_404();
			}
		}*/

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
