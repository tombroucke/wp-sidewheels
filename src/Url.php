<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels;

/**
 * Create routes to each endpoint in config.php
 */
class Url {

	/**
	 * Sidewheel settings object
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Desired path
	 *
	 * @var array
	 */
	private $path;

	/**
	 * Define settings & path
	 *
	 * @param array    $path     Desired path.
	 * @param Settings $settings Sidewheel settings.
	 */
	public function __construct( array $path, Settings $settings ) {
		$this->path = $path;
		$this->settings = $settings;
	}

	/**
	 * Create url
	 *
	 * @return string the URL
	 */
	public function __toString() {
		$endpoints = $this->settings->get( 'endpoints' );
		$url = rtrim( home_url(), '/' ) . '/';
		if ( ! isset( $endpoints[ $this->path[0] ] ) ) {
			return $url;
		}
		$current = $endpoints[ $this->path[0] ];
		$count = count( $this->path );
		for ( $i = 0; $i <= $count - 1; $i++ ) {
			if ( isset( $current['handle'] ) ) {
				$url .= $this->settings->query_var( $current['handle'] ) . '/';
			} else {
				$url .= __( $current['slug'], $this->settings->get_textdomain() ) . '/'; // phpcs:ignore
			}

			if ( $i + 1 < $count ) {
				$current = $current['children'][ $this->path[ $i + 1 ] ];
			}
		}
		return esc_url( $url, '/' );
	}
}
