<?php
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
	 */
	public function __construct( $config ) {
		$this->config = $config;

		if ( defined( 'WP_ENV' ) && WP_ENV == 'development' ) {
			$this->check_config();
		}
	}

	/**
	 * Check config file for errors, return array of errors
	 *
	 * @return array array of errors
	 */
	private function check_config() {
		$errors = array();
		$home_count = 0;
		if ( isset( $this->config['endpoints'] ) ) {
			foreach ( $this->config['endpoints'] as $name => $endpoint ) {
				if ( isset( $endpoint['is_home'] ) && ++$home_count > 1 ) {
					throw new \Exception('There are multiple endpoints set as home in your config file.', 1);
				}
			}
		}
		else {
			throw new \Exception('\'endpoints\' is not set in your config file.', 1);
		}
		if ( ! isset( $this->config['post_types'] ) ) {
			throw new \Exception('\'post_types\' is not set in your config file.', 1);
		}
		if ( ! isset( $this->config['text_domain'] ) ) {
			throw new \Exception('\'text_domain\' is not set in your config file.', 1);
		}
	}

	public function get_textdomain() {
		return $this->get( 'text_domain' );
	}

	/**
	 * Get value in config file
	 *
	 * @param  string $parameter
	 * @return array|boolean
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
			throw new \Exception( sprintf( '%s is not defined in your configuration file', $parameter ), 1 );
		}
		return $return;
	}

	/**
	 * Get value from config file. Iterate parents untill certain key is found
	 *
	 * @param  string $param
	 * @param  string $item
	 * @param  string $currentpage
	 * @return string|boolean
	 */
	public function get_first_matching( $param, $item, $currentpage ) {
		$pagearray          = explode( '/', $currentpage );
		$endpoints          = $this->get( $param );
		$current_endpoint   = $endpoints[ $pagearray[0] ];
		$match          = false;
		foreach ( $pagearray as $key => $endpoint ) {
			if ( isset( $current_endpoint[ $item ] ) ) {
				$match = $current_endpoint[ $item ];
			}
			if ( ++$key < count( $pagearray ) ) {
				$current_endpoint = $current_endpoint['children'][ $pagearray[ $key ] ];
			}
		}
		return $match;
	}

	/**
	 * Get query var from wp_query
	 *
	 * @param  string|null $var
	 * @return string|array|boolean
	 */
	public function query_var( $var = null ) {
		global $wp_query;
		// no variable
		if ( ! $var && $wp_query ) {
			return $wp_query->query_vars;
		}
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
