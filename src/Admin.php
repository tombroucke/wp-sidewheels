<?php
namespace Otomaties\WP_Sidewheels;

/**
 * Administration screens
 */
class Admin
{

    /**
     * Settings
     * @var \Sidewheels\Settings
     */
    private $settings;

    public function __construct(  Settings $settings )
    {
        $this->settings = $settings;
        $this->custom_post_types_meta_boxes();
    }

    /**
     * Add a meta box to every post type created by Sidewheels
     */
    public function custom_post_types_meta_boxes()
    {
        $post_types = $this->settings->get('post_types');
        
        foreach ($post_types as $key => $post_type) {
            add_meta_box('sidewheels_meta', __('Information', $this->settings->get_textdomain()), array( $this, 'display_meta_box' ), $key, 'side', 'high', null);
        }
    }

    /**
     * Render meta box on each post type created by Sidewheels
     */
    public function display_meta_box()
    {

        // TODO: Add a real message
        echo 'This post type is managed by WP Sidewheels';
    }
}
