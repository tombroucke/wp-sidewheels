<?php
namespace SideWheels;

class Authenticator
{

	public function __construct()
	{
		$this->settings = wp_sidewheels()->settings();

	}

	public function authentication_status() {

		$sidewheels_endpoint = $this->settings->query_var('sidewheels_endpoint');
		$sidewheels_object_id = $this->settings->query_var('sidewheels_object_id');

		if( $sidewheels_endpoint ){

			$auth_redirect 	= false;
			$capability		= $this->settings->get_first_matching('endpoints', 'capability', $sidewheels_endpoint);

			if ($capability && !current_user_can($capability) && !current_user_can('administrator')) {
				$auth_redirect = true;
			}

			if( $auth_redirect && !is_user_logged_in() ) {
				return 'loggedout';
			}
			elseif( !apply_filters('sidewheels_user_can_view', true, $sidewheels_endpoint, $sidewheels_object_id) ) {
				return 'unauthenticated';
			}
			return 'authenticated';
		}
	}
}
