<?php
namespace Otomaties\WP_Sidewheels;

use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;
use \Twig\TwigFunction;

/**
 * Controller
 */
abstract class Sidewheels_Controller {

	protected $settings;

	protected $template;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	protected function args() {
		return array();
	}

	abstract protected function template();

	public function render_template() {
		$args = $this->args();
		$args['controller'] = $this;
		$template_path = $this->settings->get( 'templates' );
		$template = str_replace( '.twig', '', $this->template() ) . '.twig';

		/*
		if ( ! isset( $post_id ) || get_post_type( $post_id ) == $post_type ) {
			$template = str_replace( '.twig', '', $template ) . '.twig';
		} else {
			throw new \Exception( sprintf( 'The requested post (%s) does not match the template.', $post_id ), 1 );
		}
		*/

		$loader = new FilesystemLoader( $template_path );
		$twig = new Environment( $loader );

		/*
		if ( ! isset( $args['breadcrumbs'] ) ) {
			$breadcrumbs = array(
				array(
					'name' => __( 'Home', $this->settings->get_textdomain() ),
					'url' => '/',
				),
			);
			$args['breadcrumbs'] = apply_filters( 'sidewheels_breadcrumbs', $breadcrumbs, $args, $post_id, $post_type );
		}
		*/

		$functions = array();
		$functions[] = new TwigFunction(
			'__',
			function ( $text, $domain ) {
				return __( $text, $domain );
			}
		);
		$functions[] = new TwigFunction(
			'_x',
			function ( $text, $context, $domain ) {
				return _x( $text, $context, $domain );
			}
		);
		$functions[] = new TwigFunction(
			'_n',
			function ( $singular, $plural, $count, $domain ) {
				return _n( $singular, $plural, $count, $domain );
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

		echo $twig->render( $template, $args );
	}
}
