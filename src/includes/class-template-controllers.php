<?php
namespace SideWheels;

/**
 * Load templates and controllers
 */
class Template_Controllers
{


    /**
     * Settings
     * @var \Sidewheels\Settings
     */
    private $settings;

    /**
     * Current endpoint
     * @var string
     */
    private $sidewheels_endpoint;

    /**
     * Current sidewheels object id
     * @var integer
     */

    public function __construct()
    {
        // Only load templates when user can view content
        if (wp_sidewheels()->authenticator->user_can_view()) {
            $this->settings = wp_sidewheels()->settings();
            $this->sidewheels_endpoint 	= $this->settings->query_var('sidewheels_endpoint');

            add_action('sidewheels_custom_template_content', array( $this, 'template_content' ));
            add_filter('template_include', array( $this, 'template_include' ), 9999);
        } else {
            wp_sidewheels_trigger_404();
        }
    }

    /**
     * Include template & controller
     */
    public function template_content()
    {
        $template_path 			= $this->settings->get_first_matching('endpoints', 'template', $this->sidewheels_endpoint);
        $controller 			= $this->settings->get_first_matching('endpoints', 'controller', $this->sidewheels_endpoint);

        if ($controller) {
            // Load controller from defined path
            $file = sprintf('%s/%s.php', $this->settings->get('controllers'), $controller);
            if (file_exists($file)) {
                include_once($file);
            } else {
                throw new \Exception(sprintf('Controller does not exist at %s.', $file), 1);
            }
        } else {
            // Load controller from default path
            $template_path_array = explode('/', $template_path);

            end($template_path_array);
            $template_path_array[key($template_path_array)] = ucwords($template_path_array[key($template_path_array)]);
            
            $controller_path = implode($template_path_array, '/');
            $file = sprintf('%s/%s.php', $this->settings->get('controllers'), $controller_path . 'Controller');
            if (file_exists($file)) {
                include_once($file);
            }
            else {
                // No controller found, only twig file
                $file = apply_filters('sidewheels_partial_template', sprintf('%s/%s.twig', $this->settings->get('templates'), $template_path), $template_path, $this->settings->get('templates'));
                if( $file ) {
                    if (file_exists($file)) {
                        wp_sidewheels_render_template($template_path . '.twig', array());
                    } else {
                        throw new \Exception(sprintf('Template does not exist at %s.', $file), 1);
                    }
                }
            }
        }
    }

    /**
     * Get main template
     * @param  string $template current template
     * @return string custom template
     */
    public function template_include($template)
    {

        do_action('sidewheels_template_include');

        // Check if post type is correct, should not be done here
        /* if ($this->sidewheels_object_id) {
            $post_type = $this->settings->get_first_matching('endpoints', 'post_type', $this->sidewheels_endpoint);
            if ($post_type && $post_type != get_post_type($this->sidewheels_object_id)) {
                wp_sidewheels_trigger_404();
            }
        }*/

        // Add main template file
        $template = sprintf('%s/%s.php', $this->settings->get('templates'), 'layout');
        if (!file_exists($template)) {
            $template = plugin_dir_path(__FILE__) . '../templates/layout.php';
        }

        // Allow hooks
        $template = apply_filters('sidewheels_main_template', $template);

        return $template;
    }
}
