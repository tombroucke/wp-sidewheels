<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels;

use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;
use \Twig\TwigFunction;

/**
 * Controller
 */
abstract class Sidewheels_Controller {

	/**
	 * WP Sidewheels settings
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Define settings
	 *
	 * @param Settings $settings WP Sidewheels settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Define template path
	 *
	 * @return string Template path
	 */
	abstract protected function template();

	/**
	 * Render defined template with extra functions and variables
	 *
	 * @return void
	 */
	public function render_template() {
		$args['controller'] = $this;
		$template_path = $this->settings->get( 'templates' );
		$template = str_replace( '.twig', '', $this->template() ) . '.twig';

		$loader = new FilesystemLoader( $template_path );
		$twig = new Environment( $loader );

		$functions = array();
		$functions[] = new TwigFunction(
			'__',
			function ( $text, $domain ) {
				return __( $text, $domain ); // phpcs:ignore
			}
		);
		$functions[] = new TwigFunction(
			'_x',
			function ( $text, $context, $domain ) {
				return _x( $text, $context, $domain ); // phpcs:ignore
			}
		);
		$functions[] = new TwigFunction(
			'_n',
			function ( $singular, $plural, $count, $domain ) {
				return _n( $singular, $plural, $count, $domain ); // phpcs:ignore
			}
		);
		$functions[] = new TwigFunction(
			'uniqid',
			function () {
				return uniqid();
			}
		);
		$functions[] = new TwigFunction(
			'print_r',
			function ( $array ) {
				return print_r( $array );
			}
		);
		$functions[] = new TwigFunction(
			'get_stylesheet_directory_uri',
			function ( $file ) {
				return get_stylesheet_directory_uri() . $file;
			}
		);

		foreach ( apply_filters( 'sidewheels_twig_functions', $functions ) as $key => $function ) {
			$twig->addFunction( $function );
		}

		$args = apply_filters( 'sidewheels_twig_variables', $args );

		echo $twig->render( $template, $args ); // phpcs:ignore
	}
}
