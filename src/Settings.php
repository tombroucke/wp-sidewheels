<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels;

/**
 * This class parses config.php and adds some additional functionality
 */
class Settings {

	/**
	 * Config file
	 *
	 * @var array
	 */
	private $config = false;

	/**
	 * Check if config file exists, save to variable
	 *
	 * @param array $config Config file.
	 */
	public function __construct( array $config ) {

		$this->config = $config;

		if ( defined( 'WP_ENV' ) && WP_ENV == 'development' ) {
			$this->check_config();
		}

	}

	/**
	 * Check config file for errors, return array of errors
	 *
	 * @return void
	 * @throws Exceptions\UndefinedConfigKeyException Problems with config file.
	 */
	private function check_config() {

		$required_keys = array(
			'endpoints',
			'post_types',
			'text_domain',
			'templates',
		);

		foreach ( $required_keys as $key ) {
			if ( ! $this->get( $key ) ) {
				throw new Exceptions\UndefinedConfigKeyException( $key );
			}
		}

	}

	/**
	 * Get defined textdomain
	 *
	 * @return string The textdomain.
	 */
	public function get_textdomain() {

		return $this->get( 'text_domain' );

	}

	/**
	 * Get value in config file
	 *
	 * @param  string $parameter This key will be fetched from the config file.
	 * @return array|boolean
	 * @throws Exceptions\UndefinedConfigKeyException Undefined key.
	 */
	public function get( $parameter = null ) {

		$return = array();
		$config = $this->config;

		if ( ! $parameter ) {
			return $config;
		}

		if ( array_key_exists( $parameter, $config ) ) {
			$return = $config[ $parameter ];
		} else {
			throw new Exceptions\UndefinedConfigKeyException( $parameter );
		}
		return $return;

	}

	/**
	 * Get value from config file. Iterate parents untill certain key is found
	 *
	 * @param  string  $item                This key that needs to be found.
	 * @param  string  $currentpage         The current path.
	 * @param  boolean $filter_return_array Whether the returned array should filter out empty matches.
	 * @return string|boolean
	 */
	public function matching_endpoint_values( string $item, string $currentpage, bool $filter_return_array = true ) {

		$pagearray        = explode( '/', $currentpage );
		$endpoints        = $this->get( 'endpoints' );
		$current_endpoint = $endpoints[ $pagearray[0] ];
		$match            = array();
		foreach ( $pagearray as $key => $endpoint ) {
			if ( isset( $current_endpoint[ $item ] ) ) {
				$match[] = $current_endpoint[ $item ];
			} else {
				$match[] = false;
			}
			if ( ++$key < count( $pagearray ) ) {
				$current_endpoint = $current_endpoint['children'][ $pagearray[ $key ] ];
			}
		}

		if ( $filter_return_array ) {
			return array_reverse( array_values( array_filter( $match ) ) );
		}

		return array_reverse( $match );

	}

	/**
	 * Get certain key from endpoint
	 *
	 * @param  string $item                 This key that needs to be found.
	 * @param  string $currentpage          The current path.
	 * @param  mixed  $default_return_value The value to return when no value is found.
	 * @return string|boolean
	 */
	public function endpoint_value( string $item, string $currentpage, $default_return_value = false ) {

		$pagearray = explode( '/', $currentpage );
		$endpoints = $this->get( 'endpoints' );

		$current_endpoint = $endpoints[ $pagearray[0] ];
		foreach ( $pagearray as $key => $endpoint ) {
			if ( ++$key < count( $pagearray ) ) {
				$current_endpoint = $current_endpoint['children'][ $pagearray[ $key ] ];
			}
		}

		if ( isset( $current_endpoint[ $item ] ) ) {
			return $current_endpoint[ $item ];
		}

		return $default_return_value;
	}

	/**
	 * Get query var from wp_query
	 *
	 * @param  string|null $var Get query var for this key.
	 * @return string|array|boolean
	 */
	public function query_var( $var = null ) {

		global $wp_query;
		// no variable: return all vars.
		if ( ! $var && $wp_query ) {
			return $wp_query->query_vars;
		}

		// Key does not exist.
		if ( ! isset( $wp_query->query_vars[ $var ] ) ) {
			return false;
		}

		return $wp_query->query_vars[ $var ];

	}

	/**
	 * Check if current page is a frontend sidewheels page
	 *
	 * @return string|boolean
	 */
	public function is_sidewheels_page() {
		
		return ( $this->query_var( 'sidewheels_endpoint' ) ? true : false );

	}
}
