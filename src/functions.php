<?php

if ( ! function_exists( 'wp_sidewheels_trigger_404' ) ) {
	/**
	 * Display WordPress' 404 page
	 */
	function wp_sidewheels_trigger_404() {
		global $wp_query;
		$wp_query = new WP_Query();
		$wp_query->set_404();
		status_header( 404 );
	}
}

if ( ! function_exists( 'wp_sidewheels_current_url' ) ) {
	/**
	 * Get the current url
	 *
	 * @return string
	 */
	function wp_sidewheels_current_url() {
		$protocol    = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http';
		$host        = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING );
		$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING );
		return $protocol . '://' . $host . $request_uri;
	}
}
