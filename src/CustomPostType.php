<?php

namespace Otomaties\Sidewheels;

class CustomPostType
{
    /**
     * Register post type
     *
     * @param string $post_type The post type slug.
     * @param array  $args      Post type args.
     */
    public static function add(string $post_type, array $args = array())
    {
		$textDomain = 'sidewheels';
        $plural_name = $args['labels']['plural_name'];
        $singular_name = $args['labels']['singular_name'];

        $default_labels = array(
            'name'               => ucfirst($plural_name),
            'singular_name'      => ucfirst($singular_name),
            'menu_name'          => ucfirst($plural_name),
            'name_admin_bar'     => ucfirst($singular_name),
			'add_new'            => __( 'Add new', $textDomain ), // phpcs:ignore
			'add_new_item'       => sprintf( __( 'Add new %s', $textDomain ), $singular_name ), // phpcs:ignore
			'new_item'           => sprintf( __( 'New %s', $textDomain ), $singular_name ), // phpcs:ignore
			'edit_item'          => sprintf( __( 'Edit %s', $textDomain ), $singular_name ), // phpcs:ignore
			'view_item'          => sprintf( __( 'View %s', $textDomain ), $singular_name ), // phpcs:ignore
			'all_items'          => sprintf( __( 'All %s', $textDomain ), $plural_name ), // phpcs:ignore
			'search_items'       => sprintf( __( 'Search %s', $textDomain ), $plural_name ), // phpcs:ignore
			'parent_item_colon'  => sprintf( __( 'Parent %s', $textDomain ), $singular_name ), // phpcs:ignore
			'not_found'          => sprintf( __( 'No %s found', $textDomain ), $plural_name ), // phpcs:ignore
			'not_found_in_trash' => sprintf( __( 'No %s found in trash', $textDomain ), $plural_name ), // phpcs:ignore
        );
        $labels = wp_parse_args($args['labels'], $default_labels);

        unset($args['labels']);

        $default_args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => $post_type ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'exclude_from_search' => true,
            'menu_position'      => null,
            'supports'           => array( 'title', 'author', 'thumbnail' ),
        );
        $args = wp_parse_args($args, $default_args);

        register_extended_post_type($post_type, $args);
    }
}
