<?php
namespace SideWheels;

/**
 * This class parses config.php and adds some additional functionality
 */
class Settings
{
    /**
     * Location of the sidewheels config file. Should be defined in wp-config.php
     * @var string
     */
    private $config_path = WP_SIDEWHEELS_CONFIG;

    /**
     * Config file
     * @var array
     */
    private $config = false;

    /**
     * Check if config file exists, save to variable
     */
    public function __construct()
    {
        if (!file_exists($this->config_path)) {
            $this->errors[] = array(
                'title'     => __('Configuration file not found', 'sidewheels'),
                'message'   => $this->config_path
            );
            return;
        }

        $this->config = include($this->config_path);
    }

    /**
     * Validate config file
     * @return boolean
     */
    public function validate()
    {
        $errors = $this->check_config();
        if (!empty($errors)) {
            foreach ($errors as $key => $error) {
                throw new Exception($error, 1);
            }
            return false;
        }
        return true;
    }

    /**
     * Check config file for errors, return array of errors
     * @return array array of errors
     */
    private function check_config()
    {
        $errors = array();
        $home_count = 0;
        if (isset($this->config['endpoints'])) {
            foreach ($this->config['endpoints'] as $name => $endpoint) {
                if (isset($endpoint['is_home']) && ++$home_count > 1) {
                    $errors[] = __('There are multiple endpoints set as home in Sidewheels\' config file', 'sidewheels');
                }
            }
        } else {
            $errors[] = __('Endpoints are not defined in Sidewheels\' config file', 'sidewheels');
        }
        if (!isset($this->config['post_types'])) {
            $errors[] = __('post_types is not defined in Sidewheels\' config file', 'sidewheels');
        }
        if (!isset($this->config['text-domain'])) {
            $errors[] = __('text-domain is not defined in Sidewheels\' config file', 'sidewheels');
        }
        return $errors;
    }

    /**
     * Get value in config file
     * @param  string $parameter
     * @return array|boolean
     */
    public function get($parameter = null)
    {
        $return = array();
        $config = $this->config;

        if (!$parameter) {
            return $config;
        }

        if ($parameter && $parameter != 'post_type_names' && array_key_exists($parameter, $config)) {
            $return = $config[$parameter];
        } elseif ($parameter && $parameter == 'post_type_names') {
            foreach ($config['post_types'] as $key => $post_type) {
                array_push($return, $key);
            }

            foreach ($config['endpoints'] as $key => $endpoint) {
                if (isset($endpoint['cpt'])) {
                    array_push($return, $endpoint['slug']);
                }
            }
        } else {
            $return = false;
        }
        return $return;
    }

    /**
     * Get value from config file. Iterate parents untill certain key is found
     * @param  string $param
     * @param  string $item
     * @param  string $currentpage
     * @return string|boolean
     */
    public function get_first_matching($param, $item, $currentpage)
    {
        $pagearray 			= explode('/', $currentpage);
        $endpoints 			= $this->get($param);
        $current_endpoint 	= $endpoints[$pagearray[0]];
        $match 			= false;
        foreach ($pagearray as $key => $endpoint) {
            if (isset($current_endpoint[$item])) {
                $match = $current_endpoint[$item];
            }
            if (++$key < count($pagearray)) {
                $current_endpoint = $current_endpoint['children'][$pagearray[$key]];
            }
        }
        return $match;
    }

    /**
     * Get query var from wp_query
     * @param  string|null $var
     * @return string|array|boolean
     */
    public function query_var($var = null)
    {
        global $wp_query;

        // no variable
        if (!$var && $wp_query) {
            return $wp_query->query_vars;
        }
        if (!isset($wp_query->query_vars[$var])) {
            return false;
        }
        return $wp_query->query_vars[$var];
    }

    /**
     * Check if current page is a frontend sidewheels page
     * @return string|boolean
     */
    public function is_sidewheels_page()
    {
        return $this->query_var('sidewheels_endpoint');
    }
}
