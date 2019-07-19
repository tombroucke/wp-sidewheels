<?php
class WP_Sidewheels_Routes
{
	private $settings;

	public function __construct()
	{
		$this->settings = wp_frontend_app()->settings();
	}

	public function create()
	{
		$this->iterate_children($this->settings->get('endpoints'));
	}

	private function iterate_children($endpoints, $parents = array(), $hierachy = array(), $depth = 0)
	{
		$depth++;

		foreach ($endpoints as $endpoint_name => $endpoint) {

			$slug = '([0-9]+)';

			// TODO translate endpoints

			if (!isset($endpoint['public']) || $endpoint['public']) {

				if( function_exists( 'icl_get_languages' ) ){
					if ($endpoint_name != '[id]') {
						$slug = $endpoint_name;
					}
					global $sitepress;
					$languages = icl_get_languages();
					foreach ($languages as $language_code => $language) {

						$current_language = $sitepress->get_current_language();
						$sitepress->switch_lang($language_code, true);

						$translated_parents = array();

						foreach ($parents as $key => $parent) {
							$translated_parents[$key] = __($parent, $this->settings->get('text-domain'));
						}

						$translated_parentstring = (!empty($translated_parents) ? rtrim(implode('/', $translated_parents), '/') . '/' : '');
						$hierachystring = (!empty($hierachy) ? rtrim(implode('/', $hierachy), '/') . '/' : '');
						$endpoint_translated_name = __($slug, $this->settings->get('text-domain'));

						add_rewrite_rule($translated_parentstring . $endpoint_translated_name . '/?$', 'index.php?fa_endpoint=' . urlencode(rtrim(str_replace('([0-9]+)', '[id]', $hierachystring . $endpoint_name), '/')) . '&fa_object_id=$matches[1]&lang=' . $language_code, 'top');

						$sitepress->switch_lang($current_language, true);
					}

					if (!isset($parents[$endpoint_name])) {
						$parents[] = $endpoint_name;
						$hierachy[] = $endpoint_name;
					}
				}
				else{

					if ($endpoint_name != '[id]') {
						$slug = $endpoint['slug'];
					}

					$translated_parents = array();

					foreach ($parents as $key => $parent) {
						$translated_parents[$key] = __($parent, $this->settings->get('text-domain'));
					}

					$translated_parentstring = (!empty($translated_parents) ? rtrim(implode('/', $translated_parents), '/') . '/' : '');
					$hierachystring = (!empty($hierachy) ? rtrim(implode('/', $hierachy), '/') . '/' : '');

					add_rewrite_rule('^' . $translated_parentstring . $slug . '/?$', 'index.php?fa_endpoint=' . urlencode(rtrim(str_replace('([0-9]+)', '[id]', $hierachystring . $endpoint_name), '/')) . '&fa_object_id=$matches[1]', 'top');

					if (!isset($parents[$endpoint_name])) {
						$parents[] = $slug;
						$hierachy[] = $endpoint_name;
					}
				}
			}

			if (isset($endpoint['children'])) {
				$this->iterate_children($endpoint['children'], $parents, $hierachy, $depth);
			} else {
				$depth--;
			}
			array_pop($parents);
			array_pop($hierachy);
		}

		$depth--;
	}
}
