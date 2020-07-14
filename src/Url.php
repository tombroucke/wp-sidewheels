<?php
namespace Otomaties\WP_Sidewheels;
/**
 * Create routes to each endpoint in config.php
 */
class Url
{
    /**
     * Settings
     * @var \Sidewheels\Settings
     */
    private $settings;

    /**
     * Desired path
     * @var array
     */
    private $path;

    public function __construct( array $path, Settings $settings )
    {
    	$this->path = $path;
    	$this->settings = $settings;
    }

    /**
     * Create url
     * @return string the URL
     */
    public function __toString() {
		$endpoints = $this->settings->get('endpoints');
		$url = rtrim( home_url(), '/' ) . '/';
		if( !isset( $endpoints[$this->path[0]] ) ) {
			return $url;
		}
		$current = $endpoints[$this->path[0]];
		for ($i=0; $i <= count($this->path)-1; $i++) {
			if( isset($current['handle']) ){
				$url .= $this->settings->query_var($current['handle']) . '/';
			}
			else {
				$url .= __($current['slug'], $this->settings->get_textdomain()) . '/';
			}

			if( $i+1 < count($this->path) ){
				$current = $current['children'][$this->path[$i+1]];
			}
		}
		return rtrim($url, '/');
    }
}