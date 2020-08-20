<?php
namespace Otomaties\WP_Sidewheels;

/**
 * Authenticate users in Sidewheels
 */
class Authenticator {

	/**
	 * Sidewheel settings object
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Define settings
	 *
	 * @param Settings $settings Sidewheel settings.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Check if current endpoint requires authentication
	 *
	 * @return boolean
	 */
	public function requires_authentication() {
		$sidewheels_endpoint = $this->settings->query_var( 'sidewheels_endpoint' );
		if ( $this->settings->is_sidewheels_page() ) {
			$capability     = $this->settings->get_first_matching( 'endpoints', 'capability', $sidewheels_endpoint );
			if ( $capability && 'read_posts' != $capability ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if user has access to endpoint
	 *
	 * @return boolean
	 */
	public function user_can_view() {
		$authorized = false;
		$sidewheels_endpoint    = $this->settings->query_var( 'sidewheels_endpoint' );

		if ( ! $this->requires_authentication() ) {
			$authorized = true;
		} else {
			$capability = $this->settings->get_first_matching( 'endpoints', 'capability', $sidewheels_endpoint );

			if ( ! $capability || current_user_can( $capability ) || current_user_can( 'manage_options' ) ) {
				$authorized = true;
			}
		}

		return apply_filters( 'sidewheels_user_can_view', $authorized, $sidewheels_endpoint, $this->settings->query_var() );
	}
}
