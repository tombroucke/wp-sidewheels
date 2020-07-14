<?php

if( !function_exists('wp_sidewheels_trigger_404') ) {
	/**
	 * Display WordPress' 404 page
	 */
	function wp_sidewheels_trigger_404() {
		global $wp_query;
		$wp_query = new WP_Query();
		$wp_query->set_404();
		status_header(404);
	}
}

if(!function_exists('wp_sidewheels_current_url')) {
	/**
	 * Get the current url
	 * @return string
	 */
	function wp_sidewheels_current_url(){
		return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	}
}
