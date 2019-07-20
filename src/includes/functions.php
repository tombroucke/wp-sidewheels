<?php
use \SideWheels\WP_Sidewheels;

if( !function_exists('wp_sidewheels_trigger_404') ) {
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
	function wp_sidewheels()
	{
		return WP_Sidewheels::get_instance();
	}
}