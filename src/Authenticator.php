<?php // phpcs:ignore
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
	public function __construct( Settings $settings ) {

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
			$capabilities = $this->settings->matching_endpoint_values( 'capability', $sidewheels_endpoint );
			if ( ! empty( $capabilities ) && 'read_posts' != $capabilities[0] ) {
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
			$capabilities = $this->settings->matching_endpoint_values( 'capability', $sidewheels_endpoint );

			if ( empty( $capabilities ) || current_user_can( $capabilities[0] ) || current_user_can( 'manage_options' ) ) {
				$authorized = true;
			}
		}

		return apply_filters( 'sidewheels_user_can_view', $authorized, $sidewheels_endpoint, $this->settings->query_var() );

	}
}
