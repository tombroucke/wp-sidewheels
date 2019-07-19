<?php
if (! defined('ABSPATH')) {
	exit;
}

get_header();
do_action('frontend_app_before_custom_template_content');
do_action('frontend_app_custom_template_content');
do_action('frontend_app_after_custom_template_content');
get_footer();