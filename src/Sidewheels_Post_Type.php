<?php
namespace Otomaties\WP_Sidewheels;

/**
 * Logic for Sidewheels post types
 */
abstract class Sidewheels_Post_Type
{
    /**
     * Post ID
     * @var integer
     */

    protected $ID;
    /**
     * Post url
     * @var string
     */
    protected $url;

    /**
     * Define ID
     * @param integer $id Post ID
     */
    public function __construct($id)
    {
        $this->ID = $id;
        $this->url = get_the_permalink($this->get_ID());
    }

    /**
     * Returns the post ID
     * @return integer Post ID
     */
    public function get_ID()
    {
        return $this->ID;
    }

    /**
     * Get post meta
     * @param  string $key
     * @return string|boolean
     */
    public function get($key, $single = true)
    {
        return get_post_meta($this->get_ID(), $key, $single);
    }

    /**
     * Get acf field
     * @param  string $key
     * @return string|boolean
     */
    public function get_field($key, $prefix = '')
    {
    	$key = $prefix ? $prefix . '_' . $key : $key;
        return get_field($key, $this->get_ID());
    }

    /**
     * Set post meta
     * @param integer $key
     * @param string $value
     * @return boolean
     */
    public function set($key, $value)
    {
        return update_post_meta($this->get_ID(), $key, $value);
    }

    /**
     * Add post meta
     * @param integer $key
     * @param string $value
     * @return boolean
     */
    public function add_meta($key, $value)
    {
        return add_post_meta($this->get_ID(), $key, $value);
    }

    /**
     * Remove post meta
     * @param integer $key
     * @param string|null $value
     * @return boolean
     */
    public function remove_meta($key, $value = null)
    {
        if ($value) {
            delete_post_meta($this->get_ID(), $key, $value);
        } else {
            delete_post_meta($this->get_ID(), $key);
        }
    }

    /**
     * Get post type
     * @return string
     */
    public function get_post_type()
    {
        return $this->post_type;
    }

    /**
     * Get post title
     * @return string
     */
    public function get_title()
    {
        return get_the_title($this->get_ID());
    }

    /**
     * Get post content
     * @return string
     */
    public function get_content()
    {
        $post_object = get_post($this->get_ID());
        return $post_object->post_content;
    }

    /**
     * Get post url
     * @return string
     */
    public function get_url(){
    	return $this->url;
    }

    public function __set($name, $value) {
    	$this->$name = $value;
    }
}
