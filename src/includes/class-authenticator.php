<?php
class WP_Sidewheels_Authenticator
{
	private $fa_object_id;

	public function __construct()
	{
		$this->settings = wp_frontend_app()->settings();

	}

	public function authentication_status() {

		$fa_endpoint = $this->settings->query_var('fa_endpoint');
		$fa_object_id = $this->settings->query_var('fa_object_id');

		if( $fa_endpoint ){

			$auth_redirect 	= false;
			$capability		= $this->settings->get_first_matching('endpoints', 'capability', $fa_endpoint);

			if ($capability && !current_user_can($capability) && !current_user_can('administrator')) {
				$auth_redirect = true;
			}

			if( $auth_redirect && !is_user_logged_in() ) {
				return 'loggedout';
			}
			elseif( !apply_filters('frontend_app_user_can_view', true, $fa_endpoint, $fa_object_id) ) {
				return 'unauthenticated';
			}
			return 'authenticated';
		}
	}
}
