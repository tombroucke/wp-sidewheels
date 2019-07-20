<?php
namespace SideWheels;

class Settings {

	private $config_path = WP_SIDEWHEELS_CONFIG;
	private $config = false;

	public function __construct() {

		$this->init();

	}

	private function init() {

		if( !file_exists($this->config_path) ){
			$this->errors[] = array(
				'title' 	=> __( 'Configuration file not found', 'frontend-app' ),
				'message'	=> $this->config_path
			);
			return;
		}

		$this->config = include( $this->config_path );

	}

	public function validate() {
		$errors = $this->check_config();
		if( !empty( $errors ) ){
			foreach ($errors as $key => $error) {
				throw new Exception($error, 1);
				
			}
			return false;
		}
		return true;
	}

	private function check_config(){

		$errors = array();
		$home_count = 0;
		if( isset( $this->config['endpoints'] ) ){
			foreach ( $this->config['endpoints'] as $name => $endpoint ) {
				if( isset( $endpoint['is_home'] ) && ++$home_count > 1 ){

					$errors[] = __( 'There are multiple endpoints set as home in Sidewheels\' config file', 'frontend-app' );

				}
			}
		}
		else{
			$errors[] = __( 'Endpoints are not defined in Sidewheels\' config file', 'frontend-app' );
		}
		if( !isset( $this->config['post_types'] ) ){
			$errors[] = __( 'post_types is not defined in Sidewheels\' config file', 'frontend-app' );
		}
		if( !isset( $this->config['text-domain'] ) ){
			$errors[] = __( 'text-domain is not defined in Sidewheels\' config file', 'frontend-app' );
		}
		return $errors;

	}

	public function get( $parameter = null ) {
	
		$return = array();
		$config = $this->config;

		if( !$parameter ){
			return $config;
		}

		if( $parameter && $parameter != 'post_type_names' && array_key_exists( $parameter, $config ) ){
			$return = $config[$parameter];
		}
		elseif( $parameter && $parameter == 'post_type_names' ){

			foreach( $config['post_types'] as $key => $post_type ){
				array_push( $return, $key );
			}

			foreach( $config['endpoints'] as $key => $endpoint ){
				if( isset( $endpoint['cpt'] ) ){
					array_push( $return, $endpoint['slug'] );
				}
			}

		}
		else{
			$return = false;
		}
		return $return;
	}

	public function get_first_matching($param, $item, $currentpage) {
		$pagearray = explode('/', $currentpage);
		$endpoints = $this->get($param);
		$current_endpoint = $endpoints[$pagearray[0]];
		$template = false;
		foreach ($pagearray as $key => $endpoint) {
			if( isset( $current_endpoint[$item] ) ){
				$template = $current_endpoint[$item];
			}
			if( ++$key < count($pagearray) ){
				$current_endpoint = $current_endpoint['children'][$pagearray[$key]];
			}
		}
		return $template;
	}

	public function query_var($var){
		global $wp_query;
		if (!isset($wp_query->query_vars[$var])) {
			return false;
		}
		return $wp_query->query_vars[$var];
	}
}