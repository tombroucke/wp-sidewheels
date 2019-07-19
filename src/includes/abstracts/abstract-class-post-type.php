<?php
abstract class FA_Post_Type
{
    protected $ID;

    public function __construct($id)
    {
        $this->ID = $id;
    }

    public function get($key)
    {
        return get_post_meta($this->get_ID(), $key, true);
    }

    public function set($key, $value)
    {
        return update_post_meta($this->get_ID(), $key, $value);
    }

    public function add_meta($key, $value)
    {
        return add_post_meta($this->get_ID(), $key, $value);
    }

    public function remove_meta($key, $value = null)
    {
        if ($value) {
            delete_post_meta($this->get_ID(), $key, $value);
        } else {
            delete_post_meta($this->get_ID(), $key);
        }
    }

    public function get_ID()
    {
        return $this->ID;
    }

    public function get_post_type()
    {
        return $this->post_type;
    }

    public function get_title()
    {
        return get_the_title($this->get_ID());
    }

    public function get_content()
    {
        $post_object = get_post($this->get_ID());
        return $post_object->post_content;
    }

    public function get_url(){
    	return get_the_permalink($this->get_ID());
    }
}
