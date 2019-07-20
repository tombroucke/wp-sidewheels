<?php
namespace SideWheels;

class Template_Controllers
{
	private $settings;
	private $sidewheels_endpoint;
	private $sidewheels_object_id;

	public function __construct()
	{

		$this->settings = wp_sidewheels()->settings();
		$this->sidewheels_endpoint 	= $this->settings->query_var('sidewheels_endpoint');
		$this->sidewheels_object_id = $this->settings->query_var('sidewheels_object_id');

		add_action('frontend_app_custom_template_content', array( $this, 'custom_template_content' ));
		add_filter('template_include', array( $this, 'custom_template_include' ), 9999);

	}

	public function custom_template_content(){

		$sidewheels_object_id = $this->sidewheels_object_id;

		$template_path 	= $this->settings->get_first_matching('endpoints', 'template', $this->sidewheels_endpoint);
		$controller 	= $this->settings->get_first_matching('endpoints', 'controller', $this->sidewheels_endpoint);

        // Include controller
		if ($controller) {
			$file = sprintf('%s/%s.php', $this->settings->get('controllers'), $controller);
			if( file_exists( $file ) ){
				include_once( $file );
			}
			else {
				throw new Exception(sprintf('Controller does not exist at %s.', $file), 1);

			}
		}
		else {
			$template_path_array = explode('/', $template_path);

			end($template_path_array);
			$template_path_array[key($template_path_array)] = ucwords($template_path_array[key($template_path_array)]);
			
			$controller_path = implode($template_path_array, '/');
			$file = sprintf('%s/%s.php', $this->settings->get('controllers'), $controller_path . 'Controller');
			if( file_exists( $file ) ){
				include_once( $file );
			}
		}

        // Include template
		if ($template_path) {
			$file = sprintf('%s/%s.php', $this->settings->get('templates'), $template_path);
			if( file_exists( $file ) ){
				$template = $file;
			}
			else {
				throw new Exception(sprintf('Template does not exist at %s.', $file), 1);

			}
			include_once($file);
		}
	}

	public function custom_template_include($template)
	{

		if( $this->sidewheels_object_id ) {
			$post_type = $this->settings->get_first_matching('endpoints', 'post_type', $this->sidewheels_endpoint);
			if( $post_type && $post_type != get_post_type($this->sidewheels_object_id) ) {
				wp_sidewheels_trigger_404();
			}
		}

		// Add main template file
		$template = sprintf('%s/%s.php', $this->settings->get('templates'), 'layout');
		if( !file_exists( $template ) ) {
			$template = plugin_dir_path( __FILE__ ) . '../templates/layout.php';
		}

		$template = apply_filters('frontend_app_main_template', $template);

		return $template;
	}
}
