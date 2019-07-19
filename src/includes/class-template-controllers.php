<?php
class WP_Sidewheels_Template_Controllers
{
	private $settings;
	private $fa_endpoint;
	private $fa_object_id;

	public function __construct()
	{

		$this->settings = wp_frontend_app()->settings();
		$this->fa_endpoint 	= $this->settings->query_var('fa_endpoint');
		$this->fa_object_id = $this->settings->query_var('fa_object_id');

		add_action('frontend_app_custom_template_content', array( $this, 'custom_template_content' ));
		add_filter('template_include', array( $this, 'custom_template_include' ), 9999);

	}

	public function custom_template_content(){

		$frontend_app_object_id = $this->fa_object_id;

		$template_path 	= $this->settings->get_first_matching('endpoints', 'template', $this->fa_endpoint);
		$controller 	= $this->settings->get_first_matching('endpoints', 'controller', $this->fa_endpoint);

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

		if( $this->fa_object_id ) {
			$post_type = $this->settings->get_first_matching('endpoints', 'post_type', $this->fa_endpoint);
			if( $post_type && $post_type != get_post_type($this->fa_object_id) ) {
				fa_trigger_404();
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
