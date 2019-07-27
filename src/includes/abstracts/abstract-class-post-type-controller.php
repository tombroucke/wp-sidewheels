<?php
/**
 * Post type controllers
 */
abstract class Sidewheels_Post_Type_Controller
{
	/**
	 * Check post type
	 * @param  integer $post_id
	 * @param  string $post_type
	 * @return boolean
	 */
	public function type_is($post_id, $post_type) {
		return get_post_type($post_id) == $post_type;
	}
}
