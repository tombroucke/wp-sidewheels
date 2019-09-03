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

if(!function_exists('wp_sidewheels_render_template')) {
	/**
	 * [wp_sidewheels_render_template description]
	 * @param  string $template template path/name.twig
	 * @param  array $args template args
	 * @param  int $post_id
	 * @param  string $post_type
	 */
	function wp_sidewheels_render_template($template, $args, $post_id = null, $post_type = null, $render = true)
	{
		$template_path = wp_sidewheels()->settings()->get('templates');

		if( !isset($post_id) || get_post_type($post_id) == $post_type ) {
			$template = str_replace('.twig', '', $template) . '.twig';
		}
		else{
			$template_path = dirname(__DIR__) . '/templates/twig/';
			$template = 'post_type_mismatch.twig';
		}

		$loader = new Twig\Loader\FilesystemLoader($template_path);
		$twig = new Twig\Environment($loader);

		if( !isset($args['breadcrumbs']) ){
			$breadcrumbs = array(
				array(
					'name' => __('Home', 'immobel'),
					'url' => '/'
				),
			);
			$args['breadcrumbs'] = apply_filters('sidewheels_breadcrumbs', $breadcrumbs, $args, $post_id, $post_type);
		}

		$functions = array();
		$functions[] = new \Twig\TwigFunction('__', function ($text, $domain) {
			return __( $text, $domain );
		});
		$functions[] = new \Twig\TwigFunction('wp_sidewheels_generate_url', function ($path) {
			return wp_sidewheels_generate_url( $path );
		});

		foreach (apply_filters('sidewheels_twig_functions', $functions) as $key => $function) {
			$twig->addFunction($function);
		}

		$args = apply_filters('sidewheels_twig_variables', $args);

		if( $render ) {
			echo $twig->render($template, $args);
		}
		else{
			return $twig->render($template, $args);
		}
	}
}

if(!function_exists('wp_sidewheels_current_url')) {
	function wp_sidewheels_current_url(){
		return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	}
}

if(!function_exists('wp_sidewheels_generate_url')) {
	function wp_sidewheels_generate_url($path){

		global $sitepress;
		$current_language = $sitepress->get_current_language();
		$endpoints = wp_sidewheels()->settings()->get('endpoints');
		$url = rtrim( home_url(), '/' ) . '/';
		if( !isset( $endpoints[$path[0]] ) ) {
			return $url;
		}
		$current = $endpoints[$path[0]];
		for ($i=0; $i <= count($path)-1; $i++) { 
			if( isset($current['handle']) ){
				$url .= wp_sidewheels()->settings()->query_var($current['handle']) . '/';
			}
			else {
				$url .= __($current['slug'], wp_sidewheels()->settings()->get('text-domain')) . '/';
			}

			if( $i+1 < count($path) ){
				$current = $current['children'][$path[$i+1]];
			}
		}
		return rtrim($url, '/');
	}
}