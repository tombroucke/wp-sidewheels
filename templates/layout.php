<?php
if (! defined('ABSPATH')) {
	exit;
}

get_header();
do_action('sidewheels_before_custom_template_content');
do_action('sidewheels_custom_template_content');
do_action('sidewheels_after_custom_template_content');
get_footer();