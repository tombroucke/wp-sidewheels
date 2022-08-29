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
	public function __construct( array $path, Settings $settings, array $replacements = array() ) {
		$this->path = $path;
		$this->settings = $settings;
		$this->replacements = $replacements;
	}

	private function rightTrim($str, $needle, $caseSensitive = true)
	{
		$strPosFunction = $caseSensitive ? "strpos" : "stripos";
		if ($strPosFunction($str, $needle, strlen($str) - strlen($needle)) !== false) {
			$str = substr($str, 0, -strlen($needle));
		}
		return $str;
	}

	/**
	 * Create url
	 *
	 * @return string the URL
	 */
	public function __toString() {
		$endpoints = $this->settings->get( 'endpoints' );
		$url = $this->rightTrim( home_url(), '/' ) . '/';

		$currentLanguage = apply_filters( 'wpml_current_language', null );
		$defaultLanguage = apply_filters( 'wpml_default_language', null );
		
		if ( $currentLanguage && $defaultLanguage && $currentLanguage !== $defaultLanguage ) {
			$langAppend = $currentLanguage . '/';
			$url = $this->rightTrim($url, $langAppend );
			$url .= $langAppend;
		}

		if ( ! isset( $endpoints[ $this->path[0] ] ) ) {
			return $url;
		}
		$current = $endpoints[ $this->path[0] ];
		$count = count( $this->path );
		for ( $i = 0; $i <= $count - 1; $i++ ) {
			if ( isset( $current['handle'] ) ) {
				if( !empty( $this->replacements ) ) {
					$url .= array_shift( $this->replacements ) . '/';
				}
				else {
					$url .= $this->settings->query_var( $current['handle'] ) . '/';
				}
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
