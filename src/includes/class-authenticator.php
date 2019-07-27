<?php
namespace SideWheels;

/**
 * Authenticate users in Sidewheels
 */
class Authenticator
{

    /**
     * Settings
     * @var \Sidewheels\Settings
     */
	private $settings;

    public function __construct()
    {
        $this->settings = wp_sidewheels()->settings();
    }

    /**
     * Check if current endpoint requires authentication
     * @return boolean
     */
    public function requires_authentication()
    {
        $sidewheels_endpoint = $this->settings->query_var('sidewheels_endpoint');
        if ($this->settings->is_sidewheels_page()) {
            $capability		= $this->settings->get_first_matching('endpoints', 'capability', $sidewheels_endpoint);
            if ($capability && $capability != 'read_posts') {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has access to endpoint
     * @return boolean
     */
    public function user_can_view()
    {
        $authorized = false;
        if (!$this->requires_authentication()) {
            $authorized = true;
        } else {
            $sidewheels_endpoint 	= $this->settings->query_var('sidewheels_endpoint');
            $sidewheels_object_id 	= $this->settings->query_var('sidewheels_object_id');
            $capability				= $this->settings->get_first_matching('endpoints', 'capability', $sidewheels_endpoint);

            if (!$capability || current_user_can($capability) || current_user_can('manage_options')) {
                $authorized = true;
            }
        }

        return apply_filters('sidewheels_user_can_view', $authorized, $sidewheels_endpoint, $sidewheels_object_id);
    }

    /**
     * Check if user is authenticated
     * @return boolean
     */
    public function is_authenticated()
    {
        if ($this->requires_authentication() && !is_user_logged_in()) {
            return false;
        }
        return true;
    }
}
