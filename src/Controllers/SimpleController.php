<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels\Controllers;

use Otomaties\WP_Sidewheels\Abstracts\Sidewheels_Controller;

/**
 * Controller for manage view
 */
class SimpleController extends Sidewheels_Controller {

	protected $template;

	public function __construct( $template ) {
		$this->template = $template;
	}

	/**
	 * Template name
	 *
	 * @return string The template path/name.
	 */
	public function template() {
		return $this->template;
	}
}
