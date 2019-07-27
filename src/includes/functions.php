<?php
use \SideWheels\WP_Sidewheels;

if( !function_exists('wp_sidewheels_trigger_404') ) {
	/**
	 * Display WordPress' 404 page
	 */
	function wp_sidewheels_trigger_404() {
		global $wp_query;
		$wp_query = new WP_Query();
		$wp_query->set_404();
		status_header(404);
		include( get_query_template( '404' ) );
		exit();
	}
}

if(!function_exists('wp_sidewheels')) {
	/**
	 * Get an instance of WP_Sidewheels
	 * @return \Sidewheels\WP_Sidewheels
	 */
	function wp_sidewheels()
	{
		return WP_Sidewheels::get_instance();
	}
}