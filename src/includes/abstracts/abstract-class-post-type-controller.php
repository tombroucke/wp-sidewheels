<?php
abstract class FA_Post_Type_Controller
{
	public function type_is($post_id, $post_type) {
		return get_post_type($post_id) == $post_type;
	}
}
